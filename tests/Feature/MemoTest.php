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

    /** 
     * 正しい入力で作成でき、一覧へリダイレクトされる
     * 作成されたメモの user_id がログイン中のユーザーになっている
     */
    public function testStore_loggedIn_redirectsToIndex(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/memos', [
            'title' => 'Test Memo',
            'body' => 'Test Content',
            'is_draft' => 0,
        ]);
        $response->assertRedirectToRoute('memos.index');

        $this->assertDatabaseHas('memos', [
            'title' => 'Test Memo',
            'body' => 'Test Content',
            'user_id' => $user->id,
        ]);
    }
    /** 
     * title が空だとエラーになり、作成されない
     */
    public function testStore_emptyTitle_hasErrorsAndDoesNotCreate(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/memos', [
            'title' => '',
            'body' => 'Test Content',
        ]);
        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('memos', 0);
    }

    /** 
     * title が101文字だとエラーになる
     */
    public function testStore_titleOver100_hasErrorsAndDoesNotCreate(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/memos', [
            'title' => str_repeat('a', 101),
            'body' => 'Test Content',
        ]);
        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('memos', 0);
    }

    /** 
     * 自分のメモなら 200 を返す
     */
    public function testEdit_myMemo_returns200(): void
    {
        $user = User::factory()->create();
        $myMemo = Memo::factory()->for($user)->create(['title' => 'My Memo']);
        $response = $this->actingAs($user)->get(route('memos.edit', $myMemo));
        $response->assertOk();
    }

    /** 
     * 他人のメモなら 403 を返す
     */
    public function testEdit_othersMemo_returns403(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherMemo = Memo::factory()->for($otherUser)->create(['title' => 'Other User Memo']);
        $response = $this->actingAs($user)->get(route('memos.edit', $otherMemo));
        $response->assertStatus(403);
    }
}
