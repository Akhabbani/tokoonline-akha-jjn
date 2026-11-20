<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RajaOngkirController;

Route::get('/', fn () => redirect()->route('beranda'));

// Login administrator
Route::get('backend/login', [LoginController::class, 'loginBackend'])->name('backend.login');
Route::post('backend/login', [LoginController::class, 'authenticateBackend'])->name('backend.login.post');
Route::post('backend/logout', [LoginController::class, 'logoutBackend'])->name('backend.logout');

// Autentikasi customer dengan Google
Route::get('/auth/redirect', [CustomerController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/google/callback', [CustomerController::class, 'callback'])->name('auth.callback');
Route::post('/logout', [CustomerController::class, 'logout'])->name('customer.logout');

// Halaman backend
Route::middleware('auth')->group(function () {
    Route::get('backend/beranda', [BerandaController::class, 'berandaBackend'])->name('backend.beranda');
    Route::resource('backend/produk', ProdukController::class, ['as' => 'backend']);
    Route::post('foto-produk/store', [ProdukController::class, 'storeFoto'])->name('backend.foto_produk.store');
    Route::delete('foto-produk/{id}', [ProdukController::class, 'destroyFoto'])->name('backend.foto_produk.destroy');
    Route::get('backend/produk/{id}/foto', [ProdukController::class, 'foto'])->name('backend.produk.foto');
    Route::resource('backend/kategori', KategoriController::class, ['as' => 'backend']);
    Route::resource('backend/user', UserController::class, ['as' => 'backend']);
    Route::resource('backend/customer', CustomerController::class, ['as' => 'backend']);
});

// Frontend toko
Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
Route::get('/produk/all', [ProdukController::class, 'produkAll'])->name('produk.all');
Route::get('/produk/kategori/{id}', [ProdukController::class, 'produkKategori'])->name('produk.kategori');
Route::get('/produk/detail/{id}', [ProdukController::class, 'detail'])->name('produk.detail');

// Cek ongkos kirim RajaOngkir (materi minggu ke-7)
Route::get('/cek-ongkir', [RajaOngkirController::class, 'index'])->name('ongkir.index');
Route::get('/provinces', [RajaOngkirController::class, 'getProvinces'])->name('ongkir.provinces');
Route::get('/cities', [RajaOngkirController::class, 'getCities'])->name('ongkir.cities');
Route::get('/districts', [RajaOngkirController::class, 'getDistricts'])->name('ongkir.districts');
Route::post('/cost', [RajaOngkirController::class, 'getCost'])->name('ongkir.cost');

// Fitur customer: akun dan keranjang (materi minggu ke-4 sampai ke-6)
Route::middleware('is.customer')->group(function () {
    Route::get('/customer/akun/{id}', [CustomerController::class, 'akun'])->name('customer.akun');
    Route::put('/customer/updateakun/{id}', [CustomerController::class, 'updateAkun'])->name('customer.updateakun');

    Route::post('/add-to-cart/{id}', [OrderController::class, 'addToCart'])->name('order.addToCart');
    Route::get('/cart', [OrderController::class, 'viewCart'])->name('order.cart');
    Route::post('/cart/update/{id}', [OrderController::class, 'updateCart'])->name('order.updateCart');
    Route::post('/cart/remove/{id}', [OrderController::class, 'removeFromCart'])->name('order.remove');
});
