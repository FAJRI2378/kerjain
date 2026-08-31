# KERJAIN — Marketplace Pekerjaan Mikro

> Marketplace untuk kapasitas produktif yang menganggur melalui pekerjaan mikro lokal. Mempertemukan **UMKM** yang membutuhkan bantuan jangka pendek dengan **Worker/Freelancer** di sekitar lokasi.

Contoh: Soto Pak Budi membutuhkan seseorang selama 3 jam untuk memotret produk dan mengunggah 20 menu dengan budget Rp75.000 — Freelancer di sekitar melihat, mengambil pekerjaan, menyelesaikan, lalu mendapat rating dan riwayat kerja.

Repo ini adalah **monorepo** yang berisi dua aplikasi terpisah:

| Folder | Teknologi | Deskripsi |
| ------ | --------- | --------- |
| `backend/` | Laravel 13 + PHP + Sanctum + SQLite | REST API |
| `frontend/` | SvelteKit + Svelte 5 + Tailwind CSS | UI (admin / UMKM / freelancer) |

---

## Kebutuhan Sistem

- **PHP** >= 8.3
- **Composer** >= 2
- **Node.js** >= 20 dan **npm** (atau bun)
- Database: **SQLite** (default, tanpa konfigurasi server DB)

---

## Menyiapkan Project

### 1. Backend (Laravel API)

```bash
cd backend

composer install

# Buat file .env dari template
cp .env.example .env
# Windows: copy .env.example .env

# Generate application key
php artisan key:generate

# Buat database SQLite (jika belum ada)
php artisan migrate:fresh --seed
```

>`migrate:fresh --seed` membuat tabel dan mengisi data awal: kategori pekerjaan, pengaturan platform, dan 3 akun pengguna contoh (lihat tabel di bawah).

### 2. Frontend (SvelteKit)

Buka terminal **kedua**:

```bash
cd frontend

npm install

npm run dev
```

Frontend berjalan di `http://localhost:5173`.

Vite dev server sudah dikonfigurasi untuk **mem-proxy** request `/api/*` ke backend `http://127.0.0.1:8000` (lihat `frontend/vite.config.js`). Jadi frontend cukup memanggil `/api/...` tanpa perlu URL absolut.

---

## Menjalankan Project

Jalankan **dua server secara bersamaan** (dua terminal terpisah):

```bash
# Terminal 1: Backend
cd backend
php artisan serve --port=8000

# Terminal 2: Frontend
cd frontend
npm run dev
```

- Frontend: `http://localhost:5173`
- Backend API: `http://localhost:8000/api`

> **Penting:** jika backend `php artisan serve` tidak berjalan, frontend akan gagal memanggil API dan menampilkan `502 Bad Gateway`.

---

## Akun Demo (Setelah `migrate:fresh --seed`)

Semua password default: **`password`**

| Peran | Nama | Email | Halaman Masuk |
| ----- | ---- | ----- | -------------- |
| Admin | Admin KERJAIN | `admin@kerjain.id` | `/admin/login` |
| Hirer (UMKM) | Budi Santoso | `budi@umkm.id` | `/login` |
| Freelancer | Andi Pratama | `andi@worker.id` | `/login` |

> Catatan: **admin login via jalur terpisah** di `/admin/login` (tidak ada tab Admin di halaman login publik). Halaman login publik hanya untuk Freelancer & UMKM di `/login`.

---

## Ringkasan Arsitektur & Alur

```
SvelteKit (frontend)
     │  HTTP JSON via /api (proxy dev)
     ▼
Laravel REST API (backend)
     │
     ▼
SQLite (database)
```

- **Auth**: Laravel **Sanctum** token-based. Token disimpan di `localStorage`.
- **Role**: `admin`, `freelancer`, `hirer`. Backend menegakkan otorisasi lewat middleware `role:` dan Policy.
- **Status pekerjaan (task)**: `pending → approved → in_progress → reviewing → completed` (dengan cabang `rejected` / `cancelled`).

---

## Fitur Per Peran

- **Freelancer**: jelajahi & cari pekerjaan, ambil pekerjaan ("Saya Bisa"), submit bukti (`proof_url`), verifikasi NIK, dashboard profil & statistik.
- **Hirer / UMKM**: buat & kelola pekerjaan, lihat pelamar/applicants, terima/tolak, selesaikan pekerjaan, kelola profil usaha.
- **Admin**: dashboard, moderasi pekerjaan (approve/reject/hapus), verifikasi & suspend pengguna, analytics, pengaturan sistem (komisi, auto-approve, maintenance mode).

---

## API Endpoints Utama

Base: `/api` — Tersedia di `backend/routes/api.php`.

| Method | Endpoint | Auth | Deskripsi |
| ------ | -------- | ---- | --------- |
| POST | `/auth/register` | - | Registrasi (role: freelancer/hirer) |
| POST | `/auth/login` | - | Login (kirim `role` sesuai akun) |
| POST | `/auth/logout` | ✓ | Logout |
| GET | `/auth/user` | ✓ | Data user saat ini |
| GET | `/categories` | - | Daftar kategori pekerjaan |
| GET | `/tasks` | ✓ | Daftar pekerjaan (discovery) |
| GET | `/tasks/mine` | ✓ | Pekerjaan milik user |
| POST | `/tasks` | hirer | Buat pekerjaan |
| POST | `/tasks/{task}/apply` | freelancer | Ambil pekerjaan |
| POST | `/tasks/{task}/submit` | freelancer | Submit bukti |
| POST | `/tasks/{task}/complete` | hirer | Selesaikan pekerjaan |
| GET | `/tasks/{task}/applicants` | hirer | Daftar pelamar |
| POST | `/tasks/{task}/applications/{apply}/accept` | hirer | Terima pelamar |
| POST | `/admin/tasks/{task}/approve` | admin | Setujui pekerjaan |
| POST | `/admin/users/{user}/verify` | admin | Verifikasi pengguna |
| PUT | `/admin/settings` | admin | Simpan pengaturan sistem |

---

## Menjalankan Test

### Backend (PHPUnit)

```bash
cd backend
php artisan test
```

Cakupan test: auth (register/login/logout), authorization, CRUD pekerjaan, aplikasi/lamaran, lifecycle status, rating, dan verifikasi admin.

### Frontend (Build check)

```bash
cd frontend
npm run build
```

> Di **Windows**, langkah terakhir build (symlink adapter Vercel) dapat gagal dengan `EPERM`. Ini isu environment Windows, bukan isu kode — seluruh halaman Svelte tetap berhasil dikompilasi. Gunakan `npm run dev` untuk pengembangan lokal.

---

## Struktur Folder

```
.
├── AGENTS.md               # Panduan pengembangan AI
├── backend/                # Laravel REST API
│   ├── app/                # controllers, models, policies, resources
│   ├── database/           # migrations, factories, seeders
│   ├── routes/api.php      # seluruh endpoint API
│   └── tests/              # PHPUnit
└── frontend/               # SvelteKit UI
    ├── src/lib/            # API client, stores/formats/toast
    ├── src/routes/         # halaman & layout per peran
    └── src/routes/admin/(workspace)/  # layout shell admin (group route)
```

---

## Peringatan Keamanan

- Jangan pernah meng-commit file `.env` (berisi kunci & kredensial). Lihat `.gitignore`.
- Semua secret disimpan melalui `.env`. Gunakan nilai `APP_KEY` yang di-generate dari instalasi Anda, bukan dari repo.
