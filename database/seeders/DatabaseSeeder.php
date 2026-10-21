<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Komputer & Office', 'slug' => 'komputer-office', 'icon' => '💻', 'color' => '#4f46e5', 'sort_order' => 1,
                'description' => 'Kuasai Microsoft Office dan aplikasi produktivitas untuk kerja maupun kuliah.'],
            ['name' => 'Pemrograman', 'slug' => 'pemrograman', 'icon' => '🧑‍💻', 'color' => '#059669', 'sort_order' => 2,
                'description' => 'Belajar coding dari nol: web, mobile, hingga aplikasi bisnis.'],
            ['name' => 'Desain Grafis', 'slug' => 'desain-grafis', 'icon' => '🎨', 'color' => '#db2777', 'sort_order' => 3,
                'description' => 'Kelas desain profesional untuk kreator, marketer, dan pekerja lepas.'],
            ['name' => 'Jaringan Komputer', 'slug' => 'jaringan-komputer', 'icon' => '🌐', 'color' => '#0891b2', 'sort_order' => 4,
                'description' => 'Jadilah teknisi jaringan dan administrator server yang dicari perusahaan.'],
            ['name' => 'Multimedia', 'slug' => 'multimedia', 'icon' => '🎬', 'color' => '#d97706', 'sort_order' => 5,
                'description' => 'Kelas video editing, animasi, dan konten kreatif untuk era digital.'],
            ['name' => 'Akuntansi Digital', 'slug' => 'akuntansi-digital', 'icon' => '📊', 'color' => '#7c3aed', 'sort_order' => 6,
                'description' => 'Software akuntansi dan pelaporan keuangan untuk bisnis modern.'],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(['slug' => $data['slug']], $data);
        }

        $courses = [
            // Komputer & Office
            ['komputer-office', 'Ms. Word Lengkap: Dasar sampai Tingkat Mahir', 'ms-word-lengkap', 'Kuasai pemformatan, tabel, mail merge, dan otomatisasi dokumen profesional.', 'Rina Amelia, S.Kom.', 'Pemula', 250000, 175000, 12, 48, 4.9, 2340, true],
            ['komputer-office', 'Ms. Excel Bisnis: Formula, Pivot Table & VBA', 'ms-excel-bisnis', 'Analisis data cepat dengan rumus lanjutan, Power Query, dan macro VBA.', 'Agus Setiawan', 'Menengah', 320000, 240000, 16, 62, 4.9, 3120, true],
            ['komputer-office', 'Ms. PowerPoint untuk Presentasi Profesional', 'ms-powerpoint', 'Desain slide menarik, animasi halus, dan teknik presentasi yang meyakinkan.', 'Dewi Larasati', 'Pemula', 180000, null, 8, 32, 4.7, 1450, false],
            ['komputer-office', 'Google Workspace & Kolaborasi Tim Modern', 'google-workspace', 'Kerja tim efisien dengan Gmail, Docs, Sheets, dan Meet.', 'Rina Amelia, S.Kom.', 'Pemula', 150000, 99000, 6, 24, 4.6, 980, false],

            // Pemrograman
            ['pemrograman', 'Belajar PHP & Laravel dari Nol', 'php-laravel-nol', 'Bikin aplikasi web dinamis lengkap dengan autentikasi dan CRUD.', 'Fikri Ramadhan, S.T.', 'Pemula', 450000, 315000, 24, 96, 4.9, 4210, true],
            ['pemrograman', 'Full-Stack Web: HTML, CSS, JavaScript', 'fullstack-web', 'Bangun website responsif dari scratch hingga siap dipublikasikan.', 'Fikri Ramadhan, S.T.', 'Pemula', 400000, 320000, 20, 84, 4.8, 3870, true],
            ['pemrograman', 'Python untuk Pemula & Otomasi Tugas', 'python-pemula', 'Scripting, analisis data dasar, dan otomasi pekerjaan kantor.', 'Yoga Pratama', 'Pemula', 380000, 285000, 14, 58, 4.8, 2760, false],
            ['pemrograman', 'React.js: Membangun Aplikasi Modern', 'reactjs-modern', 'Komponen, state management, dan integrasi API REST.', 'Bima Akbar', 'Lanjutan', 550000, 440000, 18, 72, 4.7, 1680, false],
            ['pemrograman', 'Pemrograman Dasar Algoritma & Logika', 'algoritma-logika', 'Dasar berpikir komputasional untuk semua jalur karier IT.', 'Yoga Pratama', 'Pemula', 220000, null, 10, 40, 4.6, 1980, false],

            // Desain Grafis
            ['desain-grafis', 'Adobe Photoshop: Editing Foto Profesional', 'photoshop-profesional', 'Retouch, komposisi, dan desain konten media sosial.', 'Sinta Kirana', 'Pemula', 300000, 210000, 12, 50, 4.8, 2540, true],
            ['desain-grafis', 'CorelDRAW untuk Desain Cetak & Merchandise', 'coreldraw-desain', 'Logo, kartu nama, banner, dan siap cetak.', 'Hendra Wijaya', 'Pemula', 275000, 195000, 10, 42, 4.7, 1730, false],
            ['desain-grafis', 'Adobe Illustrator: Master Vektor & Logo', 'illustrator-vektor', 'Bikin logo skalabel dan ilustrasi vektor kelas profesional.', 'Sinta Kirana', 'Menengah', 350000, 280000, 14, 55, 4.8, 1420, false],
            ['desain-grafis', 'UI/UX Design dengan Figma', 'uiux-figma', 'Wireframe, prototype, dan design system untuk aplikasi.', 'Nadia Puspita', 'Menengah', 420000, 336000, 15, 60, 4.9, 2210, true],

            // Jaringan Komputer
            ['jaringan-komputer', 'Admin Jaringan Dasar: Cisco & TCP/IP', 'jaringan-dasar', 'Konfigurasi router, switch, subnetting, dan troubleshooting.', 'Doni Kurniawan', 'Pemula', 400000, 300000, 18, 70, 4.7, 1560, false],
            ['jaringan-komputer', 'MikroTik: Routing & Firewall Lanjutan', 'mikrotick-lanjutan', 'QoS, VPN, hotspot, dan keamanan jaringan perusahaan.', 'Doni Kurniawan', 'Lanjutan', 475000, 380000, 16, 64, 4.8, 940, false],
            ['jaringan-komputer', 'Instalasi & Administrasi Server Windows/Linux', 'server-admin', 'Domain controller, sharing, dan maintenance server kantor.', 'Arif Budiman', 'Menengah', 500000, 400000, 20, 78, 4.7, 1120, false],

            // Multimedia
            ['multimedia', 'Video Editing dengan Adobe Premiere', 'premiere-editing', 'Editing, color grading, audio, hingga export untuk YouTube & reels.', 'Rangga Saputra', 'Pemula', 350000, 245000, 14, 56, 4.8, 2890, true],
            ['multimedia', 'After Effects: Motion Graphics Pemula', 'after-effects', 'Animasi teks, infografis, dan efek visual keren.', 'Rangga Saputra', 'Menengah', 420000, 336000, 15, 58, 4.7, 1340, false],
            ['multimedia', 'Fotografi Digital & Editing', 'fotografi-digital', 'Kuasai komposisi, pencahayaan, dan editing foto.', 'Mega Lestari', 'Pemula', 280000, 196000, 10, 38, 4.6, 1210, false],

            // Akuntansi Digital
            ['akuntansi-digital', 'Accurate Online: Akuntansi untuk UMKM', 'accurate-umkm', 'Pembukuan, kas, piutang, hingga laporan keuangan otomatis.', 'Sari Wulandari, S.E.', 'Pemula', 320000, 256000, 12, 46, 4.8, 1640, false],
            ['akuntansi-digital', 'MYOB Accounting Lengkap', 'myob-lengkap', 'Setup perusahaan, transaksi, dan laporan keuangan standar.', 'Sari Wulandari, S.E.', 'Pemula', 300000, 225000, 11, 44, 4.7, 1390, false],
            ['akuntansi-digital', 'Excel untuk Akuntansi & Laporan Keuangan', 'excel-akuntansi', 'Template jurnal, neraca, dan laba rugi otomatis dengan Excel.', 'Sari Wulandari, S.E.', 'Menengah', 260000, null, 9, 36, 4.6, 870, false],
        ];

        foreach ($courses as [$catSlug, $title, $slug, $short, $instructor, $level, $price, $discount, $hours, $lessons, $rating, $students, $featured]) {
            $category = Category::where('slug', $catSlug)->first();

            Course::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'title' => $title,
                    'short_description' => $short,
                    'description' => "{$short}\n\nKelas ini dirancang praktis langsung dari kebutuhan dunia kerja. Anda akan belajar melalui materi video, latihan mandiri, dan proyek nyata yang bisa dijadikan portofolio. Setiap pertemuan dilengkapi latihan serta evaluasi dari instruktur berpengalaman di bidangnya.\n\nSetelah menyelesaikan kelas, Anda akan mendapatkan sertifikat kelulusan resmi dari Farafi Computer.",
                    'instructor' => $instructor,
                    'level' => $level,
                    'price' => $price,
                    'discount_price' => $discount,
                    'duration_hours' => $hours,
                    'lessons' => $lessons,
                    'rating' => $rating,
                    'students' => $students,
                    'is_featured' => $featured,
                ]
            );
        }

        // ============ Akun login per role ============
        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@faraficomputer.id', 'role' => 'super_admin', 'phone' => '081100000001', 'password' => 'superadmin123'],
            ['name' => 'Operator Toko', 'email' => 'operator@faraficomputer.id', 'role' => 'operator', 'phone' => '081100000002', 'password' => 'operator123'],
            ['name' => 'Budi Santoso', 'email' => 'murid@faraficomputer.id', 'role' => 'murid', 'phone' => '081100000003', 'password' => 'murid123'],
            ['name' => 'Siti Aminah', 'email' => 'siti@email.com', 'role' => 'murid', 'phone' => '081100000004', 'password' => 'murid123'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'role' => $data['role'],
                    'phone' => $data['phone'],
                    'password' => Hash::make($data['password']),
                ]
            );
        }
    }
}
