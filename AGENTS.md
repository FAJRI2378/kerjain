# AGENTS.md

# KERJAIN — AI Software Engineering Instructions

## 1. Project Overview

KERJAIN adalah marketplace pekerjaan mikro yang mempertemukan:

1. **UMKM** yang membutuhkan bantuan untuk pekerjaan jangka pendek/mikro.
2. **Worker** seperti siswa, mahasiswa, freelancer, atau orang lokal yang memiliki waktu dan keterampilan yang belum termanfaatkan.

Contoh kebutuhan:

> Soto Pak Budi membutuhkan seseorang selama 3 jam untuk memotret produk dan mengunggah 20 menu dengan budget Rp75.000.

Worker di sekitar lokasi dapat melihat pekerjaan tersebut, mengambil pekerjaan, menyelesaikannya, kemudian mendapatkan rating dan riwayat pekerjaan.

KERJAIN bukan marketplace barang.

KERJAIN adalah:

> **Marketplace untuk kapasitas produktif yang menganggur melalui pekerjaan mikro lokal.**

Contoh pekerjaan:

* Foto produk
* Upload produk/menu
* Desain poster
* Input data
* Pembukuan sederhana
* Membuat katalog
* Mengelola WhatsApp Business
* Mengantar barang
* Packing
* Membantu event
* Membuat video pendek
* Menerjemahkan menu
* Membuat website sederhana
* Memperbaiki komputer
* Administrasi
* Pekerjaan mikro digital maupun fisik lainnya

Tujuan utama platform:

> Mengurangi kesenjangan antara kebutuhan tenaga kerja mikro UMKM dan kapasitas produktif masyarakat di sekitar mereka.

Project diarahkan untuk mendukung konsep **SDG 8**, khususnya aktivitas produktif, kesempatan kerja, kewirausahaan, dan pertumbuhan usaha kecil.

---

# 2. Technology Stack

## Frontend

Frontend menggunakan:

* Svelte
* SvelteKit apabila digunakan oleh existing project
* TypeScript apabila sudah digunakan oleh existing project
* CSS / styling system yang sudah tersedia di project

Frontend yang sudah dibuat merupakan sumber utama untuk memahami kebutuhan backend.

## Backend

Backend menggunakan:

* Laravel
* PHP
* REST API
* Relational Database
* Laravel Eloquent
* Laravel Form Request
* Laravel API Resources apabila diperlukan
* Laravel Policies / Gates untuk authorization
* Laravel Jobs / Queues apabila memang diperlukan
* Laravel Notifications apabila memang diperlukan

Database engine harus mengikuti konfigurasi project yang sudah tersedia.

Jangan mengganti technology stack tanpa alasan teknis yang kuat.

---

# 3. PRIMARY AGENT PRINCIPLE

## STOP: Jangan langsung membuat backend.

AI agent WAJIB memahami frontend Svelte yang sudah ada sebelum membuat atau mengubah backend.

Urutan kerja wajib:

```text
Existing Svelte UI
        ↓
User Flow
        ↓
Feature Inventory
        ↓
UI Data Requirements
        ↓
Business Rules
        ↓
Domain Model
        ↓
API Contract
        ↓
Database Design
        ↓
Laravel Implementation
        ↓
Automated Tests
        ↓
Frontend Integration Validation
```

Agent tidak boleh membalik urutan tersebut hanya karena implementasi backend terlihat mudah.

---

# 4. FRONTEND-FIRST ANALYSIS

Sebelum menulis kode Laravel, agent WAJIB membaca dan menganalisis frontend Svelte.

Minimal periksa:

```text
src/
routes/
components/
lib/
stores/
services/
types/
hooks/
```

Jika struktur berbeda, sesuaikan dengan project aktual.

Jangan mengasumsikan struktur folder.

## Agent harus mencari:

### A. Pages

Identifikasi semua halaman yang berhubungan dengan:

* Authentication
* Registration
* Login
* Dashboard
* Job discovery
* Job detail
* Create job
* Edit job
* Apply / take job
* My jobs
* Job progress
* Job completion
* Rating
* Profile
* Worker profile
* UMKM profile
* Notifications
* Wallet/payment apabila sudah terdapat di UI
* Admin/moderation apabila sudah terdapat di UI

### B. Components

Cari component yang menunjukkan kebutuhan data backend.

Contoh:

```text
JobCard
JobDetail
JobStatusBadge
WorkerCard
Rating
ProfileCard
ApplicationList
NotificationItem
```

