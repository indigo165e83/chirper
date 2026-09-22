<?php

namespace Tests\Feature;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Chirp の認可の Feature テスト（#12）
 *
 * - ChirpPolicy（他人の Chirp の編集画面 → 403）
 * - ChirpPolicy（他人の Chirp の更新 → 403、DB は変わらない）
 * - ChirpPolicy（他人の Chirp の削除 → 403、DB に残る）
 *
 * 対象外:
 * - 自分の Chirp の編集・更新・削除（今回は他人側の拒否のみ。Policy が常に false を返す壊れ方は検出できない）
 * - 画面の見た目（CSS / JS）
 *
 * php artisan test --filter=ChirpTest
 */
class ChirpTest extends TestCase
{
    use RefreshDatabase;

    /** 他人の Chirp の編集画面は 403 を返す */
    public function testEdit_othersChirp_returns403(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherChirp = Chirp::factory()->for($otherUser)->create();
        $this->assertTrue($otherChirp->user->is($otherUser), '前提: Chirp の持ち主が $otherUser であること');

        $response = $this->actingAs($user)->get("/chirps/{$otherChirp->id}/edit");

        $response->assertStatus(403);
    }
}
