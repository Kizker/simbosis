<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\AdsBanner;
use App\Models\Article;
use App\Models\ArticleViewDaily;
use App\Models\AuthorProfile;
use App\Models\Category;
use App\Models\Share;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\Support\EditorialImageFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    private const ARTICLE_COUNT = 84;
    private const VIDEO_COUNT = 8;
    private const PHOTO_COUNT = 12;
    private const AD_COUNT = 12;

    public function run(): void
    {
        [$editors, , $writers] = $this->seedUsers();
        $categories = $this->seedCategories();
        $tags = $this->seedTags();

        $this->seedArticles($writers, $editors, $categories, $tags);
        $this->seedAds();
        $this->fillMissingMedia();
    }

    private function seedUsers(): array
    {
        $editorNames = ['Raka Pratama', 'Nadia Permata', 'Bima Wicaksana'];
        $reviewerNames = ['Maya Santoso', 'Dimas Nugraha'];
        $writerNames = [
            'Ayu Lestari' => 'Meliput isu layanan publik, pendidikan, dan dinamika masyarakat perkotaan.',
            'Fajar Hidayat' => 'Menulis ekonomi, UMKM, pasar modal, dan transformasi bisnis lokal.',
            'Rani Kurnia' => 'Fokus pada teknologi, keamanan digital, dan kebijakan inovasi.',
            'Bagas Mahendra' => 'Mengikuti perkembangan olahraga nasional, otomotif, dan komunitas muda.',
            'Sinta Maharani' => 'Menyusun laporan gaya hidup, kesehatan, hiburan, dan budaya populer.',
        ];

        $editors = collect();
        foreach ($editorNames as $index => $name) {
            $user = User::query()->updateOrCreate(
                ['email' => 'editor'.($index + 1).'@example.com'],
                [
                    'name' => $name,
                    'password' => Hash::make('Password@12345!'),
                ]
            );
            $user->assignRole('Editor');
            $editors->push($user);
        }

        $reviewers = collect();
        foreach ($reviewerNames as $index => $name) {
            $user = User::query()->updateOrCreate(
                ['email' => 'reviewer'.($index + 1).'@example.com'],
                [
                    'name' => $name,
                    'password' => Hash::make('Password@12345!'),
                ]
            );
            $user->assignRole('Reviewer');
            $reviewers->push($user);
        }

        $writers = collect();
        foreach ($writerNames as $name => $bio) {
            $index = $writers->count() + 1;
            $user = User::query()->updateOrCreate(
                ['email' => "wartawan{$index}@example.com"],
                [
                    'name' => $name,
                    'password' => Hash::make('Password@12345!'),
                ]
            );
            $user->assignRole('Wartawan');
            $writers->push($user);

            AuthorProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => $bio,
                    'twitter' => null,
                    'instagram' => null,
                    'website' => null,
                ]
            );
        }

        return [$editors, $reviewers, $writers];
    }

    private function seedCategories(): Collection
    {
        $categories = [
            'Nasional' => 'Berita kebijakan publik, pemerintahan, pendidikan, dan layanan warga di Indonesia.',
            'Internasional' => 'Laporan kawasan, diplomasi, ekonomi global, dan isu lintas negara.',
            'Ekonomi' => 'Perkembangan pasar, UMKM, industri, investasi, dan keuangan keluarga.',
            'Teknologi' => 'Inovasi digital, keamanan siber, startup, perangkat, dan kebijakan teknologi.',
            'Olahraga' => 'Agenda kompetisi, prestasi atlet, komunitas olahraga, dan analisis pertandingan.',
            'Hiburan' => 'Film, musik, panggung kreatif, figur publik, dan industri budaya populer.',
            'Lifestyle' => 'Gaya hidup sehat, kuliner, perjalanan, keluarga, dan ruang kota.',
            'Kesehatan' => 'Layanan medis, pencegahan penyakit, kesehatan mental, dan kebijakan kesehatan.',
            'Otomotif' => 'Mobil, motor, kendaraan listrik, keselamatan berkendara, dan industri otomotif.',
            'Sains' => 'Riset, iklim, energi, lingkungan, antariksa, dan pengetahuan terapan.',
        ];

        return collect($categories)->map(function (string $description, string $name) {
            return Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $description,
                    'is_active' => true,
                ]
            );
        })->values();
    }

    private function seedTags(): Collection
    {
        $tagNames = [
            'Pemilu', 'Kebijakan Publik', 'Pendidikan', 'Transportasi', 'UMKM', 'Bursa',
            'Startup', 'AI', 'Keamanan Siber', 'Sepak Bola', 'Bulutangkis', 'Film',
            'Musik', 'Kuliner', 'Kesehatan Mental', 'Rumah Sakit', 'Mobil Listrik',
            'Motor Baru', 'Riset', 'Iklim', 'Energi', 'Ekspor', 'Investasi',
            'Kota Cerdas', 'Digitalisasi', 'Komunitas', 'Pariwisata', 'Lingkungan',
            'Infrastruktur', 'Data',
        ];

        return collect($tagNames)->map(function (string $name) {
            return Tag::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        })->values();
    }

    private function seedArticles(
        Collection $writers,
        Collection $editors,
        Collection $categories,
        Collection $tags
    ): void {
        $draftCycle = [
            ArticleStatus::Draft,
            ArticleStatus::Submitted,
            ArticleStatus::Review,
            ArticleStatus::Revision,
            ArticleStatus::Archived,
        ];

        for ($i = 1; $i <= self::ARTICLE_COUNT; $i++) {
            $author = $writers[($i - 1) % $writers->count()];
            $editor = $editors[($i + 1) % $editors->count()];
            $category = $categories[($i - 1) % $categories->count()];
            $status = $i <= 66
                ? ArticleStatus::Published
                : $draftCycle[($i - 67) % count($draftCycle)];

            $title = $this->articleTitle($category->name, $i);
            $excerpt = $this->articleExcerpt($category->name, $title, $i);
            $content = $this->articleContent($category->name, $title, $i);
            $coverPath = $this->editorialImage($category->name, $title, $i);
            $publishedAt = null;
            $seoTitle = null;
            $seoDesc = null;

            if ($status === ArticleStatus::Published) {
                $publishedAt = Carbon::now()->subHours(($i - 1) * 6);
                $seoTitle = $title;
                $seoDesc = $excerpt;
            } elseif ($status === ArticleStatus::Archived) {
                $publishedAt = Carbon::now()->subDays(20 + $i)->subHours(($i * 3) % 24);
                $seoTitle = $title;
                $seoDesc = $excerpt;
            }

            $article = Article::query()->updateOrCreate(
                ['slug' => Str::slug($title).'-'.$i],
                [
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'content_html' => $content,
                    'cover_image_path' => $coverPath,
                    'author_id' => $author->id,
                    'editor_id' => $editor->id,
                    'category_id' => $category->id,
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'reading_time' => 3 + ($i % 6),
                    'view_count' => 1200 + (($i * 317) % 18000),
                    'share_count' => 40 + (($i * 29) % 1400),
                    'is_breaking' => ($status === ArticleStatus::Published) && ($i % 9 === 0 || $i <= 6),
                    'is_featured' => ($status === ArticleStatus::Published) && ($i % 5 === 0 || $i <= 8),
                    'seo_title' => $seoTitle,
                    'seo_desc' => $seoDesc,
                    'canonical_url' => null,
                ]
            );

            $article->tags()->sync($this->tagIdsForArticle($tags, $i));
            $this->seedArticleViews($article->id, $i);
            $this->seedArticleShares($article->id, $i);
        }
    }

    private function articleTitle(string $category, int $index): string
    {
        $titles = [
            'Nasional' => [
                'Pemerintah Siapkan Integrasi Data Bantuan Sosial Antar Daerah',
                'Sekolah Negeri Mulai Terapkan Kalender Belajar Berbasis Proyek',
                'Transportasi Publik Perkotaan Diperluas ke Kawasan Penyangga',
                'Kementerian Dorong Layanan Administrasi Desa Masuk Platform Digital',
                'DPR Bahas Skema Perlindungan Pekerja Informal di Kota Besar',
                'Program Renovasi Pasar Rakyat Ditargetkan Rampung Bertahap',
                'Pemda Perkuat Posko Cuaca Ekstrem Menjelang Musim Hujan',
                'Kampus Vokasi Digandeng untuk Menjawab Kebutuhan Industri Lokal',
                'Layanan Kependudukan Bergerak Dibuka di Terminal dan Pusat Belanja',
            ],
            'Internasional' => [
                'Negara ASEAN Bahas Koridor Logistik Hijau di Pertemuan Regional',
                'Harga Pangan Global Stabil Setelah Jalur Distribusi Mulai Pulih',
                'Uni Eropa Perketat Standar Keamanan Data untuk Platform Digital',
                'Delegasi Indonesia Dorong Kerja Sama Energi Bersih di Asia Timur',
                'Kota Pesisir Dunia Tukar Strategi Menghadapi Kenaikan Muka Laut',
                'Bursa Asia Menguat Setelah Sinyal Pemangkasan Suku Bunga',
                'Organisasi Kesehatan Dunia Soroti Akses Layanan Primer di Daerah Terpencil',
                'Forum Pendidikan Global Bahas Kurikulum Keterampilan Digital',
                'Diplomat Muda Indonesia Ikuti Program Kepemimpinan Kawasan',
            ],
            'Ekonomi' => [
                'Pelaku UMKM Kuliner Naik Kelas Lewat Pembayaran Digital Terpadu',
                'Rupiah Bergerak Stabil Seiring Minat Investor ke Surat Utang Negara',
                'Industri Tekstil Lokal Fokus Produk Bernilai Tambah Tinggi',
                'Koperasi Modern Perluas Akses Pembiayaan untuk Petani Muda',
                'Ekspor Produk Perikanan Olahan Tumbuh dari Pelabuhan Timur',
                'Bank Digital Memperketat Edukasi Keamanan Transaksi Nasabah',
                'Harga Bahan Pokok Dipantau Menjelang Libur Panjang',
                'Kawasan Industri Baru Siapkan Insentif untuk Manufaktur Hijau',
                'Pengusaha Ritel Optimistis Belanja Rumah Tangga Tetap Kuat',
            ],
            'Teknologi' => [
                'Startup Lokal Rilis Platform Analitik untuk Toko Online Kecil',
                'Pakar Keamanan Siber Ingatkan Risiko Phishing Berkedok Undangan Digital',
                'Kampus dan Industri Bentuk Lab AI untuk Bahasa Indonesia',
                'Aplikasi Transportasi Uji Fitur Rute Hemat Emisi di Kota Besar',
                'Pemerintah Daerah Mulai Migrasi Arsip Publik ke Cloud Nasional',
                'Komunitas Developer Kenalkan Praktik Aksesibilitas Web ke Sekolah',
                'Produsen Perangkat Rilis Ponsel Kelas Menengah dengan Baterai Lebih Awet',
                'Sistem Pembayaran QR Dikembangkan untuk Pasar Tradisional',
                'Peneliti Bangun Model Deteksi Banjir dari Data Sensor Warga',
            ],
            'Olahraga' => [
                'Timnas U-23 Fokus Pemulihan Fisik Jelang Laga Penentuan',
                'Atlet Bulutangkis Muda Indonesia Menembus Final Turnamen Asia',
                'Liga Komunitas Sepak Bola Perempuan Digelar di Empat Kota',
                'Pelatih Renang Nasional Uji Program Latihan Berbasis Data',
                'Klub Basket Ibu Kota Rekrut Pemain Akademi untuk Musim Baru',
                'Federasi Sepeda Siapkan Jalur Seleksi Atlet Downhill Junior',
                'Ajang Lari Kota Tua Dorong Kampanye Transportasi Publik',
                'Pemain Futsal Daerah Masuk Pemusatan Latihan Nasional',
                'Komite Olahraga Evaluasi Kesiapan Venue Multi Event',
            ],
            'Hiburan' => [
                'Film Drama Keluarga Lokal Raih Respons Hangat di Festival Regional',
                'Musisi Muda Rilis Album Pop dengan Sentuhan Instrumen Tradisional',
                'Serial Komedi Baru Mengangkat Cerita Pekerja Kreatif Jakarta',
                'Sutradara Dokumenter Menyorot Perjalanan Komunitas Pesisir',
                'Konser Amal Musisi Lintas Generasi Kumpulkan Dana Pendidikan',
                'Rumah Produksi Siapkan Adaptasi Novel Terlaris ke Layar Lebar',
                'Pameran Seni Digital Menarik Pengunjung Keluarga Muda',
                'Aktor Pendatang Baru Mencuri Perhatian Lewat Peran Sosial',
                'Platform Streaming Lokal Tambah Kurasi Film Indonesia Klasik',
            ],
            'Lifestyle' => [
                'Tren Bekal Sehat Mendorong Tumbuhnya Dapur Rumahan Kreatif',
                'Kafe Berkonsep Taman Kota Menjadi Ruang Kerja Alternatif',
                'Komunitas Jalan Kaki Pagi Menghidupkan Trotoar Pusat Kota',
                'Panduan Mengatur Keuangan Keluarga Saat Harga Kebutuhan Naik',
                'Desainer Lokal Memakai Material Daur Ulang untuk Koleksi Harian',
                'Destinasi Desa Wisata Mulai Menawarkan Paket Belajar Budaya',
                'Pola Tidur Teratur Jadi Fokus Kampanye Produktivitas Anak Muda',
                'Menu Nusantara Modern Masuk Daftar Favorit Restoran Hotel',
                'Ruang Publik Ramah Anak Diperluas di Kawasan Hunian Padat',
            ],
            'Kesehatan' => [
                'Puskesmas Perkotaan Perkuat Skrining Kesehatan Mental Remaja',
                'Dokter Ingatkan Pentingnya Vaksinasi Lengkap untuk Lansia',
                'Rumah Sakit Daerah Uji Sistem Antrean Online untuk Poli Spesialis',
                'Kampanye Gizi Seimbang Sasar Kantin Sekolah dan Pesantren',
                'Ahli Paru Mendorong Pemantauan Kualitas Udara di Ruang Kelas',
                'Layanan Telemedisin Diperluas untuk Konsultasi Penyakit Kronis',
                'Kader Posyandu Diberi Pelatihan Deteksi Dini Stunting',
                'Komunitas Penyintas Berbagi Strategi Mengelola Stres Kerja',
                'Apotek Edukasi Penggunaan Obat Aman bagi Keluarga',
            ],
            'Otomotif' => [
                'Produsen Mobil Listrik Lokal Perluas Jaringan Pengisian Cepat',
                'Motor Matic Baru Mengusung Fitur Keselamatan untuk Pengendara Harian',
                'Bengkel Independen Mulai Adopsi Diagnostik Digital Kendaraan',
                'Komunitas Mobil Klasik Gelar Tur Edukasi Keselamatan Jalan',
                'Dealer Siapkan Program Tukar Tambah Kendaraan Hemat Energi',
                'Regulasi Emisi Baru Mendorong Inovasi Mesin Lebih Efisien',
                'Tren Aksesori Kendaraan Pintar Tumbuh di Kalangan Urban',
                'Pelatihan Eco Driving Diperkenalkan untuk Pengemudi Logistik',
                'Pasar Motor Bekas Tetap Aktif Berkat Pembiayaan Fleksibel',
            ],
            'Sains' => [
                'Peneliti Indonesia Mengembangkan Peta Risiko Panas Perkotaan',
                'Observatorium Nasional Catat Aktivitas Meteor di Langit Selatan',
                'Riset Rumput Laut Ditargetkan Menjadi Bahan Baku Bioplastik',
                'Mahasiswa Teknik Uji Turbin Mikro untuk Desa Terpencil',
                'Ilmuwan Iklim Memetakan Dampak El Nino pada Produksi Pangan',
                'Ekspedisi Sungai Mengukur Kualitas Air di Kawasan Industri',
                'Laboratorium Kampus Kembangkan Sensor Murah untuk Petani',
                'Program Sains Warga Ajak Pelajar Mengamati Keanekaragaman Burung',
                'Penelitian Baterai Natrium Mulai Dilirik untuk Penyimpanan Energi',
            ],
        ];

        $pool = $titles[$category] ?? $titles['Nasional'];
        return $pool[($index - 1) % count($pool)];
    }

    private function articleExcerpt(string $category, string $title, int $index): string
    {
        $focus = $this->categoryFocus($category);
        $area = $this->locationName($index);

        return "{$title} menjadi perhatian di {$area} karena berdampak langsung pada {$focus}. Redaksi merangkum konteks, respons pemangku kepentingan, dan langkah lanjutan yang perlu dipantau pembaca.";
    }

    private function articleContent(string $category, string $title, int $index): string
    {
        $area = $this->locationName($index);
        $focus = $this->categoryFocus($category);
        $source = $this->sourceName($category);
        $program = $this->programName($category, $index);
        $impact = $this->impactSentence($category, $area);

        $paragraphs = [
            "{$source} menyebut {$title} sebagai agenda yang mulai terlihat dampaknya di {$area}. Perubahan tersebut tidak berdiri sendiri karena berkaitan dengan kebutuhan warga, kesiapan layanan, dan koordinasi antar lembaga di lapangan.",
            "Dalam pemantauan redaksi, perhatian utama berada pada {$focus}. Sejumlah pelaku di sektor terkait menilai kebijakan dan program terbaru perlu disertai jadwal pelaksanaan yang terbuka agar masyarakat dapat menilai hasilnya secara lebih jelas.",
            "{$impact} Warga juga berharap informasi resmi disampaikan dengan bahasa sederhana, terutama untuk keputusan yang memengaruhi aktivitas harian, biaya layanan, atau akses terhadap fasilitas publik.",
            "Tim pelaksana menyiapkan {$program} sebagai langkah awal. Evaluasi akan dilakukan bertahap melalui data penggunaan, laporan warga, dan masukan komunitas agar perbaikan tidak berhenti pada tahap peluncuran.",
        ];

        return collect($paragraphs)
            ->map(fn (string $paragraph) => "<p>{$paragraph}</p>")
            ->implode('')
            .'<h2>Hal yang perlu diperhatikan</h2>'
            .'<ul>'
            .'<li>Transparansi jadwal dan target pelaksanaan menjadi faktor penting agar publik dapat mengikuti progres.</li>'
            .'<li>Data lapangan perlu diperbarui secara berkala supaya kebijakan tidak hanya terlihat baik di atas kertas.</li>'
            .'<li>Partisipasi komunitas lokal membantu menemukan masalah kecil sebelum berkembang menjadi hambatan besar.</li>'
            .'</ul>'
            ."<p>Redaksi Simbiosis News akan terus memantau perkembangan isu ini, terutama dampaknya terhadap {$focus} dan kualitas layanan di {$area}.</p>";
    }

    private function seedArticleViews(int $articleId, int $index): void
    {
        for ($day = 0; $day < 7; $day++) {
            ArticleViewDaily::query()->updateOrCreate(
                [
                    'article_id' => $articleId,
                    'viewed_date' => Carbon::now()->subDays($day)->toDateString(),
                ],
                ['count' => 18 + (($articleId * 11 + $index * 7 + $day * 13) % 110)]
            );
        }
    }

    private function seedArticleShares(int $articleId, int $index): void
    {
        $shareChannels = ['wa', 'fb', 'x', 'telegram'];

        Share::query()->where('article_id', $articleId)->delete();

        $shareCount = ($index * 2) % 9;
        for ($share = 0; $share < $shareCount; $share++) {
            Share::query()->create([
                'article_id' => $articleId,
                'channel' => $shareChannels[($index + $share) % count($shareChannels)],
                'created_at' => Carbon::now()->subDays(($index + $share) % 7)->subHours(($index * 3 + $share) % 24),
            ]);
        }
    }


    private function seedAds(): void
    {
        $slots = ['header', 'sidebar', 'in_article', 'footer'];
        $titles = [
            'Simbiosis Partner - Literasi Digital',
            'Kelas Bisnis Lokal untuk UMKM',
            'Festival Kuliner Nusantara Akhir Pekan',
            'Paket Data Keluarga Produktif',
            'Tabungan Pendidikan Generasi Baru',
            'Ruang Kerja Fleksibel Pusat Kota',
            'Asuransi Kesehatan Keluarga Muda',
            'Program Tukar Tambah Kendaraan Hemat Energi',
            'Kursus Bahasa untuk Profesional',
            'Perjalanan Desa Wisata Berkelanjutan',
            'Perangkat Rumah Pintar Hemat Listrik',
            'Konferensi Startup dan Inovasi Lokal',
        ];

        foreach ($titles as $index => $title) {
            $i = $index + 1;
            AdsBanner::query()->updateOrCreate(
                ['title' => $title],
                [
                    'slot' => $slots[$index % count($slots)],
                    'image_path' => $this->editorialImage('iklan', $title, $i + 320, 1280, 420),
                    'target_url' => 'https://example.com',
                    'is_active' => true,
                    'starts_at' => Carbon::now()->subDays(3),
                    'ends_at' => Carbon::now()->addDays(10),
                ]
            );
        }
    }

    private function tagIdsForArticle(Collection $tags, int $index): array
    {
        $count = 2 + ($index % 4);
        $start = ($index * 3) % $tags->count();
        $ids = [];

        for ($i = 0; $i < $count; $i++) {
            $ids[] = $tags[($start + $i) % $tags->count()]->id;
        }

        return $ids;
    }

    private function categoryFocus(string $category): string
    {
        return [
            'Nasional' => 'akses layanan publik dan kualitas kebijakan pemerintah',
            'Internasional' => 'hubungan kawasan, perdagangan, dan mobilitas warga',
            'Ekonomi' => 'daya beli, lapangan kerja, dan keberlanjutan usaha',
            'Teknologi' => 'keamanan data, efisiensi layanan, dan literasi digital',
            'Olahraga' => 'pembinaan atlet, partisipasi komunitas, dan prestasi daerah',
            'Hiburan' => 'ekosistem kreatif dan ruang apresiasi karya lokal',
            'Lifestyle' => 'kebiasaan harian, keluarga, dan kualitas ruang hidup',
            'Kesehatan' => 'pencegahan penyakit dan pemerataan layanan medis',
            'Otomotif' => 'mobilitas aman, efisiensi energi, dan layanan purna jual',
            'Sains' => 'riset terapan, lingkungan, dan pemanfaatan data ilmiah',
        ][$category] ?? 'kepentingan publik';
    }

    private function locationName(int $index): string
    {
        $locations = [
            'Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Semarang', 'Medan',
            'Makassar', 'Denpasar', 'Palembang', 'Balikpapan', 'Malang', 'Solo',
        ];

        return $locations[($index - 1) % count($locations)];
    }

    private function sourceName(string $category): string
    {
        return [
            'Nasional' => 'Pemerintah pusat',
            'Internasional' => 'Perwakilan diplomatik Indonesia',
            'Ekonomi' => 'Pelaku industri dan analis pasar',
            'Teknologi' => 'Komunitas teknologi dan regulator digital',
            'Olahraga' => 'Pengurus cabang olahraga',
            'Hiburan' => 'Pelaku industri kreatif',
            'Lifestyle' => 'Komunitas warga dan pelaku usaha lokal',
            'Kesehatan' => 'Tenaga kesehatan dan pengelola fasilitas medis',
            'Otomotif' => 'Pelaku industri otomotif',
            'Sains' => 'Peneliti dan akademisi',
        ][$category] ?? 'Narasumber terkait';
    }

    private function programName(string $category, int $index): string
    {
        $programs = [
            'Nasional' => ['dashboard layanan warga', 'forum konsultasi publik', 'posko respons cepat'],
            'Internasional' => ['meja kerja sama kawasan', 'pertukaran data kebijakan', 'agenda diplomasi tematik'],
            'Ekonomi' => ['klinik pembiayaan usaha', 'kurasi produk lokal', 'dashboard harga komoditas'],
            'Teknologi' => ['uji coba sistem terbuka', 'kelas keamanan digital', 'pusat bantuan pengguna'],
            'Olahraga' => ['program pembinaan usia muda', 'monitor performa atlet', 'kalender kompetisi daerah'],
            'Hiburan' => ['laboratorium kreator muda', 'kurasi karya independen', 'panggung komunitas'],
            'Lifestyle' => ['aktivasi ruang publik', 'kelas kebiasaan sehat', 'program belanja lokal'],
            'Kesehatan' => ['layanan skrining bergerak', 'edukasi keluarga sehat', 'rujukan digital terpantau'],
            'Otomotif' => ['uji rute harian', 'pelatihan keselamatan berkendara', 'pusat servis terjadwal'],
            'Sains' => ['observasi berbasis warga', 'peta data terbuka', 'prototipe riset terapan'],
        ];
        $pool = $programs[$category] ?? ['program pemantauan berkala'];

        return $pool[$index % count($pool)];
    }

    private function impactSentence(string $category, string $area): string
    {
        return [
            'Nasional' => "Di {$area}, warga menilai kepastian layanan lebih penting daripada sekadar pengumuman program baru.",
            'Internasional' => "Dampaknya mulai dibaca oleh pelaku usaha di {$area} yang memiliki jaringan ekspor dan perjalanan lintas negara.",
            'Ekonomi' => "Pelaku usaha di {$area} melihat ruang pertumbuhan, tetapi tetap menunggu stabilitas biaya operasional.",
            'Teknologi' => "Pengguna di {$area} menyambut fitur baru selama perlindungan data dan bantuan teknis mudah diakses.",
            'Olahraga' => "Komunitas olahraga di {$area} berharap pembinaan tidak hanya muncul menjelang kompetisi besar.",
            'Hiburan' => "Pelaku kreatif di {$area} menilai dukungan distribusi karya sama pentingnya dengan panggung pertunjukan.",
            'Lifestyle' => "Warga di {$area} mulai mencari pilihan yang praktis, sehat, dan tetap sesuai dengan anggaran keluarga.",
            'Kesehatan' => "Fasilitas kesehatan di {$area} menekankan pentingnya edukasi sebelum keluhan pasien menumpuk.",
            'Otomotif' => "Pengendara di {$area} mulai memperhitungkan efisiensi energi dan ketersediaan layanan perawatan.",
            'Sains' => "Akademisi di {$area} mendorong hasil riset diterjemahkan menjadi solusi yang dapat diuji langsung.",
        ][$category] ?? "Warga di {$area} menunggu manfaat yang dapat dirasakan dalam kegiatan sehari-hari.";
    }

    private function fillMissingMedia(): void
    {
        Article::query()
            ->where(function ($query) {
                $query->whereNull('cover_image_path')->orWhere('cover_image_path', '');
            })
            ->orderBy('id')
            ->get()
            ->each(function (Article $article, int $index) {
                $article->forceFill([
                    'cover_image_path' => $this->editorialImage($article->category?->name ?? 'nasional', $article->title, $index + 1),
                ])->save();
            });

        Video::query()
            ->where(function ($query) {
                $query->whereNull('cover_image_path')->orWhere('cover_image_path', '');
            })
            ->orderBy('id')
            ->get()
            ->each(function (Video $video, int $index) {
                $video->forceFill([
                    'cover_image_path' => $this->editorialImage('video', $video->title, $index + 111),
                ])->save();
            });

        Photo::query()
            ->where(function ($query) {
                $query->whereNull('image_path')->orWhere('image_path', '');
            })
            ->orderBy('id')
            ->get()
            ->each(function (Photo $photo, int $index) {
                $photo->forceFill([
                    'image_path' => $this->editorialImage('foto', $photo->title, $index + 211),
                ])->save();
            });

        AdsBanner::query()
            ->where(function ($query) {
                $query->whereNull('image_path')->orWhere('image_path', '');
            })
            ->orderBy('id')
            ->get()
            ->each(function (AdsBanner $banner, int $index) {
                $banner->forceFill([
                    'image_path' => $this->editorialImage('iklan', $banner->title, $index + 311, 1280, 420),
                ])->save();
            });
    }

    private function editorialImage(string $kind, string $title, int $offset = 1, int $width = 1280, int $height = 800): string
    {
        return EditorialImageFactory::make($kind, $title, $offset, $width, $height);
    }
}