Jangan hanya membaca nama component.

Baca props, state, event, dan data yang digunakan.

### C. API Calls

Cari seluruh penggunaan:

```text
fetch()
axios
API client
service
repository
load()
actions
form submission
```

Catat:

* HTTP method
* URL
* Request body
* Query parameter
* Path parameter
* Expected response
* Error response
* Authentication requirement

### D. Frontend Types

Cari:

```text
interface
type
DTO
response type
form type
```

Frontend type sering kali menunjukkan struktur data yang backend harus sediakan.

Namun:

> Frontend type bukan otomatis database schema.

Agent tetap harus melakukan domain analysis.

---

# 5. UI → BACKEND REQUIREMENT MAPPING

Setelah membaca frontend, buat mapping:

| UI Feature   | User        | Required Data | Action | Backend Requirement   |
| ------------ | ----------- | ------------- | ------ | --------------------- |
| Job List     | Worker      | jobs          | GET    | Job listing API       |
| Job Detail   | Worker      | job + UMKM    | GET    | Job detail API        |
| Create Job   | UMKM        | job fields    | POST   | Job creation          |
| Apply Job    | Worker      | job ID        | POST   | Job application       |
| My Jobs      | Worker/UMKM | jobs          | GET    | User-specific queries |
| Complete Job | Worker      | job ID        | POST   | Job state transition  |
| Rating       | Worker/UMKM | rating        | POST   | Rating system         |

Table ini harus dibuat berdasarkan **UI aktual**, bukan asumsi.

Jika sebuah feature belum ada di UI:

> Jangan otomatis membuat backend feature tersebut hanya karena feature tersebut terdengar masuk akal.

---

# 6. PRODUCT DOMAIN

Core domain KERJAIN minimal terdiri dari:

```text
User
Profile
UMKM
Worker
Job
JobCategory
JobApplication / JobAssignment
JobStatus
Rating
Location
Notification
```

Namun agent harus memvalidasi kembali domain tersebut berdasarkan UI aktual.

Jangan membuat 20 tabel hanya karena terlihat "enterprise".

---

# 7. CORE USER ROLES

Minimal terdapat dua tipe user utama:

```text
UMKM
WORKER
```

Apabila frontend memiliki role tambahan:

```text
ADMIN
```

maka role tersebut harus dianalisis dan diimplementasikan sesuai kebutuhan.

Jangan membuat role tambahan tanpa kebutuhan nyata.

---

# 8. CORE JOB LIFECYCLE

Pekerjaan mikro harus memiliki lifecycle yang jelas.

Contoh awal:

```text
DRAFT
  ↓
OPEN
  ↓
APPLIED
  ↓
ASSIGNED
  ↓
IN_PROGRESS
  ↓
SUBMITTED
  ↓
COMPLETED
```

Dengan kemungkinan:

```text
OPEN → CANCELLED
APPLIED → REJECTED
ASSIGNED → CANCELLED
IN_PROGRESS → CANCELLED
SUBMITTED → REVISION_REQUIRED
```

Status final harus ditentukan berdasarkan UI dan business requirements.

## Important

Status bukan sekadar string.

Setiap perubahan status harus memiliki:

1. Actor
2. Current status
3. Target status
4. Permission
5. Business rule
6. Timestamp

Contoh:

```text
Worker:
ASSIGNED → IN_PROGRESS

Worker:
IN_PROGRESS → SUBMITTED

UMKM:
SUBMITTED → COMPLETED

UMKM:
SUBMITTED → REVISION_REQUIRED
```

Agent harus mencegah state transition ilegal.

Contoh:

```text
COMPLETED → OPEN
```

tidak boleh terjadi kecuali ada business rule eksplisit yang mengizinkannya.

---

# 9. LOCATION

Location merupakan bagian penting dari value proposition KERJAIN.

UI dapat menampilkan:

```text
1.4 km
```

Artinya backend kemungkinan membutuhkan:

```text
latitude
longitude
```

dan bukan sekadar:

```text
address = "Bekasi"
```

Backend harus memisahkan:

```text
latitude
longitude
address
```

apabila data tersebut memang digunakan UI.

Distance sebaiknya dihitung dari koordinat.

Contoh:

```text
Worker location
        ↓
Job location
        ↓
Distance calculation
        ↓
"1.4 km"
```

Jangan menyimpan distance sebagai data permanen apabila distance dapat dihitung secara dinamis.

