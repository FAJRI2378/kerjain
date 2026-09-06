<?php

namespace Database\Seeders;

use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Models\JobCategory;
use App\Models\Rating;
use App\Models\Task;
use App\Models\TaskApplication;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Console\SeedingException;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Task::query()->exists()) {
            return;
        }

        $categories = JobCategory::query()->pluck('id', 'slug');
        $hirers = [
            'budi' => $this->user('budi@umkm.id', 'Budi Santoso', 'hirer'),
            'sari' => $this->user('sari@umkm.id', 'Sari Rahmawati', 'hirer'),
            'dedi' => $this->user('dedi@umkm.id', 'Dedi Firmansyah', 'hirer'),
            'lina' => $this->user('lina@umkm.id', 'Lina Susanti', 'hirer'),
        ];
        $workers = [
            'andi' => $this->user('andi@worker.id', 'Andi Pratama', 'freelancer'),
            'rizky' => $this->user('rizky@worker.id', 'Rizky Ramadhan', 'freelancer'),
            'nadia' => $this->user('nadia@worker.id', 'Nadia Putri', 'freelancer'),
            'bima' => $this->user('bima@worker.id', 'Bima Saputra', 'freelancer'),
        ];

        $this->businessProfile($hirers['budi'], [
            'business_name' => 'Soto Pak Budi',
            'category' => 'Kuliner / F&B',
            'business_type' => 'Kuliner / F&B (Resto, Cafe, Kedai)',
            'address' => 'Jl. Sudirman No. 45, Menteng, Jakarta Pusat',
            'phone' => '081234567801',
        ]);
        $this->businessProfile($hirers['sari'], [
            'business_name' => 'Kedai Kopi Senja',
            'category' => 'Kuliner / F&B',
            'business_type' => 'Kuliner / F&B (Resto, Cafe, Kedai)',
            'address' => 'Jl. Malioboro No. 88, Yogyakarta',
            'phone' => '081234567802',
        ]);
        $this->businessProfile($hirers['dedi'], [
            'business_name' => 'Toko Elektronik Jaya',
            'category' => 'Retail & Toko Kelontong',
            'business_type' => 'Retail & Toko Kelontong',
            'address' => 'Jl. Braga No. 12, Bandung',
            'phone' => '081234567803',
        ]);
        $this->businessProfile($hirers['lina'], [
            'business_name' => 'Butik Anggrek',
            'category' => 'Fashion & Konveksi',
            'business_type' => 'Fashion & Konveksi',
            'address' => 'Jl. Tunjungan No. 20, Surabaya',
            'phone' => '081234567804',
        ]);

        foreach ($hirers as $hirer) {
            $this->seedWallet($hirer, WalletTransaction::TYPE_TOPUP, 'Top-up saldo escrow', 500000);
        }

        $tasks = [
            // Open — tampil di landing page
            array_merge($this->task('Foto Produk & Upload 20 Menu', 'fotografi', 75000, $hirers['budi'], Task::STATUS_APPROVED), [
                'description' => 'Foto 20 menu andalan dan unggah lengkap dengan nama serta harga ke katalog online.',
                'location' => 'Jl. Sudirman No. 45, Menteng, Jakarta Pusat',
                'deadline' => '3 jam',
                'days_ago' => 1,
                'applicants' => ['nadia', 'rizky'],
            ]),
            array_merge($this->task('Desain Poster Promosi Ramadan', 'desain', 120000, $hirers['dedi'], Task::STATUS_APPROVED), [
                'description' => 'Buat poster promosi Ramadan ukuran 1:1 dan story, siap cetak dan dibagikan ke media sosial.',
                'location' => 'Jl. Braga No. 12, Bandung',
                'deadline' => '1 hari',
                'days_ago' => 2,
                'applicants' => ['andi', 'nadia', 'bima'],
            ]),
            array_merge($this->task('Input 200 Data Produk ke Excel', 'admin', 100000, $hirers['lina'], Task::STATUS_APPROVED), [
                'description' => 'Rapikan dan input 200 data produk stok ke spreadsheet dengan format yang sudah disiapkan.',
                'location' => 'Jl. Tunjungan No. 20, Surabaya',
                'deadline' => '1 hari',
                'days_ago' => 2,
                'applicants' => ['andi', 'rizky'],
            ]),
            array_merge($this->task('Rekam Video Promosi Menu Singkat', 'pemasaran', 150000, $hirers['sari'], Task::STATUS_APPROVED), [
                'description' => 'Buat video pendek 30-45 detik promosi menu favorit untuk feed dan reels.',
                'location' => 'Jl. Malioboro No. 88, Yogyakarta',
                'deadline' => '2 hari',
                'days_ago' => 3,
                'applicants' => ['bima', 'nadia'],
            ]),
            array_merge($this->task('Kelola Katalog WhatsApp Business', 'admin', 90000, $hirers['budi'], Task::STATUS_APPROVED), [
                'description' => 'Susun katalog produk di WhatsApp Business agar rapi, lengkap dengan foto dan harga.',
                'location' => 'Jl. Sudirman No. 45, Menteng, Jakarta Pusat',
                'deadline' => '1 minggu',
                'days_ago' => 4,
                'applicants' => ['andi'],
            ]),
            array_merge($this->task('Terjemahkan Menu ke Bahasa Inggris', 'admin', 85000, $hirers['sari'], Task::STATUS_APPROVED), [
                'description' => 'Terjemahkan 25 menu ke bahasa Inggris dengan nada santai, siap diletakkan di meja.',
                'location' => 'Jl. Malioboro No. 88, Yogyakarta',
                'deadline' => '2 hari',
                'days_ago' => 4,
                'applicants' => ['nadia'],
            ]),
            array_merge($this->task('Packing & Labeling 50 Paket', 'admin', 80000, $hirers['dedi'], Task::STATUS_APPROVED), [
                'description' => 'Bantu packing dan pasang label alamat untuk 50 paket pesanan sebelum dijemput kurir.',
                'location' => 'Jl. Braga No. 12, Bandung',
                'deadline' => '3 jam',
                'days_ago' => 5,
                'applicants' => ['rizky'],
            ]),
            array_merge($this->task('Live Host TikTok 2 Jam', 'live-host', 200000, $hirers['lina'], Task::STATUS_APPROVED), [
                'description' => 'Jadi host live jualan di TikTok selama 2 jam. Rajin, ramah, dan berani bicara di depan kamera.',
                'location' => 'Jl. Tunjungan No. 20, Surabaya',
                'deadline' => '2 jam',
                'days_ago' => 5,
                'applicants' => ['bima'],
            ]),

            // Pending — menunggu moderasi admin
            array_merge($this->task('Upload 30 Menu ke GoFood', 'admin', 70000, $hirers['dedi'], Task::STATUS_PENDING), [
                'description' => 'Upload 30 menu ke GoFood merchant lengkap dengan foto dan harga.',
                'location' => 'Jl. Braga No. 12, Bandung',
                'deadline' => '1 hari',
                'days_ago' => 0,
            ]),
            array_merge($this->task('Buat Logo Warung Kopi', 'desain', 100000, $hirers['lina'], Task::STATUS_PENDING), [
                'description' => 'Desain logo warung kopi modern, minimalis, dikirim dalam format vektor.',
                'location' => 'Jl. Tunjungan No. 20, Surabaya',
                'deadline' => '2 hari',
                'days_ago' => 0,
            ]),

            // Rejected
            array_merge($this->task('Desain Banner Toko', 'desain', 90000, $hirers['budi'], Task::STATUS_REJECTED), [
                'description' => 'Buat banner lebaran.',
                'location' => 'Jl. Sudirman No. 45, Menteng, Jakarta Pusat',
                'days_ago' => 10,
                'rejection_reason' => 'Deskripsi pekerjaan terlalu singkat dan budget tidak sesuai skala pekerjaan. Mohon perbaiki lalu ajukan ulang.',
            ]),
            array_merge($this->task('Bantu Bongkar Barang Gudang', 'admin', 75000, $hirers['sari'], Task::STATUS_REJECTED), [
                'description' => 'Bantu bongkar dan susun barang dari gudang ke rak toko.',
                'location' => 'Jl. Malioboro No. 88, Yogyakarta',
                'days_ago' => 12,
                'rejection_reason' => 'Kategori dan jenis pekerjaan tidak sesuai ketentuan platform untuk pekerjaan mikro.',
            ]),

            // In progress
            array_merge($this->task('Pembukuan Sederhana Usaha Kopi', 'admin', 130000, $hirers['sari'], Task::STATUS_IN_PROGRESS), [
                'description' => 'Rapikan pemasukan dan pengeluaran bulan ini ke buku catatan digital sederhana.',
                'location' => 'Jl. Malioboro No. 88, Yogyakarta',
                'deadline' => '1 minggu',
                'days_ago' => 6,
                'worker' => 'nadia',
            ]),
            array_merge($this->task('Pasang Google Business Profile', 'pemasaran', 110000, $hirers['dedi'], Task::STATUS_IN_PROGRESS), [
                'description' => 'Buat dan optimalkan Google Business Profile lengkap dengan foto toko dan jam operasional.',
                'location' => 'Jl. Braga No. 12, Bandung',
                'deadline' => '2 hari',
                'days_ago' => 7,
                'worker' => 'rizky',
            ]),

            // Reviewing — menunggu UMKM konfirmasi hasil
            array_merge($this->task('Buat Video Promosi Singkat', 'pemasaran', 160000, $hirers['budi'], Task::STATUS_REVIEWING), [
                'description' => 'Produksi video promosi 30 detik dengan voiceover dan teks singkat.',
                'location' => 'Jl. Sudirman No. 45, Menteng, Jakarta Pusat',
                'deadline' => '3 hari',
                'days_ago' => 8,
                'worker' => 'bima',
            ]),
            array_merge($this->task('Revisi Foto Katalog Menu', 'fotografi', 95000, $hirers['lina'], Task::STATUS_REVIEWING), [
                'description' => 'Rapikan ulang hasil foto katalog menu agar pencahayaan dan komposisi konsisten.',
                'location' => 'Jl. Tunjungan No. 20, Surabaya',
                'deadline' => '2 hari',
                'days_ago' => 9,
                'worker' => 'rizky',
            ]),

            // Completed
            array_merge($this->task('Foto Produk Ringan 20 Item', 'fotografi', 100000, $hirers['budi'], Task::STATUS_COMPLETED), [
                'description' => 'Memotret 20 item makanan ringan dengan latar putih untuk katalog.',
                'location' => 'Jl. Sudirman No. 45, Menteng, Jakarta Pusat',
                'deadline' => '3 jam',
                'days_ago' => 15,
                'worker' => 'andi',
            ]),
            array_merge($this->task('Desain Kartu Menu Stand Up', 'desain', 75000, $hirers['sari'], Task::STATUS_COMPLETED), [
                'description' => 'Desain kartu menu stand up ukuran A5 dengan harga dan deskripsi singkat.',
                'location' => 'Jl. Malioboro No. 88, Yogyakarta',
                'deadline' => '2 hari',
                'days_ago' => 18,
                'worker' => 'nadia',
            ]),
            array_merge($this->task('Input Data Pelanggan ke Spreadsheet', 'admin', 90000, $hirers['dedi'], Task::STATUS_COMPLETED), [
                'description' => 'Input 150 data pelanggan langganan dari catatan fisik ke spreadsheet.',
                'location' => 'Jl. Braga No. 12, Bandung',
                'deadline' => '1 hari',
                'days_ago' => 20,
                'worker' => 'andi',
            ]),

            // Cancelled
            array_merge($this->task('Foto Booth Bazar Sekolah', 'fotografi', 120000, $hirers['budi'], Task::STATUS_CANCELLED), [
                'description' => 'Dokumentasi booth bazar sekolah selama 4 jam. Acara dibatalkan karena jadwal bentrok.',
                'location' => 'Jl. Sudirman No. 45, Menteng, Jakarta Pusat',
                'deadline' => '5 jam',
                'days_ago' => 25,
            ]),
        ];

        $invoiceCounter = (int) Invoice::query()->count() + 1;

        foreach ($tasks as $taskData) {
            $owner = $taskData['owner'];
            $categoryId = $categories[$taskData['category']]
                ?? throw new SeedingException("Kategori seed '{$taskData['category']}' tidak ditemukan.");

            $task = Task::create([
                'owner_id' => $owner->id,
                'category_id' => $categoryId,
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'budget' => $taskData['budget'],
                'location' => $taskData['location'],
                'status' => $taskData['status'],
                'deadline' => $taskData['deadline'] ?? null,
                'rejection_reason' => $taskData['rejection_reason'] ?? null,
                'proof_url' => $taskData['proof_url'] ?? null,
                'worker_id' => isset($taskData['worker']) ? $workers[$taskData['worker']]->id : null,
                'created_at' => now()->subDays($taskData['days_ago'])->subMinutes(random_int(0, 300)),
                'updated_at' => now()->subDays($taskData['days_ago']),
            ]);

            foreach ($taskData['applicants'] ?? [] as $workerKey) {
                $this->apply($task, $workers[$workerKey], 'pending');
            }

            if (isset($taskData['worker'])) {
                $this->apply($task, $workers[$taskData['worker']], 'accepted');
            }

            if ($task->isCompleted() && $task->worker) {
                $this->settleCompletedTask($task, $invoiceCounter);
                $invoiceCounter++;

                $this->rate($task, $owner, $task->worker, 5, 'Hasil kerja rapi dan sesuai permintaan.');
                $this->rate($task, $task->worker, $owner, 4, 'Komunikasi UMKM jelas, dana cair lancar.');
            }
        }
    }

    private function user(string $email, string $name, string $role): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role' => $role,
                'password' => 'password',
                'is_verified' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }

    private function businessProfile(User $user, array $data): void
    {
        BusinessProfile::updateOrCreate(['user_id' => $user->id], $data);
    }

    private function task(string $title, string $category, int $budget, User $owner, string $status): array
    {
        return compact('title', 'category', 'budget', 'owner', 'status');
    }

    private function apply(Task $task, User $worker, string $status): void
    {
        TaskApplication::firstOrCreate(
            ['task_id' => $task->id, 'worker_id' => $worker->id],
            ['status' => $status]
        );
    }

    private function seedWallet(User $user, string $type, string $title, int $amount): Wallet
    {
        $wallet = Wallet::forUser($user->id);

        if ($type === WalletTransaction::TYPE_IN || $type === WalletTransaction::TYPE_TOPUP) {
            $wallet->increment('balance', $amount);
        } else {
            $wallet->decrement('balance', $amount);
        }

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'title' => $title,
            'amount' => $amount,
        ]);

        return $wallet;
    }

    private function settleCompletedTask(Task $task, int $invoiceCounter): void
    {
        $this->seedWallet($task->owner, WalletTransaction::TYPE_OUT, "Pembayaran tugas: {$task->title}", $task->budget);
        $this->seedWallet($task->worker, WalletTransaction::TYPE_IN, "Pendapatan tugas: {$task->title}", $task->budget);

        Invoice::create([
            'user_id' => $task->owner_id,
            'task_id' => $task->id,
            'invoice_number' => 'INV-'.now()->format('Y').'-'.str_pad((string) $invoiceCounter, 3, '0', STR_PAD_LEFT),
            'amount' => $task->budget,
            'status' => Invoice::STATUS_PAID,
            'issued_at' => now()->subDays($task->updated_at->diffInDays(now()) ?: 0)->addMinutes(30),
        ]);
    }

    private function rate(Task $task, User $reviewer, User $reviewee, int $rating, string $comment): void
    {
        Rating::firstOrCreate(
            ['task_id' => $task->id, 'reviewer_id' => $reviewer->id],
            ['reviewee_id' => $reviewee->id, 'rating' => $rating, 'comment' => $comment]
        );
    }
}