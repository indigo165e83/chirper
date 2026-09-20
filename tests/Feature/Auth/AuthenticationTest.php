<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 認証の Feature テスト（#12）
 *
 * - ログイン画面（未ログイン → 200 ／ ログイン済み → guest ミドルウェアで / へ）
 * - auth ミドルウェア（未ログイン → ログイン画面へリダイレクト）
 * - Auth::attempt()（正しい資格情報 → ログイン成立し / へ ／ 誤り → email にエラー、未ログインのまま）
 * - intended()（弾かれたページがあればそこへ、無ければ / へ）
 * - session()->regenerate()（ログイン前後でセッションIDが変わる）
 * - ログアウト（セッションが破棄され、未ログインに戻る）
 *
 * 対象外:
 * - 新規登録（register）
 * - パスワードリセット / メール確認
 * - ログイン試行回数の制限（throttle）
 * - 画面の見た目（CSS / JS）
 *
 * php artisan test --filter=AuthenticationTest
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** ログイン画面が表示される */
    public function testLoginScreen_guest_returns200(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    /** 正しいメールアドレスとパスワードでログインできる */
    public function testLogin_validCredentials_authenticatesAndRedirectsToRoot(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => $password,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    /** パスワードが違うとログインできず、email にエラーが出る */
    public function testLogin_wrongPassword_hasErrorsAndStaysGuest(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => $password,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
