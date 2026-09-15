<?php

namespace Tests\Feature;

use App\Actions\GeneratePrintSheet;
use App\Models\Batch;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintSheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_print_sheet_throws_exception_when_no_pdfs(): void
    {
        $event = Event::create([
            'name' => 'Test Event',
            'date' => now(),
            'time' => now(),
            'venue' => 'Venue',
            'capacity' => 100,
        ]);

        $type = TicketType::create([
            'event_id' => $event->id,
            'name' => 'General',
            'quantity' => 10,
            'price' => 0,
        ]);

        $batch = Batch::create([
            'event_id' => $event->id,
            'ticket_type_id' => $type->id,
            'batch_timestamp' => 1234567890,
            'ticket_count' => 0,
            'status' => 'ready',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No individual ticket PDFs found for this batch.');

        app(GeneratePrintSheet::class)->handle($batch);
    }
}
