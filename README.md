# Portal Berita Profesional – Fullpower

Template production-ready Portal Berita berbasis Laravel + Blade dengan RBAC redaksi, workflow editorial atomic, SEO kuat, dan PWA.

## Stack
- Laravel (modern stable) + PHP 8.2+
- MySQL
- Auth: Laravel Breeze (Blade) **(generator)**
- RBAC: spatie/laravel-permission
- Blade + Vite + TailwindCSS + Alpine.js
- WYSIWYG: CKEditor 5 Classic Build + sanitasi allowlist anti-XSS
- Image: Intervention Image
- Search: MySQL FULLTEXT
- Cache: Laravel Cache
- Queue: Database
- PWA: manifest + service worker + offline fallback
- Sitemap: /sitemap.xml + robots.txt

> Catatan: output Breeze tidak disertakan karena harus digenerate lewat artisan.

## Install & Run (Dev)
```bash
composer install
cp .env.example .env
php artisan key:generate

# set DB_* di .env
php artisan migrate
php artisan db:seed

npm install
npm run dev

php artisan serve
```

## Install Breeze (Auth)
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
```

## Build (Production)
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Queue (Database)
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work --tries=3
```

## SMTP
Set di `.env`:
- `MAIL_MAILER=smtp`
- `MAIL_HOST=...`
- `MAIL_PORT=...`
- `MAIL_USERNAME=...`
- `MAIL_PASSWORD=...`
- `MAIL_FROM_ADDRESS=...`

## Deploy Nginx (ringkas)
- HTTPS wajib (TLS)
- `APP_ENV=production`, `APP_DEBUG=false`
- Set `SESSION_SECURE_COOKIE=true`
- Jalankan `php artisan storage:link`
- Set permission storage & cache writable
- Jalankan queue worker via supervisor/systemd

## SOP Redaksi (workflow)
- Wartawan membuat artikel (draft)
- Submit (submitted)
- Editor memindahkan ke review (review) dan memberi keputusan:
  - Reject → revision + catatan wajib
  - Approve/publish → published + published_at wajib + SEO minimal terisi
- Archive → archived

Semua transisi status melalui service layer dan dicatat di:
- `article_status_histories` (append-only)
- `audit_logs` (append-only)

## SEO Global & Sitemap
- Pengaturan SEO global via Admin → Settings
- Sitemap tersedia di `/sitemap.xml` (cached)
- Robots di `/robots.txt`

## Ads Slot + Analytics
- Ads slot tersedia: Header, Sidebar, In-Article, Footer
- Tidak ada integrasi ads network default (placeholder aman)
- Analytics (GA) opsional:
  **DATA TIDAK TERSEDIA: GOOGLE_ANALYTICS_ID** (isi di settings/env bila ada)

## Security Hardening Checklist
- Pastikan CSP prod aktif (lihat `config/security.php`)
- HSTS aktif di production (HTTPS)
- Rate limiting: login, komentar, upload, publish
- Sanitasi HTML allowlist
- Upload whitelist + size limit + UUID + sha256
