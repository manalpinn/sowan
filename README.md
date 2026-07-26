<div align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
  
  <h1>🎉 Sowan (Buku Tamu Digital)</h1>
  <p><strong>Sistem Manajemen Event & Buku Tamu Digital Modern</strong></p>

  <p>
    <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
    <img src="https://img.shields.io/badge/Vue.js-35495E?style=for-the-badge&logo=vue.js&logoColor=4FC08D" alt="Vue.js" />
    <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
    <img src="https://img.shields.io/badge/Inertia.js-9553E9?style=for-the-badge&logo=Inertia&logoColor=white" alt="Inertia.js" />
    <img src="https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  </p>
  
  <p>
    <em>Ucapkan selamat tinggal pada buku tamu kertas manual, antrean panjang di meja penerima tamu, dan data acara yang berantakan. Sowan hadir untuk merevolusi cara Anda mengelola kehadiran tamu acara.</em>
  </p>
</div>

---

## 📌 Pendahuluan

**Sowan** adalah platform buku tamu digital dan manajemen acara berskala *enterprise* yang dirancang khusus untuk acara modern seperti pernikahan, pertemuan perusahaan, seminar, dan pesta eksklusif. 

Dibangun dengan mengutamakan skalabilitas dan pengalaman pengguna, Sowan memanfaatkan kekuatan **Laravel 11/12** di sisi *backend* dan **Vue.js 3** melalui **Inertia.js** di sisi *frontend* untuk memberikan pengalaman aplikasi satu halaman (Single Page Application/SPA) yang mulus tanpa kerumitan membangun API yang terpisah.

## ✨ Fitur Utama & Keunggulan

### 🎫 Manajemen Acara Terpadu
- **Multi-Event:** Kelola berbagai acara secara bersamaan di dalam satu sistem.
- **Kustomisasi:** Sesuaikan tema, warna, dan pesan selamat datang acara agar sesuai dengan *branding* Anda.
- **Layanan Lokasi:** Terintegrasi dengan tautan dan peta *embed* Google Maps untuk navigasi yang mudah.
- **Batas Waktu:** Atur tanggal dan waktu mulai/selesai untuk membatasi proses *check-in* secara otomatis.

### 👥 Manajemen Tamu Tingkat Lanjut
- **Impor Massal:** Impor ratusan tamu dengan mudah menggunakan templat Excel/CSV.
- **Pelacakan RSVP:** Pantau siapa yang akan hadir, tidak hadir, dan kelola batas Pax (tamu tambahan).
- **Pembuatan Kode QR:** Buat kode QR unik secara otomatis untuk setiap tamu yang diundang.
- **Undangan PDF:** Unduh undangan PDF yang dipersonalisasi dan dilengkapi kode QR untuk dicetak secara tradisional.

### 💬 Integrasi WhatsApp (Didukung oleh Fonnte)
- **Undangan Otomatis:** Kirim undangan WhatsApp yang dipersonalisasi secara massal dengan satu klik.
- **Penyiaran Mirip Manusia (Human-like):** Dilengkapi dengan jeda (loop delays) dan mekanisme anti-spam bawaan untuk memastikan pengiriman pesan massal aman dan tidak diblokir.
- **Dukungan Login OTP:** Aktifkan proses login yang mudah tanpa kata sandi menggunakan OTP yang dikirimkan langsung ke WhatsApp untuk keamanan tambahan.

### 📱 Sistem Pemindai Cerdas (Scanner)
- **Pemindai Berbasis Web:** Tidak butuh aplikasi tambahan! Pindai kode QR langsung dari browser *mobile* menggunakan kamera perangkat (`html5-qrcode`).
- **Dukungan Mode Offline:** Internet mati? Tidak masalah. Unduh data tamu ke penyimpanan lokal browser, lakukan pemindaian secara *offline*, dan sinkronisasikan kembali saat koneksi pulih.
- **Check-in Manual:** Cari berdasarkan nama atau masukkan token manual untuk tamu yang lupa membawa kode QR mereka.
- **Umpan Balik Suara:** Notifikasi audio (bunyi bip) saat pemindaian berhasil untuk lingkungan acara yang serba cepat.

### 📊 Dasbor & Analitik Real-time
- **Statistik Langsung:** Pantau total tamu, yang sudah *check-in*, *check-out*, dan status RSVP secara *real-time*.
- **Grafik Interaktif:** Representasi data visual yang indah didukung oleh ApexCharts.
- **Pelaporan & Ekspor:** Buat catatan kehadiran yang komprehensif dan ekspor ke dalam format Excel atau PDF yang rapi untuk analisis pasca-acara.

## 🔐 Peran & Hak Akses

Sowan menggunakan sistem *Role-Based Access Control* (RBAC) yang tangguh menggunakan `spatie/laravel-permission`:

1. **Superadmin**: Akses penuh ke seluruh sistem. Dapat membuat acara, mengelola pengguna, dan menetapkan peran.
2. **Event Admin**: Dapat mengelola tamu, melihat statistik, dan mengoperasikan pemindai khusus untuk acara yang ditugaskan kepada mereka.
3. **Scanner/Receptionist**: Akses terbatas yang dirancang khusus hanya untuk mengoperasikan halaman pemindai QR untuk acara yang ditugaskan.

