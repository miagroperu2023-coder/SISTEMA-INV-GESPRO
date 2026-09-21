<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\cashier\CashierShiftController;
use App\Http\Controllers\category\CategoryController;
use App\Http\Controllers\client\ClientController;
use App\Http\Controllers\configuracion\ConfiguracionController;
use App\Http\Controllers\equipo\EquipoController;
use App\Http\Controllers\product\ProductController;
use App\Http\Controllers\report\ReportController;
use App\Http\Controllers\sales\SalesController;
use App\Http\Controllers\sede\SedeController;
use App\Http\Controllers\sizeSet\SizeSetController;
use App\Http\Controllers\superAdmin\SuperAdminController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});


Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');

Route::get('/resgistro', [RegisterController::class, 'index'])->name('register');
Route::post('/resgistro', [RegisterController::class, 'store'])->name('register.store');

Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/super-admin/negocios', [SuperAdminController::class, 'index'])->name('superadmin.negocios');
});

Route::middleware(['auth', 'suscripcion_activa'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/ventas', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/caja', [CashierShiftController::class, 'index'])->name('caja.index');
    Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
    Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/tallas', [SizeSetController::class, 'index'])->name('sizesets.index');
    Route::get('/clientes', [ClientController::class, 'index'])->name('clients.index');

    Route::middleware('dueno_admin')->group(function () {
        Route::get('/sedes', [SedeController::class, 'index'])->name('sedes.index');
        Route::get('/equipo', [EquipoController::class, 'index'])->name('equipo.index');
        Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/Empresa', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    });
});
