<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Event extends Model
{
    protected $fillable = [
        'name',
        'date',
        'time',
        'venue',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time' => 'datetime:H:i',
        ];
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function operatorAssignments(): HasMany
    {
        return $this->hasMany(EventOperatorAssignment::class);
    }

    public function assignedOperators(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, EventOperatorAssignment::class, 'event_id', 'user_id');
    }
}
