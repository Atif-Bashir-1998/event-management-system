<?php

use App\Http\Controllers\RolePermission\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RolePermission\AccessControlController;
use App\Http\Controllers\RolePermission\RoleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // user-roles related routes
    Route::resource('role', RoleController::class);
    Route::resource('permission', PermissionController::class);
    Route::post('/role/{role}/permissions', [AccessControlController::class, 'add_permission_to_role'])->name('role.add-permission');
    Route::delete('/role/{role}/permissions', [AccessControlController::class, 'remove_permission_from_role'])->name('role.remove-permission');
    Route::post('/user/{user}/permissions', [AccessControlController::class, 'add_permission_to_user'])->name('user.add-permission');
    Route::delete('/user/{user}/permissions', [AccessControlController::class, 'remove_permission_from_user'])->name('user.remove-permission');
    Route::get('/access-control', [AccessControlController::class, 'index'])->name('access-control');
});

require __DIR__.'/auth.php';