---

# 10. JOB DATA MODEL

Minimal sebuah Job kemungkinan membutuhkan:

```text
id
owner_id
category_id
title
description
budget
estimated_duration
location
latitude
longitude
status
deadline
created_at
updated_at
```

Tetapi ini hanyalah starting hypothesis.

Agent WAJIB membandingkannya dengan UI.

Jika UI menunjukkan:

```text
Budget: Rp75.000
Durasi: 3 jam
Lokasi: 1.4 km
```

maka backend harus menyediakan data yang mampu menghasilkan informasi tersebut.

---

# 11. MONEY

Jangan menggunakan floating point untuk uang.

Gunakan integer dalam satuan rupiah.

Contoh:

```text
75000
```

bukan:

```text
75000.00
```

dan bukan:

```text
75000.50
```

kecuali memang terdapat kebutuhan pecahan uang.

API harus memiliki format yang konsisten.

Contoh:

```json
{
  "budget": 75000
}
```

Frontend bertanggung jawab terhadap formatting:

```text
Rp75.000
```

Backend bertanggung jawab terhadap nilai sebenarnya.

---

# 12. AUTHENTICATION

Authentication harus dibuat berdasarkan kebutuhan frontend.

Agent wajib mencari:

```text
login form
register form
logout
current user
auth store
protected routes
token handling
```

Jika menggunakan token-based authentication, Laravel Sanctum dapat digunakan apabila sesuai dengan arsitektur frontend.

Jangan menambahkan OAuth, JWT, refresh-token rotation, atau sistem authentication kompleks lainnya tanpa kebutuhan.

---

# 13. AUTHORIZATION

Authentication ≠ Authorization.

Agent harus membedakan:

```text
Who are you?
```

dengan:

```text
What are you allowed to do?
```

Contoh:

Worker tidak boleh:

```text
edit job milik UMKM lain
delete job milik UMKM lain
complete job milik worker lain
```

UMKM tidak boleh:

```text
apply sebagai worker
```

kecuali sistem memang mengizinkan satu user memiliki beberapa capability.

Gunakan:

* Policies
* Gates
* Form Request authorization

jika diperlukan.

Jangan hanya mengandalkan:

```php
if ($user->id === $job->owner_id)
```

yang tersebar di banyak controller.

---

# 14. API DESIGN

API harus dibuat berdasarkan kebutuhan frontend.

Jangan membuat endpoint sebanyak mungkin.

Contoh:

```http
GET    /api/jobs
GET    /api/jobs/{job}
POST   /api/jobs
PUT    /api/jobs/{job}
DELETE /api/jobs/{job}

POST   /api/jobs/{job}/applications
GET    /api/my/jobs
GET    /api/my/applications

POST   /api/jobs/{job}/start
POST   /api/jobs/{job}/submit
POST   /api/jobs/{job}/complete

POST   /api/jobs/{job}/ratings
```

Namun endpoint final harus mengikuti UI aktual.

---

# 15. API RESPONSE

Response harus konsisten.

Contoh:

```json
{
  "data": {
    "id": 1,
    "title": "Foto Produk + Upload Menu",
    "budget": 75000,
    "duration": "3 jam",
    "distance": 1.4,
    "status": "open"
  }
}
```

Collection:

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

Error:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "budget": [
      "The budget field is required."
    ]
  }
}
```

Gunakan format konsisten di seluruh API.

---

# 16. VALIDATION

Validation harus berada di backend meskipun frontend sudah melakukan validation.

Contoh:

```text
Frontend validation
        +
Backend validation
```

Frontend validation adalah UX.

Backend validation adalah security dan data integrity.

Contoh:

```php
'budget' => ['required', 'integer', 'min:1000'],
'title' => ['required', 'string', 'max:255'],
'description' => ['required', 'string'],
```

Rule final harus disesuaikan dengan product requirements.

---

# 17. BUSINESS LOGIC

Jangan memasukkan seluruh business logic ke controller.

Contoh buruk:

```php
public function submit(Request $request)
{
    // 100 lines of business logic
}
```

Controller sebaiknya mengorkestrasi request.

Untuk business process yang kompleks, gunakan service/action/domain class yang masuk akal.

Contoh:

```text
Controller
    ↓
Action / Service
    ↓
