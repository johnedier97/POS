<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CashRegisterSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitOfMeasureController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('units', UnitOfMeasureController::class);
    Route::resource('branches', BranchController::class);
    Route::resource('registers', CashRegisterController::class);

    Route::get('/shift/open', [CashRegisterSessionController::class, 'create'])->name('sessions.create');
    Route::post('/shift/open', [CashRegisterSessionController::class, 'store'])->name('sessions.store');
    Route::get('/shift/close/{session}', [CashRegisterSessionController::class, 'edit'])->name('sessions.edit');
    Route::put('/shift/close/{session}', [CashRegisterSessionController::class, 'update'])->name('sessions.update');

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');

    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::post('/sales/{sale}/invoice-mock', [SaleController::class, 'invoiceMock'])->name('sales.invoice-mock');

    Route::resource('purchases', PurchaseOrderController::class);
    Route::post('/purchases/{purchase}/receive', [PurchaseOrderController::class, 'receive'])->name('purchases.receive');
    Route::post('/purchases/{purchase}/revert', [PurchaseOrderController::class, 'revert'])->name('purchases.revert');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

    // Admin exclusive routes
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('products', ProductController::class);
        Route::resource('inventory', InventoryController::class);
    });
});

require __DIR__.'/auth.php';
