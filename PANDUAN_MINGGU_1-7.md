# TokoOnline Laravel — Materi sampai Minggu ke-7

Project ini disesuaikan dengan Modul Web Programming III sampai Minggu ke-7. Materi setelah Minggu ke-7 tidak ditambahkan.

## Fitur yang tersedia

1. Frontend toko, banner, navigasi, dan layout produk.
2. Daftar produk, detail produk, filter kategori, serta pagination.
3. Login/registrasi customer menggunakan Google OAuth (Laravel Socialite).
4. Halaman Akun Saya dan pembaruan profil customer.
5. Middleware `is.customer` dan pengelompokan route customer.
6. Keranjang belanja: tambah produk, ubah jumlah, hapus produk, dan hitung total harga/berat.
7. Halaman cek ongkos kirim RajaOngkir: daftar provinsi, kota, kecamatan, dan tarif berdasarkan kurir.

## Langkah setelah menerima project

1. Pastikan konfigurasi database pada file `.env` sesuai database lokal.
2. Isi `RAJAONGKIR_API_KEY` dengan API key milik akun RajaOngkir/Komerce.
3. Sesuaikan `RAJAONGKIR_BASE_URL` dengan endpoint API yang aktif untuk akun tersebut. Nilai awal mengikuti contoh modul: `https://rajaongkir.komerce.id/api/v1` (RajaOngkir API V2).
4. Ganti `RAJAONGKIR_ORIGIN_DISTRICT_ID` dengan ID kecamatan asal toko. API V2 menghitung ongkir berdasarkan ID kecamatan, bukan ID kota.
5. Pastikan `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, dan `GOOGLE_REDIRECT_URL` sudah sesuai konfigurasi OAuth Google. Redirect URL harus mengarah ke `/auth/google/callback` pada domain lokal yang digunakan.
6. Jalankan perintah berikut dari folder project:

```bash
php artisan optimize:clear
php artisan migrate
php artisan storage:link
```

7. Jalankan Laravel:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000/beranda`.

## Catatan penting

- Migrasi baru membuat tabel `orders` dan `order_items` untuk keranjang belanja. Jalankan `php artisan migrate` agar tabel tersebut dibuat.
- Fitur cek ongkir memerlukan API key yang valid dan koneksi internet.
- Integrasi ongkir menggunakan API V2 RajaOngkir: provinsi → kota → kecamatan → hitung ongkir. API key dikirim dari backend Laravel, bukan dari browser.
- Checkout, riwayat pesanan, pengelolaan pesanan admin, grafik, dan payment gateway tidak termasuk cakupan karena berada setelah Minggu ke-7.