Model
```

Namun:

> Jangan membuat Service class hanya demi mengikuti pattern.

Jika logic hanya:

```php
Job::create(...)
```

tidak perlu membuat:

```text
CreateJobService
CreateJobManager
CreateJobHandler
CreateJobProcessor
CreateJobUseCase
```

secara bersamaan.

Gunakan abstraction ketika complexity benar-benar membutuhkannya.

---

# 18. DATABASE DESIGN

Database harus dibuat setelah memahami domain.

Minimal pertimbangkan:

```text
users
profiles
jobs
job_categories
job_applications
ratings
notifications
```

Foreign key harus digunakan.

Contoh:

```text
jobs.owner_id → users.id
jobs.category_id → job_categories.id
job_applications.job_id → jobs.id
job_applications.worker_id → users.id
ratings.job_id → jobs.id
ratings.reviewer_id → users.id
ratings.reviewee_id → users.id
```

Jangan menggunakan database sebagai dumping ground.

Setiap field harus memiliki alasan.

---

# 19. DATABASE NORMALIZATION

Jangan menyimpan data yang sebenarnya merupakan relasi sebagai JSON hanya karena lebih cepat dibuat.

Contoh buruk:

```text
job
{
    applicants: [...]
}
```

Jika applicants merupakan entity yang memiliki lifecycle sendiri, gunakan table:

```text
job_applications
```

Sebaliknya, jangan memecah setiap hal kecil menjadi table jika tidak memiliki nilai domain.

Prinsip:

> Normalize when the data has independent meaning or lifecycle.

---

# 20. N+1 QUERY PREVENTION

Agent harus memperhatikan Eloquent relationship.

Contoh buruk:

```php
foreach ($jobs as $job) {
    echo $job->owner->name;
}
```

tanpa eager loading.

Gunakan:

```php
Job::with('owner')->get();
```

jika memang relationship tersebut digunakan.

Namun jangan eager-load semua relationship secara membabi buta.

Load data berdasarkan kebutuhan UI.

---

# 21. PAGINATION

List yang berpotensi besar harus menggunakan pagination.

Contoh:

```text
GET /api/jobs?page=1&per_page=20
```

Jangan mengembalikan seluruh job ke frontend.

Job marketplace secara alami dapat berkembang menjadi dataset besar.

---

# 22. FILTERING AND SEARCH

Jika UI menyediakan:

```text
Search
Category
Location
Budget
Distance
Duration
```

backend harus menyediakan query capability yang relevan.

Contoh:

```http
GET /api/jobs?category=design
GET /api/jobs?max_budget=100000
GET /api/jobs?radius=5
GET /api/jobs?search=poster
```

Jangan membuat filter backend yang tidak digunakan UI kecuali ada alasan product yang jelas.

---

# 23. CONCURRENCY

Agent harus memikirkan race condition pada pekerjaan.

Contoh:

```text
Worker A → Apply
Worker B → Apply
```

Jika hanya tersedia satu worker:

```text
Worker A
Worker B
     ↓
same job
```

Backend harus memastikan tidak terjadi dua assignment yang valid secara bersamaan.

Jangan hanya mengandalkan:

```php
if ($job->status === 'open')
```

tanpa mempertimbangkan concurrent requests.

Gunakan database transaction / locking / unique constraint apabila diperlukan.

---

# 24. RATING

Rating harus memiliki aturan.

Contoh:

```text
Worker memberikan rating kepada UMKM
UMKM memberikan rating kepada Worker
```

Rating hanya boleh diberikan setelah pekerjaan selesai.

Agent harus memastikan:

```text
User cannot rate themselves.
User cannot rate unrelated users.
User cannot rate the same job repeatedly.
Rating must belong to a completed job.
```

Rule final mengikuti product specification.

---

# 25. SECURITY

Backend wajib mempertimbangkan:

* Authentication
* Authorization
* Input validation
* Mass assignment protection
* SQL injection prevention
* XSS-safe output handling
* Rate limiting
* File upload validation
* Ownership validation
* IDOR prevention
* Sensitive data exposure

Jangan menganggap endpoint aman hanya karena frontend menyembunyikan tombol.

Contoh:

```text
Frontend:
<button hidden>
```

bukan security.

Security harus berada di backend.

---

# 26. FILE UPLOAD

Jika UI memungkinkan:

* Foto produk
* Bukti pekerjaan
* Portfolio
* Profile photo
* Attachment

agent harus menentukan:

```text
storage disk
file validation
maximum size
allowed MIME types
file naming
access policy
```

Jangan menyimpan file upload secara sembarangan langsung ke public directory.

---

# 27. NOTIFICATIONS

Jika UI memiliki notification center, backend perlu menentukan event yang memicunya.

Contoh:

```text
Worker applied
        ↓
