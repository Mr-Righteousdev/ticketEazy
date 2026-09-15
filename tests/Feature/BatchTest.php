<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BatchTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('admin');

        return User::factory()->create()->assignRole('admin');
    }

    private function createEventWithTicketType(): array
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

        return compact('event', 'type');
    }

    public function test_batches_page_renders_for_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/batches')
            ->assertOk();
    }

    public function test_batch_model_derives_from_batch_path(): void
    {
        $result = Batch::deriveFromBatchPath('tickets/1/2/batch-1234567890');

        $this->assertNotNull($result);
        $this->assertSame(1, $result['eventId']);
        $this->assertSame(2, $result['typeId']);
        $this->assertSame(1234567890, $result['timestamp']);
    }

    public function test_batch_model_returns_null_for_invalid_path(): void
    {
        $result = Batch::deriveFromBatchPath('invalid/path');

        $this->assertNull($result);
    }

    public function test_batch_path_attribute_is_correct(): void
    {
        ['event' => $event, 'type' => $type] = $this->createEventWithTicketType();

        $batch = Batch::create([
            'event_id' => $event->id,
            'ticket_type_id' => $type->id,
            'batch_timestamp' => 1234567890,
            'ticket_count' => 10,
            'status' => 'ready',
        ]);

        $this->assertSame("tickets/{$event->id}/{$type->id}/batch-1234567890", $batch->batch_path);
    }

    public function test_batch_formatted_date_attribute(): void
    {
        ['event' => $event, 'type' => $type] = $this->createEventWithTicketType();

        $batch = Batch::create([
            'event_id' => $event->id,
            'ticket_type_id' => $type->id,
            'batch_timestamp' => 1704067200,
            'ticket_count' => 10,
            'status' => 'ready',
        ]);

        $this->assertNotEmpty($batch->formatted_date);
    }

    public function test_batch_scope_ready(): void
    {
        ['event' => $event, 'type' => $type] = $this->createEventWithTicketType();

        Batch::create([
            'event_id' => $event->id,
            'ticket_type_id' => $type->id,
            'batch_timestamp' => 1234567890,
            'ticket_count' => 5,
            'status' => 'ready',
        ]);

        Batch::create([
            'event_id' => $event->id,
            'ticket_type_id' => $type->id,
            'batch_timestamp' => 1234567891,
            'ticket_count' => 3,
            'status' => 'generating',
        ]);

        $this->assertSame(1, Batch::ready()->count());
    }

    public function test_batch_individual_pdfs_returns_empty_when_no_directory(): void
    {
        ['event' => $event, 'type' => $type] = $this->createEventWithTicketType();

        $batch = Batch::create([
            'event_id' => $event->id,
            'ticket_type_id' => $type->id,
            'batch_timestamp' => 1234567890,
            'ticket_count' => 0,
            'status' => 'ready',
        ]);

        $this->assertEmpty($batch->individual_pdfs);
    }
}
