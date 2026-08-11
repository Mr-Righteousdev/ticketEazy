<?php

namespace Tests\Feature;

use App\Filament\Widgets\AttendanceStatsOverview;
use App\Models\Event;
use App\Models\ScanLog;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceReportsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('admin');

        return User::factory()->create()->assignRole('admin');
    }

    public function test_scan_logs_page_renders_for_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/scan-logs')
            ->assertOk();
    }

    public function test_attendance_stats_widget_counts(): void
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
            'name' => 'General Admission',
            'quantity' => 10,
            'price' => 0,
        ]);

        $operator = User::factory()->create();

        Ticket::create([
            'ticket_type_id' => $type->id,
            'token' => 'token-aaaa',
            'short_code' => 'AAAAAAAAAAAA',
            'status' => 'generated',
        ]);

        $used = Ticket::create([
            'ticket_type_id' => $type->id,
            'token' => 'token-bbbb',
            'short_code' => 'BBBBBBBBBBBB',
            'status' => 'used',
        ]);

        ScanLog::create([
            'ticket_id' => $used->id,
            'scanned_by' => $operator->id,
            'scanned_at' => now(),
            'result' => 'ok',
        ]);

        ScanLog::create([
            'ticket_id' => $used->id,
            'scanned_by' => $operator->id,
            'scanned_at' => now(),
            'result' => 'invalid',
        ]);

        $widget = app(AttendanceStatsOverview::class);
        $stats = (new ReflectionMethod($widget, 'getStats'))->invoke($widget);

        $this->assertSame(2, (int) $stats[0]->getValue()); // issued
        $this->assertSame(1, (int) $stats[1]->getValue()); // checked in
        $this->assertSame(1, (int) $stats[2]->getValue()); // remaining
        $this->assertSame(1, (int) $stats[3]->getValue()); // rejected (invalid + expired)
    }
}