UMKM notification

Worker submitted work
        ↓
UMKM notification

Job completed
        ↓
Worker notification

New rating
        ↓
User notification
```

Notification bukan sekadar CRUD.

Identifikasi event yang menyebabkan notification.

---

# 28. TRANSACTION BOUNDARIES

Gunakan database transaction ketika satu business operation mengubah beberapa entity yang harus konsisten.

Contoh:

```text
Accept worker
    ↓
update job
    ↓
create assignment
    ↓
reject competing applications
```

Operasi seperti ini harus dipertimbangkan untuk transaction.

Jangan menggunakan transaction pada semua query secara otomatis.

---

# 29. QUEUE

Gunakan queue hanya untuk pekerjaan yang memang cocok diproses asynchronous.

Contoh:

* Notification
* Email
* Image processing
* Report generation
* Heavy computation

Jangan memasukkan CRUD biasa ke queue hanya agar terlihat scalable.

---

# 30. TESTING

Setiap business-critical behavior harus memiliki test.

Minimal:

### Authentication

```text
register
login
logout
```

### Authorization

```text
worker cannot edit another user's job
UMKM cannot perform worker-only action
```

### Job

```text
create
update
delete
list
detail
```

### Application

```text
apply
accept
reject
duplicate application prevention
```

### Lifecycle

```text
open → assigned
assigned → in_progress
in_progress → submitted
submitted → completed
```

### Rating

```text
valid rating
invalid rating
duplicate rating
rating before completion
```

Testing harus fokus pada behavior, bukan sekadar mengejar coverage percentage.

---

# 31. DEVELOPMENT WORKFLOW FOR AI AGENTS

Setiap AI agent yang bekerja pada repository harus mengikuti workflow berikut.

## Phase 1 — Reconnaissance

Baca:

```text
AGENTS.md
README.md
frontend Svelte project
backend Laravel project
.env.example
existing migrations
existing models
existing routes
existing controllers
existing tests
```

Jangan langsung coding.

---

## Phase 2 — UI Analysis

Dokumentasikan:

```text
Pages
Components
Forms
API calls
Frontend types
User flows
Loading states
Error states
Empty states
Authentication states
```

Cari kebutuhan backend yang tersirat dari UI.

---

## Phase 3 — Gap Analysis

Bandingkan:

```text
UI requirements
vs
existing backend
```

Kategorikan:

```text
EXISTS
MISSING
INCORRECT
AMBIGUOUS
```

Contoh:

```text
GET /jobs
→ EXISTS

GET /jobs/{id}
→ MISSING

POST /jobs/{id}/apply
→ MISSING

distance field
→ INCORRECT

job completion rule
→ AMBIGUOUS
```

---

## Phase 4 — Domain Design

Tentukan:

```text
Entities
Relationships
States
Business rules
Authorization
Validation
```

Jangan membuat migration sebelum phase ini selesai.

---

## Phase 5 — API Contract

Tentukan:

```text
endpoint
method
request
response
validation
authentication
authorization
error response
```

API contract harus dapat menjelaskan bagaimana Svelte frontend berkomunikasi dengan Laravel.

---

## Phase 6 — Database

Baru setelah API/domain jelas:

```text
migration
foreign key
index
constraint
relationship
```

---

## Phase 7 — Implementation

Implementasikan dengan urutan:

```text
Migration
↓
Model / Relationship
↓
Validation
↓
Authorization
↓
Business Logic
↓
Controller
↓
Resource / Response
↓
Route
↓
Test
```

---

## Phase 8 — Integration

Pastikan frontend dapat:

```text
request API
receive response
handle loading
handle validation errors
handle authorization errors
handle empty state
handle success state
```

Jangan menganggap backend selesai hanya karena PHPUnit berhasil.

---

# 32. API CONTRACT IS THE BOUNDARY

Frontend dan backend harus berkomunikasi melalui contract yang jelas.

Contoh:

```text
Svelte
   │
   │ HTTP JSON
   ▼
Laravel API
   │
   ▼
Domain
   │
   ▼
