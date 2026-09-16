#!/usr/bin/env bash
# ==============================================================================
# Simbiosis News — Docker Automated Deployment Script
# Target: Production Container Environment
# Domain: https://simbiosis.news
# ==============================================================================
set -e

echo "🚀 [1/4] Menarik pembaruan kode dari GitHub (main)..."
if [ -d .git ]; then
    git fetch origin main
    git reset --hard origin/main
    git pull origin main
fi

# Pastikan file .env tersedia
if [ ! -f .env ]; then
    if [ -f .env.production ]; then
        echo "📋 Menggunakan .env.production sebagai .env..."
        cp .env.production .env
    elif [ -f .env.example ]; then
        echo "📋 Menyalin .env.example sebagai .env..."
        cp .env.example .env
    fi
fi

echo "🐳 [2/4] Membangun image Docker & merefresh container..."
docker compose build --no-cache
docker compose up -d --remove-orphans

echo "🗄️ [3/4] Menjalankan migrasi database di dalam container..."
docker compose exec -T app php artisan migrate --force

echo "🧹 [4/4] Membersihkan image yang tidak digunakan..."
docker image prune -f

echo "============================================================"
echo "✅ Container Docker Simbiosis News Berhasil Dijalankan!"
echo "🌐 Akses Web : http://localhost:8000 (atau via https://simbiosis.news)"
echo "📦 Container : simbiosis_app (Port 8000 -> 80)"
echo "============================================================"
docker compose ps
