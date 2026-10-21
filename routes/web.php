<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountInvoiceController;
use App\Http\Controllers\AccountOrderController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInvoiceController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

/* ============ Toko ============ */
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/kursus', [CourseController::class, 'index'])->name('courses.index');
Route::get('/kategori/{slug}', [CourseController::class, 'index'])->name('categories.show');
Route::get('/kursus/{slug}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
Route::patch('/keranjang/{courseId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{courseId}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('/keranjang', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/pesanan/{orderNumber}/sukses', [CheckoutController::class, 'success'])->name('orders.success');

/* ============ Pembayaran (Midtrans) ============ */
Route::get('/pesanan/{orderNumber}/bayar', [MidtransController::class, 'pay'])->name('midtrans.pay');
Route::get('/pembayaran/selesai', [MidtransController::class, 'finish'])->name('midtrans.finish');
Route::post('/midtrans/notifikasi', [MidtransController::class, 'notification'])->name('midtrans.notification');

/* ============ Agen AI Penjawab Konsumen ============ */
Route::post('/agen/tanya', [AgentController::class, 'chat'])
    ->middleware('throttle:30,1')
    ->name('agent.chat');

/* ============ Layanan (PKL, Kursus, MOU, dll) ============ */
Route::get('/layanan', [ServiceController::class, 'index'])->name('services.index');
Route::get('/layanan/cek-status', [ServiceController::class, 'track'])->name('services.track');
Route::get('/layanan/{type}', [ServiceController::class, 'show'])->name('services.show');
Route::post('/layanan/{type}', [ServiceController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('services.store');
Route::get('/layanan/{type}/sukses/{ticket}', [ServiceController::class, 'success'])->name('services.success');

/* ============ Autentikasi ============ */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/* ============ Panel Admin (super admin & operator) ============ */
Route::middleware(['auth', 'role:super_admin,operator'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Kursus & pesanan: super admin + operator
        Route::get('/kursus', [AdminCourseController::class, 'index'])->name('courses.index');
        Route::get('/kursus/baru', [AdminCourseController::class, 'create'])->name('courses.create');
        Route::post('/kursus', [AdminCourseController::class, 'store'])->name('courses.store');
        Route::get('/kursus/{course}/edit', [AdminCourseController::class, 'edit'])->name('courses.edit');
        Route::put('/kursus/{course}', [AdminCourseController::class, 'update'])->name('courses.update');
        Route::delete('/kursus/{course}', [AdminCourseController::class, 'destroy'])->name('courses.destroy');

        Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/pesanan/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/pesanan/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

        // Tagihan murid: super admin + operator
        Route::get('/tagihan', [AdminInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/tagihan/baru', [AdminInvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/tagihan', [AdminInvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/tagihan/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/tagihan/{invoice}/edit', [AdminInvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/tagihan/{invoice}', [AdminInvoiceController::class, 'update'])->name('invoices.update');
        Route::patch('/tagihan/{invoice}/status', [AdminInvoiceController::class, 'updateStatus'])->name('invoices.status');
        Route::delete('/tagihan/{invoice}', [AdminInvoiceController::class, 'destroy'])->name('invoices.destroy');

        // Laporan keuangan: super admin + operator
        Route::get('/laporan', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/ekspor', [AdminReportController::class, 'export'])->name('reports.export');

        // Layanan (PKL, kursus, MOU, dll): super admin + operator
        Route::get('/layanan', [AdminServiceController::class, 'index'])->name('services.index');
        Route::get('/layanan/{serviceRequest}', [AdminServiceController::class, 'show'])->name('services.show');
        Route::patch('/layanan/{serviceRequest}/status', [AdminServiceController::class, 'updateStatus'])->name('services.status');
        Route::delete('/layanan/{serviceRequest}', [AdminServiceController::class, 'destroy'])->name('services.destroy');

        // Hanya super admin: pengguna & kategori
        Route::middleware('role:super_admin')->group(function () {
            Route::get('/pengguna', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/pengguna/baru', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('/pengguna', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('/pengguna/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('/pengguna/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::delete('/pengguna/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

            Route::get('/kategori', [AdminCategoryController::class, 'index'])->name('categories.index');
            Route::post('/kategori', [AdminCategoryController::class, 'store'])->name('categories.store');
            Route::put('/kategori/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/kategori/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
        });
    });

/* ============ Area Murid ============ */
Route::middleware(['auth', 'role:murid'])
    ->prefix('akun')
    ->name('account.')
    ->group(function () {
        Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');

        // Tagihan murid
        Route::get('/tagihan', [AccountInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/tagihan/{invoice}', [AccountInvoiceController::class, 'show'])->name('invoices.show');
        Route::post('/tagihan/{invoice}/bayar', [AccountInvoiceController::class, 'pay'])->name('invoices.pay');

        // Pesanan & profil
        Route::get('/pesanan', [AccountOrderController::class, 'index'])->name('orders.index');
        Route::get('/pesanan/{orderNumber}', [AccountOrderController::class, 'show'])->name('orders.show');
        Route::get('/profil', [AccountController::class, 'profile'])->name('profile');
        Route::put('/profil', [AccountController::class, 'updateProfile'])->name('profile.update');
    });

/* Callback Snap untuk tagihan (di luar guard murid agar aman saat redirect). */
Route::get('/pembayaran/tagihan/selesai', [AccountInvoiceController::class, 'finish'])->name('account.invoices.finish');