---

## 🛠 Detail Teknologi

**Arsitektur Backend:**
- Kerangka Kerja (Framework): [Laravel](https://laravel.com/)
- Basis Data: MySQL / PostgreSQL / SQLite
- Autentikasi: Laravel Breeze + Implementasi OTP Kustom
- Antrean (Queues): Antrean Database / Redis untuk menangani pesan WhatsApp massal
- Excel/PDF: `maatwebsite/excel` & `barryvdh/laravel-dompdf`

**Arsitektur Frontend:**
- Kerangka Kerja (Framework): [Vue.js 3](https://vuejs.org/) (Composition API)
- Jembatan (Bridge): [Inertia.js](https://inertiajs.com/)
- Styling: [Tailwind CSS](https://tailwindcss.com/)
- Pemindai: [html5-qrcode](https://github.com/mebjas/html5-qrcode)
- Peringatan (Alerts): SweetAlert2

---

## 🚀 Memulai

Ikuti petunjuk di bawah ini untuk menyalin dan menjalankan proyek ini di mesin lokal Anda untuk tujuan pengembangan dan pengujian.

### Prasyarat

Pastikan sistem Anda memenuhi persyaratan berikut:
- PHP >= 8.2
- Composer
- Node.js (v18+) & npm
- Server Basis Data (MySQL/MariaDB/PostgreSQL/SQLite)
- Akun & API Token [Fonnte](https://fonnte.com/) (Opsional, untuk fitur WhatsApp)

### Panduan Instalasi

1. **Klon repositori**
   ```bash
   git clone https://github.com/manalpinn/sowan.git
   cd sowan
   ```

2. **Instal Dependensi PHP**
   ```bash
   composer install
   ```

3. **Instal Dependensi JavaScript**
   ```bash
   npm install
   ```

4. **Pengaturan Lingkungan (Environment)**
   Salin file contoh environment dan hasilkan *application key* Anda:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   
   *Selanjutnya, buka file `.env` dan konfigurasikan pengaturan basis data Anda (`DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, dll.).*

5. **Jalankan Migrasi & Seeder**
   Ini akan menyiapkan tabel-tabel basis data dan mengisi pengguna Superadmin serta peran bawaan.
   ```bash
   php artisan migrate --seed
   ```

6. **Konfigurasi Antrean Latar Belakang (Penting!)**
   Karena pesan WhatsApp massal dan email diproses di latar belakang untuk mencegah masalah batas waktu server (timeout), Anda harus mengonfigurasi *queue driver* Anda di `.env`:
   ```env
   QUEUE_CONNECTION=database
   ```

7. **Konfigurasi API WhatsApp Fonnte (Opsional)**
   Untuk mengaktifkan undangan WhatsApp dan OTP, tambahkan token Fonnte Anda ke file `.env`:
   ```env
   FONNTE_TOKEN=token_anda_disini
   ```

8. **Build Aset Frontend**
   Kompilasi komponen Vue dan gaya Tailwind:
   ```bash
   npm run build
   
   # Atau, jika Anda sedang aktif mengembangkan (development):
   npm run dev
   ```

9. **Jalankan Aplikasi**
   Buka jendela terminal baru dan jalankan server pengembangan Laravel:
   ```bash
   php artisan serve
   ```
   
10. **Jalankan Pekerja Antrean (Queue Worker)**
    Buka *satu lagi* jendela terminal untuk memproses pekerjaan di latar belakang (seperti mengirim pesan WhatsApp):
    ```bash
    php artisan queue:work
    ```

---

## 📸 Tangkapan Layar (Screenshots)

*(Kami menyarankan untuk menambahkan tangkapan layar di sini untuk memamerkan UI Anda yang indah. Berikut adalah *placeholder* yang disarankan)*

- **Dasbor:** `![Tangkapan Layar Dasbor](path/to/dashboard.png)`
- **Manajemen Acara:** `![Pengaturan Acara](path/to/event.png)`
- **Tampilan Pemindai:** `![Pemindai QR](path/to/scanner.png)`
- **Undangan Publik:** `![Halaman RSVP Publik](path/to/rsvp.png)`

---

## 🤝 Berkontribusi

Kontribusi adalah hal yang membuat komunitas *open-source* menjadi tempat yang luar biasa untuk belajar, menginspirasi, dan berkreasi. Segala kontribusi yang Anda buat akan **sangat dihargai**.

1. Fork Proyek Ini
2. Buat Cabang Fitur Anda (`git checkout -b feature/FiturLuarBiasa`)
3. Lakukan Commit pada Perubahan Anda (`git commit -m 'Menambahkan beberapa FiturLuarBiasa'`)
4. Push ke Cabang (`git push origin feature/FiturLuarBiasa`)
5. Buka sebuah Pull Request

## 📄 Lisensi

Didistribusikan di bawah Lisensi MIT. Lihat `LICENSE` untuk informasi lebih lanjut.
