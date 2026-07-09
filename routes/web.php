<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::view('/', 'welcome')->name('home');

Route::get('/dashboard', function () {
    if (Auth::user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } else if (Auth::user()->hasRole('operator')) {
        return redirect()->route('scan.dashboard');
    }
})->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::view('/admin', 'dashboard')->name('admin.dashboard');
});

Route::middleware(['auth', 'verified', 'role:operator'])->group(function () {
    Route::view('/scan', 'dashboard')->name('scan.dashboard');
});

require __DIR__.'/settings.php';
