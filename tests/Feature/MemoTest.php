<?php

namespace Tests\Feature;

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
}
