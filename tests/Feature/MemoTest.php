<?php

namespace Tests\Feature;

use App\Models\Memo;
use App\Models\User;
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
    public function test_index_guest_redirects_to_login(): void
    {
        $response = $this->get('/memos');
        $response->assertRedirect(route('login'));
    }

    /** ログイン済みなら 200 を返す */
    public function test_index_logged_in_returns200(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/memos');
        $response->assertOk();
    }

    /**
     * 自分のメモが一覧に表示される
     * 他人のメモが表示されない
     */
    public function test_index_logged_in_shows_only_own_memos(): void
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
    public function test_store_logged_in_redirects_to_index(): void
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
    public function test_store_empty_title_has_errors_and_does_not_create(): void
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
    public function test_store_title_over100_has_errors_and_does_not_create(): void
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
    public function test_edit_my_memo_returns200(): void
    {
        $user = User::factory()->create();
        $myMemo = Memo::factory()->for($user)->create(['title' => 'My Memo']);
        $response = $this->actingAs($user)->get(route('memos.edit', $myMemo));
        $response->assertOk();
    }

    /**
     * 他人のメモなら 403 を返す
     */
    public function test_edit_others_memo_returns403(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherMemo = Memo::factory()->for($otherUser)->create(['title' => 'Other User Memo']);
        $response = $this->actingAs($user)->get(route('memos.edit', $otherMemo));
        $response->assertStatus(403);
    }

    /**
     * 自分のメモを更新できる
     */
    public function test_update_my_memo_redirects_to_index(): void
    {
        $user = User::factory()->create();
        $myMemo = Memo::factory()->for($user)->create(['title' => 'Old Title', 'body' => 'Old Body']);
        $response = $this->actingAs($user)->put(route('memos.update', $myMemo), [
            'title' => 'New Title',
            'body' => 'New Body',
            'is_draft' => 0,
        ]);
        $response->assertRedirectToRoute('memos.index');

        $this->assertDatabaseHas('memos', [
            'id' => $myMemo->id,
            'user_id' => $user->id,
            'title' => 'New Title',
            'body' => 'New Body',
            'is_draft' => 0,
        ]);
    }

    /**
     * 他人のメモへの更新は 403 で、DB が変わらない
     *
     * updated_at も変わらないことまで見る（403 を返しつつ書き込む実装を検出するため）
     */
    public function test_update_others_memo_returns403_and_does_not_update(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherMemo = $this->travelTo(now()->subDay(), function () use ($otherUser) {
            return Memo::factory()->for($otherUser)->create([
                'title' => 'Old Title',
                'body' => 'Old Body',
            ]);
        });
        $beforeUpdatedAt = $otherMemo->updated_at;     // リクエスト前のupdated_atの値を退避
        $response = $this->actingAs($user)->put(route('memos.update', $otherMemo), [
            'title' => 'New Title',
            'body' => 'New Body',
            'is_draft' => 0,
        ]);
        $response->assertStatus(403);
        $this->assertDatabaseCount('memos', 1);
        $this->assertDatabaseHas('memos', [
            'id' => $otherMemo->id,
            'title' => 'Old Title',
            'body' => 'Old Body',
        ]);
        $otherMemo->refresh();  // DB から読み直す
        $this->assertEquals(
            $beforeUpdatedAt,
            $otherMemo->updated_at,
            '他人のメモの updated_at が変わっていないこと'
        );
    }
}
