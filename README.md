# Chirper

Laravel 12 で作成した、短文投稿（マイクロブログ）アプリケーションです。

Laravel 公式チュートリアル [Laravel Bootcamp](https://bootcamp.laravel.com/) を出発点とし、
**スターターキットを使わずに認証・認可を自前で実装**したうえで、独自の機能追加を進めています。

> **このリポジトリについて**
> Laravel の学習過程を公開しているリポジトリです。ベースは公式チュートリアルですが、
> 認証まわりは Breeze 等を使わず手で実装しており、以降のコミットは自分で設計・実装したものです。
> 何を学び、どう判断したかを追えるよう、コミット履歴を残しています。

---

## スクリーンショット

<!-- TODO: 画面キャプチャを docs/screenshots/ に置いて差し替える -->
<!-- 一覧画面 / 投稿フォーム / ログイン画面 の3枚があると伝わりやすい -->

| 一覧 | ログイン |
| - | - |
| （画像） | （画像） |

---

## 技術スタック

| 分類 | 使用技術 |
| - | - |
| 言語 | PHP 8.4 |
| フレームワーク | Laravel 12 |
| テンプレート | Blade（コンポーネント / スロット） |
| CSS | Tailwind CSS + daisyUI |
| ビルド | Vite |
| データベース | SQLite |
| テスト | Pest（PHPUnit） |
| 実行環境 | WSL2 (Ubuntu 24.04) |

---

## 実装している機能

### 投稿（Chirp）

- 投稿の一覧表示（新着順・50件）
- 投稿の作成（255文字まで）
- 投稿の編集・削除
- 投稿者のアバター表示、相対時刻表示（「3分前」など）

### 認証

- 会員登録
- ログイン / ログアウト
- Remember me（ログイン状態の保持）
- ログイン後、アクセスしようとしていたページへ復帰

### 認可

- **自分の投稿のみ編集・削除できる**（`ChirpPolicy` で制御）
- 未ログインユーザーは変更系の操作にアクセスできない（`auth` ミドルウェア）
- ログイン済みユーザーは登録・ログイン画面にアクセスできない（`guest` ミドルウェア）

---

## 設計で意識したこと

### 認証と認可を2層に分ける

「ログインしているか」（認証）と「その操作をしてよいか」（認可）は別の関心事として扱っています。

- **認証** … ルートに `auth` / `guest` ミドルウェアを付与
- **認可** … `ChirpPolicy` を定義し、コントローラーで `$this->authorize()` を呼ぶ

```php
public function destroy(Chirp $chirp)
{
    $this->authorize('delete', $chirp);
    $chirp->delete();

    return redirect('/')->with('success', 'Your chirp has been deleted!');
}
```

### 画面の出し分けはセキュリティではない

Blade で編集・削除ボタンを出し分けていますが、これは**見た目の配慮**であり防御ではありません。
URL を直接叩かれた場合でも Policy が拒否するよう、サーバー側での検証を必ず通しています。

### ログイン処理のセキュリティ

- ログイン成功直後に `session()->regenerate()` を実行し、**セッション固定攻撃**を防止
- ログイン失敗時のエラーは `email` キーに統一し、**メールアドレスの存在有無を推測させない**
- パスワードは `old()` にフラッシュしない（`onlyInput('email')`）

### N+1 問題への対処

一覧表示では投稿ごとに投稿者を参照するため、`with('user')` で Eager Loading しています。

```php
$chirps = Chirp::with('user')->latest()->take(50)->get();
```

---

## セットアップ

### 必要なもの

- PHP 8.4 以上
- Composer 2.x
- Node.js 20 以上

### 手順

```bash
git clone https://github.com/indigo165e83/chirper.git
cd chirper

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed

composer run dev
```

`http://localhost:8000` で起動します。

`composer run dev` は開発サーバーと Vite を同時に起動します。個別に動かす場合は
`php artisan serve` と `npm run dev` を別々のターミナルで実行してください。

---

## テスト

```bash
php artisan test
```


---

## ライセンス

MIT License
