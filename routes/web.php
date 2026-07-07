<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Src\Billing\Presentation\Controllers\PaymentController;
use Src\Catalog\Presentation\Controllers\AdminCourseController;
use Src\Catalog\Presentation\Controllers\CatalogController;
use Src\Enrollment\Presentation\Controllers\MyCoursesController;
use Src\Ordering\Presentation\Controllers\CheckoutController;
use Src\Payment\Presentation\Controllers\FakeDokuCheckoutController;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Dashboard — mengarahkan sesuai peran (admin vs mahasiswa)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    // Admin diarahkan ke manajemen kursus; mahasiswa ke katalog.
    return Auth::user()->isAdmin()
        ? redirect()->route('admin.courses.index')
        : redirect()->route('catalog.index');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Area ADMIN — hanya untuk peran admin (Bikin & kelola kursus)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('courses', [AdminCourseController::class, 'index'])->name('courses.index');
        Route::get('courses/create', [AdminCourseController::class, 'create'])->name('courses.create');
        Route::post('courses', [AdminCourseController::class, 'store'])->name('courses.store');
        Route::get('courses/{course}/edit', [AdminCourseController::class, 'edit'])->name('courses.edit');
        Route::put('courses/{course}', [AdminCourseController::class, 'update'])->name('courses.update');
    });

/*
|--------------------------------------------------------------------------
| Area MAHASISWA — hanya untuk peran student
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:student'])->group(function () {
    // Katalog kursus.
    Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('catalog/{course}', [CatalogController::class, 'show'])->name('catalog.show');

    // Alur Order -> Checkout -> Bayar.
    Route::post('checkout/{course}/start', [CheckoutController::class, 'start'])->name('checkout.start');
    Route::get('checkout/{order}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('checkout/{order}/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('orders', [CheckoutController::class, 'myOrders'])->name('orders.index');

    // Halaman pembayaran (invoice) + polling pelunasan.
    Route::get('payment/{invoice}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('payment/{invoice}/poll', [PaymentController::class, 'poll'])->name('payment.poll');

    // Simulasi hosted checkout DOKU.
    Route::get('doku/checkout/{reference}', [FakeDokuCheckoutController::class, 'show'])->name('payment.fake-checkout');
    Route::post('doku/checkout/{reference}/pay', [FakeDokuCheckoutController::class, 'pay'])->name('payment.fake-pay');

    // Kursus yang dimiliki mahasiswa + halaman belajar.
    Route::get('my-courses', [MyCoursesController::class, 'index'])->name('my-courses.index');
    Route::get('my-courses/{course}/learn', [MyCoursesController::class, 'learn'])->name('my-courses.learn');
});

/*
|--------------------------------------------------------------------------
| Profil (Breeze)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