Database
```

Frontend tidak boleh mengetahui:

```text
database schema
Eloquent model
Laravel implementation detail
```

Backend tidak boleh bergantung pada:

```text
Svelte component implementation
```

Yang menjadi boundary adalah API contract.

---

# 33. DO NOT OVERENGINEER

KERJAIN adalah project lomba.

Prioritas:

```text
Functional
↓
Correct
↓
Reliable
↓
Secure
↓
Maintainable
↓
Scalable
```

Bukan:

```text
Enterprise architecture
↓
100 abstraction
↓
microservices
↓
event sourcing
↓
CQRS
↓
Kubernetes
```

Jangan menggunakan:

* Microservices
* CQRS
* Event sourcing
* Repository pattern di setiap model
* Generic CRUD abstraction
* Multiple service layers
* Message broker

kecuali terdapat kebutuhan nyata.

Laravel monolith yang modular sudah cukup untuk tahap awal.

---

# 34. PREFERRED LARAVEL ARCHITECTURE

Starting architecture:

```text
HTTP Request
    ↓
Form Request
    ↓
Controller
    ↓
Action / Service (only when needed)
    ↓
Eloquent Model
    ↓
Database
```

Response:

```text
Model
    ↓
API Resource
    ↓
JSON
    ↓
Svelte
```

Authorization:

```text
Request / Controller
        ↓
Policy
```

Async:

```text
Service / Event
        ↓
Job
        ↓
Queue
```

Jangan membuat layer tambahan jika tidak memberikan manfaat.

---

# 35. FILE ORGANIZATION

Gunakan struktur Laravel yang familiar terlebih dahulu.

Contoh:

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Services/
└── Jobs/
```

Jika domain semakin besar, struktur dapat berkembang menjadi domain-oriented structure.

Jangan melakukan refactoring besar sebelum diperlukan.

---

# 36. CODE QUALITY RULES

Kode harus:

* Mudah dibaca
* Explicit
* Memiliki naming yang jelas
* Mengikuti Laravel conventions
* Tidak memiliki duplicate business logic
* Tidak memiliki dead code
* Tidak memiliki commented-out code yang tidak diperlukan
* Tidak menggunakan magic number tanpa alasan
* Tidak menyembunyikan business rule dalam helper yang tidak jelas

Prefer:

```php
$job->isOpen()
```

daripada:

```php
$job->status === 3
```

jika status memiliki domain meaning.

---

# 37. ERROR HANDLING

API harus membedakan:

```text
400 Bad Request
401 Unauthenticated
403 Forbidden
404 Not Found
409 Conflict
422 Validation Error
429 Too Many Requests
ternal Server Error
```

Gunakan status HTTP secara semantik.

Jangan mengembalikan:

```text
200 OK
```

untuk semua kondisi hanya karena frontend lebih mudah.

---

# 38. OBSERVABILITY

Untuk business-critical operations, logging harus membantu menjawab:

```text
What happened?
Who did it?
To which resource?
When?
Why did it fail?
```

Jangan melakukan logging terhadap:

* Password
* Token
* Sensitive credentials
* Data pribadi yang tidak diperlukan

---

# 39. ENVIRONMENT

Secret tidak boleh masuk repository.

Gunakan:

```text
.env
.env.example
```

Jangan commit:

```text
API keys
database password
application secret
private credentials
```

---

# 40. GIT WORKFLOW

AI agent harus membuat perubahan kecil dan terisolasi.

Prefer:

```text
feat: add job listing API
feat: add job application flow
fix: prevent duplicate job application
test: add job lifecycle tests
```

Hindari satu perubahan besar yang mencampur:

```text
database
frontend
refactoring
formatting
unrelated fixes
```

---

# 41. BEFORE MODIFYING EXISTING CODE

Sebelum mengubah file existing:

1. Baca file.
2. Pahami siapa yang menggunakannya.
3. Cari reference-nya.
4. Pahami behavior existing.
5. Tentukan apakah perubahan backward compatible.
6. Baru ubah.

Jangan mengganti implementation hanya karena agent memiliki preferensi architecture berbeda.

---

# 42. WHEN REQUIREMENTS ARE AMBIGUOUS

Jika requirement ambigu:

Jangan mengarang business rule secara diam-diam.

Agent harus:

1. Cari bukti di UI.
2. Cari bukti di existing backend.
3. Cari API contract.
4. Cari documentation.
5. Jika tetap ambigu, pilih behavior paling sederhana dan aman.
6. Dokumentasikan assumption tersebut.

Contoh:

