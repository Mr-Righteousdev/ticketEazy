<?php

use App\Http\Controllers\UniqueQrController;
use App\Livewire\ScanTicket;
use App\Models\TicketType;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('admin')) {
        return redirect('/admin');
    }

    if ($user->hasRole('operator')) {
        return redirect()->route('scan.dashboard');
    }

    return redirect()->route('home');
})->name('dashboard');

Route::get('/verify/{token}', function () {
    return redirect('https://www.youtube.com/watch?v=R5P_-lEws6Q&pp=ygUEd2V3ZQ%3D%3D');
})->name('ticket.verify');

Route::get('/v/{code}', function () {
    return redirect('https://www.youtube.com/watch?v=R5P_-lEws6Q&pp=ygUEd2V3ZQ%3D%3D');
})->name('ticket.quick-verify');

Route::middleware(['auth', 'verified', 'role:operator'])->group(function () {
    Route::get('/scan', ScanTicket::class)->name('scan.dashboard');
});

Route::middleware(['auth'])->get('/admin/tickets/batches/{typeId}/{timestamp}/download', function (int $typeId, int $timestamp) {
    $ticketType = TicketType::findOrFail($typeId);

    $relativePath = "tickets/{$ticketType->event_id}/{$typeId}/downloads/{$typeId}-batch-{$timestamp}.zip";
    $path = Storage::disk('local')->path($relativePath);

    if (! file_exists($path)) {
        abort(404);
    }

    return response()->download($path, "{$ticketType->name}-batch-{$timestamp}.zip");
})->name('tickets.batch.download');

Route::get('/previews/{hash}.png', function (string $hash) {
    $path = Storage::disk('local')->path("previews/{$hash}.png");
    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path, ['Content-Type' => 'image/png']);
})->name('previews.show');

// Device-aware QR: owner phone (cookie from /uqr/setup) goes to the private link, everyone else to the fallback.
Route::get('/go/{code}', [UniqueQrController::class, 'go'])->name('uqr.go');
Route::get('/uqr/setup', [UniqueQrController::class, 'setupForm']);
Route::post('/uqr/setup', [UniqueQrController::class, 'setup'])->middleware('throttle:5,1')->name('uqr.setup');
Route::get('/uqr/forget', [UniqueQrController::class, 'forget']);

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/uqr/create', [UniqueQrController::class, 'createForm']);
    Route::post('/uqr/create', [UniqueQrController::class, 'create'])->name('uqr.create');
});

require __DIR__.'/settings.php';
