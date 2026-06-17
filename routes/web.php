<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('units', \App\Http\Controllers\UnitOfMeasureController::class);
    Route::resource('branches', \App\Http\Controllers\BranchController::class);
    Route::resource('registers', \App\Http\Controllers\CashRegisterController::class);
    
    Route::get('/shift/open', [\App\Http\Controllers\CashRegisterSessionController::class, 'create'])->name('sessions.create');
    Route::post('/shift/open', [\App\Http\Controllers\CashRegisterSessionController::class, 'store'])->name('sessions.store');
    Route::get('/shift/close/{session}', [\App\Http\Controllers\CashRegisterSessionController::class, 'edit'])->name('sessions.edit');
    Route::put('/shift/close/{session}', [\App\Http\Controllers\CashRegisterSessionController::class, 'update'])->name('sessions.update');

    Route::get('/pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [\App\Http\Controllers\PosController::class, 'store'])->name('pos.store');

    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::post('/sales/{sale}/invoice-mock', [SaleController::class, 'invoiceMock'])->name('sales.invoice-mock');

    Route::resource('purchases', \App\Http\Controllers\PurchaseOrderController::class);
    Route::post('/purchases/{purchase}/receive', [\App\Http\Controllers\PurchaseOrderController::class, 'receive'])->name('purchases.receive');
    Route::post('/purchases/{purchase}/revert', [\App\Http\Controllers\PurchaseOrderController::class, 'revert'])->name('purchases.revert');

    Route::get('/inventory', [\App\Http\Controllers\InventoryController::class, 'index'])->name('inventory.index');

    // Admin exclusive routes
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);
        Route::resource('products', \App\Http\Controllers\ProductController::class);
        Route::resource('inventory', \App\Http\Controllers\InventoryController::class);
    });
});

require __DIR__.'/auth.php';
