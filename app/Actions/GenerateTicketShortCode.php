<?php

namespace App\Actions;

use App\Models\Ticket;

class GenerateTicketShortCode
{
    /**
     * Unambiguous alphabet: no I, L, O, 0, or 1.
     */
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const CODE_LENGTH = 12;

    public function generate(): string
    {
        $length = self::CODE_LENGTH;

        do {
            $code = '';

            for ($i = 0; $i < $length; $i++) {
                $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
        } while (Ticket::where('short_code', $code)->exists());

        return $code;
    }

    public static function isShortCode(string $value): bool
    {
        return preg_match('/^[A-HJ-NP-Z2-9]{'.self::CODE_LENGTH.'}$/D', $value) === 1;
    }
}