```text
ASSUMPTION:
Satu job hanya dapat memiliki satu worker aktif.

REASON:
UI hanya menyediakan satu assignment.

IMPACT:
Database perlu memastikan tidak terdapat multiple active assignments.
```

---

# 43. UI IS NOT THE ONLY SOURCE OF TRUTH

Frontend adalah sumber utama untuk:

```text
user interaction
display requirements
data presentation
available actions
```

Namun frontend bukan sumber utama untuk:

```text
security
database integrity
authorization
business-critical validation
```

Backend tetap harus enforce seluruh aturan penting.

---

# 44. DEFINITION OF DONE

Sebuah backend feature dianggap selesai hanya jika:

* [ ] UI flow sudah dipahami
* [ ] User flow sudah dipahami
* [ ] Domain requirement sudah jelas
* [ ] API contract sudah jelas
* [ ] Database requirement sudah jelas
* [ ] Validation sudah dibuat
* [ ] Authorization sudah dibuat
* [ ] Business logic sudah dibuat
* [ ] API response konsisten
* [ ] Automated test tersedia
* [ ] Error handling tersedia
* [ ] Frontend integration berhasil
* [ ] Tidak terdapat obvious security issue
* [ ] Tidak terdapat unnecessary abstraction

---

# 45. REQUIRED AGENT REPORT

Setelah mengerjakan sebuah feature, AI agent harus memberikan ringkasan:

```text
## Feature
Nama feature

## UI Analysis
Halaman/component Svelte yang dianalisis.

## User Flow
Flow user yang didukung.

## Backend Changes
File yang dibuat/diubah.

## API
Endpoint yang ditambahkan/diubah.

## Database
Migration/table/relationship yang berubah.

## Business Rules
Rule yang diterapkan.

## Authorization
Siapa yang boleh melakukan action.

## Tests
Test yang ditambahkan/dijalankan.

## Assumptions
Asumsi yang dibuat karena requirement belum eksplisit.

## Remaining Issues
Hal yang masih belum selesai atau membutuhkan keputusan.
```

---

# 46. CRITICAL RULE

## NEVER CODE FROM ASSUMPTION WHEN THE UI CAN PROVIDE EVIDENCE.

Sebelum membuat endpoint seperti:

```http
POST /jobs/{job}/apply
```

agent harus menemukan:

```text
button "Saya Bisa"
```

atau interaction equivalent pada frontend.

Sebelum membuat:

```http
POST /jobs/{job}/complete
```

agent harus memahami:

```text
siapa yang melihat button
kapan button muncul
status job apa yang diperlukan
response apa yang diharapkan
```

Sebelum membuat database field:

```text
duration
budget
latitude
longitude
deadline
```

agent harus mengetahui apakah data tersebut:

```text
ditampilkan UI
dimasukkan user
dihitung backend
digunakan sebagai filter
digunakan dalam business rule
```

---

# 47. FINAL AGENT DIRECTIVE

Ketika diberikan task baru, lakukan:

```text
1. Inspect repository.
2. Read this AGENTS.md.
3. Inspect Svelte UI.
4. Identify relevant user flow.
5. Identify existing API calls.
6. Identify required data.
7. Inspect existing Laravel implementation.
8. Perform UI ↔ Backend gap analysis.
9. Define business rules.
10. Define API contract.
11. Define database changes.
12. Implement the smallest correct solution.
13. Add tests.
14. Run tests.
15. Validate integration with the UI.
16. Report changes, assumptions, and remaining issues.
```

### Absolute Rule

**DO NOT start by creating migrations, models, controllers, services, repositories, or endpoints.**

Start by understanding:

> **What does the existing Svelte UI actually need?**

Then design the Laravel backend to satisfy that need with the simplest architecture that remains correct, secure, testable, and maintainable.

---

# 48. KERJAIN PRODUCT NORTH STAR

Every technical decision should ultimately support this interaction:

```text
UMKM has a small problem
        ↓
UMKM posts a micro-job
        ↓
Nearby productive person discovers it
        ↓
Worker says "Saya Bisa"
        ↓
UMKM selects worker
        ↓
Work is performed
        ↓
Work is submitted
        ↓
UMKM confirms completion
        ↓
Both sides build reputation
        ↓
Worker gains work history
        ↓
UMKM gets affordable access to productive capacity
```

If a technical decision does not meaningfully support the product flow above, question whether it is necessary.

**Build the product first. Build the architecture around the product — not the other way around.**
