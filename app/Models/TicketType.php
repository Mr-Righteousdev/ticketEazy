<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'price',
        'quantity',
        'template_path',
        'is_discount',
        'discount_label',
        'parent_type_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_discount' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function parentType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'parent_type_id');
    }

    public function childTypes(): HasMany
    {
        return $this->hasMany(TicketType::class, 'parent_type_id');
    }
}
