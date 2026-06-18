# COACHTECH お問い合わせフォーム

## 概要

お問い合わせフォームアプリケーションです。

ユーザーはお問い合わせ内容を入力し、確認画面を経由して送信できます。
また、管理者はお問い合わせ一覧の閲覧や検索、詳細確認ができます。

本アプリケーションは Laravel 10 を使用した Traditional Web 構成で実装しています。
また、お問い合わせデータを操作するための API も提供しています。

---

## 使用技術

| 項目         | バージョン |
| ------------ | ---------- |
| PHP          | 8.2        |
| Laravel      | 10.x       |
| MySQL        | 8.0        |
| Nginx        | Latest     |
| Tailwind CSS | 3.4.0      |
| Vite         | Latest     |
| Docker       | Latest     |
| Laravel Sail | Latest     |
| phpMyAdmin   | Latest     |

---

## 環境構築

### 1. Laravelプロジェクト作成

```bash
docker run --rm \
-u "$(id -u):$(id -g)" \
-v "$(pwd):/var/www/html" \
-w /var/www/html \
-e COMPOSER_CACHE_DIR=/tmp/composer_cache \
laravelsail/php82-composer:latest \
composer create-project laravel/laravel:^10.0 contact-form-app
```

### 2. プロジェクトディレクトリへ移動

```bash
cd contact-form-app
```

### 3. Laravel Sailインストール

```bash
docker run --rm \
-u "$(id -u):$(id -g)" \
-v "$(pwd):/var/www/html" \
-w /var/www/html \
-e COMPOSER_CACHE_DIR=/tmp/composer_cache \
laravelsail/php82-composer:latest \
composer require laravel/sail --dev
```

### 4. Sail設定ファイル生成

```bash
docker run --rm \
-u "$(id -u):$(id -g)" \
-v "$(pwd):/var/www/html" \
-w /var/www/html \
-e COMPOSER_CACHE_DIR=/tmp/composer_cache \
laravelsail/php82-composer:latest \
php artisan sail:install --with=mysql
```

### 5. .env設定

`.env` を開き、以下の内容になっていることを確認してください。

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### 6. Sail起動

```bash
./vendor/bin/sail up -d
```

### 7. Sailエイリアス設定

zshの場合

```bash
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.zshrc

exec $SHELL
```

bashの場合

```bash
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.bashrc

exec $SHELL
```

### 8. フロントエンド環境構築

#### npmパッケージインストール

```bash
sail npm install
```

#### Tailwind CSSインストール

```bash
sail npm install -D tailwindcss@^3.4.0 postcss autoprefixer

sail npm install alpinejs
```

#### Tailwind設定ファイル生成

```bash
sail npx tailwindcss init -p
```

#### tailwind.config.js

```js
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
```

### 9. 提供リソースの反映

```bash
git clone https://github.com/coachtech-prepared-file/Preparedblade-ConfirmationTest-ContactForm.git
```

クローン後、リポジトリ内の `resources` フォルダをプロジェクト直下へコピーしてください。

### 10. Vite起動

```bash
sail npm run dev
```

### 11. phpMyAdmin追加

docker-compose.yml に以下を追加

```yaml
phpmyadmin:
    image: phpmyadmin:latest
    ports:
        - "${FORWARD_PHPMYADMIN_PORT:-8080}:80"
    environment:
        PMA_HOST: mysql
        PMA_USER: "${DB_USERNAME}"
        PMA_PASSWORD: "${DB_PASSWORD}"
    networks:
        - sail
    depends_on:
        - mysql
```

### 12. アプリケーションキー生成

```bash
sail artisan key:generate
```

### 13. マイグレーション実行

```bash
sail artisan migrate --seed
```

データベースを再作成する場合

```bash
sail artisan migrate:fresh --seed
```

---

## ER図

ER図作成後に追加

---

## APIエンドポイント一覧

| メソッド | URL                | 概要                 |
| -------- | ------------------ | -------------------- |
| GET      | /api/contacts      | お問い合わせ一覧取得 |
| GET      | /api/contacts/{id} | お問い合わせ詳細取得 |
| POST     | /api/contacts      | お問い合わせ登録     |
| PUT      | /api/contacts/{id} | お問い合わせ更新     |
| DELETE   | /api/contacts/{id} | お問い合わせ削除     |

---

## 開発環境URL

### アプリケーション

http://localhost

### phpMyAdmin

http://localhost:8080

---

## 作成者

新海　圭一郎
