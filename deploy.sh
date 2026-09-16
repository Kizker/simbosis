#!/usr/bin/env bash
# ==============================================================================
# Simbiosis News — Automated Hostinger / Cloud Production Deployment Script
# Target: ~/domains/simbiosis.news/public_html
# Domain: https://simbiosis.news
# ==============================================================================
set -e

echo "🚀 [Simbiosis News] Memulai proses deployment ke server produksi Hostinger..."

# 1. Pastikan file .env tersedia
if [ ! -f .env ]; then
    if [ -f .env.production ]; then
        echo "📋 Menggunakan .env.production sebagai .env..."
        cp .env.production .env
    else
        echo "❌ File .env atau .env.production tidak ditemukan!"
        exit 1
    fi
fi

# 2. Sinkronisasi kode terbaru dari GitHub (jika di-clone dengan git)
if [ -d .git ]; then
    echo "📥 Menarik update kode terbaru dari repository GitHub (main)..."
    git fetch origin main
    git reset --hard origin/main
    git pull origin main
fi

# 3. Instalasi Dependensi PHP (Composer)
echo "📦 Menginstall dependensi PHP (Composer Production)..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 4. Migrasi Database MySQL
echo "🗄️ Menjalankan migrasi database MySQL..."
php artisan migrate --force

# 5. Build Aset Frontend (Vite & Vue 3)
if [ -f public/build/manifest.json ] && [ "$BUILD_FRONTEND" != "true" ]; then
    echo "⚡ Aset frontend produksi (public/build) sudah siap dari Git, melewati npm build untuk menghemat resource server."
elif command -v npm &> /dev/null; then
    echo "⚡ Mengompilasi aset frontend Vite..."
    npm install --production=false
    npm run build || echo "⚠️ npm run build gagal karena batasan resource hosting, menggunakan aset pra-kompilasi."
else
    echo "⚠️ npm tidak terdeteksi, melewati tahap kompilasi frontend lokal di server."
fi

# 6. Symlink Storage
if [ ! -L public/storage ]; then
    rm -rf public/storage
    ln -s ../storage/app/public public/storage 2>/dev/null || php artisan storage:link || true
fi

# 7. Optimasi & Bersihkan Cache Laravel
echo "🧹 Mengoptimasi cache Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Pengaturan Izin Folder & File
echo "🔒 Menyesuaikan hak akses file & direktori..."
chmod -R 775 storage bootstrap/cache
chmod 644 .htaccess 2>/dev/null || true
chmod 644 public/.htaccess 2>/dev/null || true

echo "============================================================"
echo "✅ [Simbiosis News] Deployment Hostinger Selesai!"
echo "🌐 Akses Website : https://simbiosis.news"
echo "🔑 Akses Admin   : https://simbiosis.news/harmony-access/login"
echo "============================================================"
