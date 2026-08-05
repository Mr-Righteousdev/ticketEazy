<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'role:operator'])->group(function () {
    Route::view('/scan', 'dashboard')->name('scan.dashboard');
});

Route::get('/previews/{hash}.png', function (string $hash) {
    $path = Storage::disk('local')->path("previews/{$hash}.png");
    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path, ['Content-Type' => 'image/png']);
})->name('previews.show');

require __DIR__.'/settings.php';
