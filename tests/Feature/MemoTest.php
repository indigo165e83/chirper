<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Memo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Memo 機能の Feature テスト（#10）
 *
 * - auth ミドルウェア（未ログイン → ログイン画面）
 * - 一覧の絞り込み（自分のメモだけ）
 * - MemoPolicy（他人のメモ → 403、DB 不変）
 * - FormRequest のバリデーション
 *
 * 対象外: 画面の見た目（CSS / JS）
 *
 * php artisan test --filter=MemoTest
 */
class MemoTest extends TestCase
{
    use RefreshDatabase;
    
    /** 未ログインなら一覧はログイン画面へリダイレクトされる */
    public function testIndex_guest_redirectsToLogin(): void
    {
        $response = $this->get('/memos');
        $response->assertRedirect(route('login'));
    }

    /** ログイン済みなら 200 を返す */
    public function testIndex_loggedIn_returns200(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/memos');
        $response->assertOk();
    }

    /** 
     * 自分のメモが一覧に表示される
     * 他人のメモが表示されない
     */
    public function testIndex_loggedIn_showsOnlyOwnMemos(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myMemo = Memo::factory()->for($user)->create(['title' => 'My Memo']);
        $otherMemo = Memo::factory()->for($otherUser)->create(['title' => 'Other User Memo']);

        $response = $this->actingAs($user)->get('/memos');

        $response->assertOk();
        $response->assertSee($myMemo->title);
        $response->assertDontSee($otherMemo->title);
    }
}
