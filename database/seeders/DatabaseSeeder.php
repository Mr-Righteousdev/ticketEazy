<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $operatorRole = Role::create(['name' => 'operator']);

        $admin = User::factory()->create([
            'name' => 'Lillian',
            'email' => 'lillian@ticketeezy.com',
        ]);
        $admin->assignRole($adminRole);

        $operator1 = User::factory()->create([
            'name' => 'Gate Operator 1',
            'email' => 'gate1@ticketeezy.com',
        ]);
        $operator1->assignRole($operatorRole);

        $operator2 = User::factory()->create([
            'name' => 'Gate Operator 2',
            'email' => 'gate2@ticketeezy.com',
        ]);
        $operator2->assignRole($operatorRole);

        Event::create([
            'name' => 'Grand Opening',
            'date' => now()->addMonth(),
            'time' => '18:00',
            'venue' => 'Novaspand Hall',
            'capacity' => 500,
            'status' => 'draft',
        ]);
    }
}
