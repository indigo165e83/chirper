<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

/**
 * 認証の Feature テスト（#12）
 *
 * - ログイン画面（未ログイン → 200）
 * - Auth::attempt()（正しい資格情報 → ログイン成立し / へ ／ 誤り → email にエラー、未ログインのまま）
 * - ログアウト（セッションが破棄され、未ログインに戻る）
 *
 * - auth ミドルウェア（未ログイン → ログイン画面へリダイレクト）
 * - intended()（弾かれたページがあればそこへ、無ければ / へ）
 * - guest ミドルウェア（ログイン済み → / へリダイレクト）
 *
 * - セッション ID（ログイン前後で振り直される）
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

    // ---- ログイン・ログアウト（Auth\Login / Auth\Logout） ----

    /** ログイン画面が表示される */
    public function testLoginScreen_guest_returns200(): void
    {
        $response = $this->get(route('login'));

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

        $response->assertSessionHasErrors(['email' => 'The provided credentials do not match our records.']);
        $this->assertGuest();
    }

    /**
     * 存在しないメールアドレスでもログインできず、同じエラーキーになる
     *
     * パスワード誤りと同じ応答にすることで、メールアドレスの登録有無を外部から推測できないようにする（アカウント列挙の防止）
     */
    public function testLogin_nonExistentEmail_hasErrorsAndStaysGuest(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'The provided credentials do not match our records.']);
        $this->assertGuest();
    }

    /** ログアウトできる */
    public function testLogout_loggedIn_becomesGuestAndRedirectsToRoot(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    // ---- 入口の制御（auth ミドルウェア / intended / guest ミドルウェア） ----

    /**
     * 未ログインで保護されたページへ行くとログイン画面へリダイレクトされる（auth）
     *
     * MemoTest::testIndex_guest_redirectsToLogin() と同じ振る舞いを、auth ミドルウェアの視点で検証する
     */
    public function testAuthMiddleware_guest_redirectsToLogin(): void
    {
        $response = $this->get(route('memos.index'));

        $response->assertRedirect(route('login'));
    }

    /** ログイン後、元々行こうとしたページへ戻される（intended） */
    public function testLogin_fromProtectedPage_redirectsToIntendedPage(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => $password,
        ]);

        // 未ログインで保護されたページへ行き、弾かれる（行き先がセッションに保存される）
        $response = $this->get(route('memos.index'));
        $response->assertRedirect(route('login'));

        // ログインする
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect(route('memos.index'));
        $this->assertAuthenticatedAs($user);
    }

    /** ログイン済みでログイン画面にアクセスするとリダイレクトされる（guest） */
    public function testGuestMiddleware_loggedIn_redirectsToRoot(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $response = $this->get(route('login'));

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    // ---- セッション（ログイン時の ID 振り直し） ----

    /**
     * ログインの前後でセッション ID が変わる（セッション固定攻撃対策）
     *
     * テストのリクエストは Cookie を引き継がないため、何もしないと毎回新しいセッション ID が振られ、
     * ID が変わったかどうかを判定できない。withCookie() で既知の ID を持たせて送ることで判定する。
     *
     * ID の振り直しは SessionGuard::updateSession() と Auth\Login の regenerate() の両方が行う。
     * どちらか片方を消してもこのテストは落ちない（振る舞いが変わらないため）
     */
    public function testLogin_validCredentials_regeneratesSessionId(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => $password,
        ]);

        // ブラウザがセッション Cookie を持ってきた状態を再現する
        $before = Str::random(40);

        $this->withCookie(config('session.cookie'), $before) 
            ->post('/login', [
                'email' => $user->email,
                'password' => $password,
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertNotEquals($before, session()->getId());
    }

}
