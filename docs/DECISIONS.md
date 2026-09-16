# Keputusan Arsitektur – Portal Berita Profesional “Fullpower”

Dokumen ini mencatat keputusan penting yang dipilih untuk template ini, beserta trade-off.

## 1) Editor WYSIWYG: CKEditor 5 (Classic Build)

**Dipilih:** CKEditor 5 Classic Build (free build).  
**Alasan:** Integrasi sederhana di Blade + Alpine, minim wiring, stabil untuk template production-ready.  
**Trade-off:** Build cenderung monolitik; kustomisasi plugin lebih terbatas dibanding Tiptap yang modular.

## 2) Status & Workflow Redaksi

**Status enum (wajib):** `draft → submitted → review → revision → published → archived`  
**Aturan:** semua transisi status dilakukan melalui `App\Services\ArticleWorkflowService` di dalam **DB transaction**.  
**Auditability:** setiap transisi menulis record append-only ke `article_status_histories` dan juga ke `audit_logs`.

## 3) Strategi Caching Halaman Publik

**Dipilih:** `Cache::remember()` untuk:
- Beranda (`public:home`)
- Trending (`public:trending`)
- Related posts (`public:related:{article_id}`)
- Sitemap (`public:sitemap`)

**Trade-off:** cache sederhana & mudah dioperasikan; invalidasi dilakukan pada event publish/update.  
**Catatan:** cache store dapat diganti di `.env` (default database/redis sesuai kebutuhan).

## 4) Kebijakan Sanitasi HTML (Allowlist)

**Dipilih:** Sanitasi server-side berbasis DOMDocument allowlist (`App\Services\HtmlSanitizer`).  
**Allowlist:** ditentukan di `config/security.php` (tag/attr/protocol).  
**Tujuan:** mencegah XSS dari editor.

## 5) Kebijakan Upload (MIME/Ext/Size)

**Dipilih:** `App\Services\MediaUploadService`:
- MIME whitelist (default image)
- Size limit (default 5MB)
- Rename UUID
- Hash sha256 untuk dedup/audit
- Re-encode ke WebP (Intervention Image) untuk performa

**Pemisahan storage:**
- Media publik: `storage/app/public` + `storage:link`
- File sensitif: disiapkan default disk `local` (private). (Saat ini tidak ada file sensitif spesifik dalam MVP.)

## 6) CSP (Production vs Development)

**Dipilih:** CSP ketat di production, lebih longgar di development untuk Vite HMR.
- `config/security.php` mengontrol `csp.dev` dan `csp.prod`
- `App\Http\Middleware\SecurityHeaders` menerapkan CSP sesuai `app.env`

**Trade-off:** CSP prod lebih aman; dev tetap nyaman untuk hot reload.
