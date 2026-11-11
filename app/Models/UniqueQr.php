<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UniqueQr extends Model
{
    protected $fillable = ['code', 'owner_url', 'fallback_url'];

    protected static function booted(): void
    {
        static::creating(function (UniqueQr $qr) {
            do {
                $qr->code = Str::random(10);
            } while (static::where('code', $qr->code)->exists());
        });
    }
}
