<?php

use App\Livewire\ScanTicket;
use App\Models\Ticket;
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

Route::get('/verify/{token}', function (string $token) {
    $ticket = Ticket::with('ticketType.event')
        ->where('token', $token)
        ->first();

    return view('ticket.verify', ['ticket' => $ticket]);
})->name('ticket.verify');

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

require __DIR__.'/settings.php';
