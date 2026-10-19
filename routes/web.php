<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Redirect otomatis dari /dashboard bawaan Breeze ke /admin/dashboard
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');

    // Grouping semua route Admin dengan Prefix & Name Prefix
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        
        // Action Knowledge Base & Settings
        Route::post('/knowledge', [AdminController::class, 'storeKnowledge'])->name('knowledge.store');
        Route::put('/knowledge/{id}', [AdminController::class, 'updateKnowledge'])->name('knowledge.update');
        Route::delete('/knowledge/{id}', [AdminController::class, 'destroyKnowledge'])->name('knowledge.destroy');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    });

    // Profile routes bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';