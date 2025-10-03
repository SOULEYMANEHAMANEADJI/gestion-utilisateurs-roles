<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
})->name('welcome');


Auth::routes();

// Dashboard principal
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

// Routes d'administration
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin,admin'])->group(function () {
    // Dashboard admin (redirection vers dashboard principal)
    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('dashboard');

    // Routes utilisateurs - Actions spécifiques AVANT les routes génériques
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
    Route::get('/users/search', [UserController::class, 'quickSearch'])->name('users.search');
    Route::get('/users/suggestions', [UserController::class, 'suggestions'])->name('users.suggestions');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');

    // Actions en lot et statuts
    Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');

    // Analytics
    Route::get('/analytics', [UserController::class, 'analytics'])->name('analytics');

    // Routes génériques utilisateurs (après les spécifiques)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Gestion des rôles
    Route::resource('roles', RoleController::class);
});

// Redirection par défaut après connexion
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->name('home');
