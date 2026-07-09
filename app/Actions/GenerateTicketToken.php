<?php

namespace App\Actions;

use Illuminate\Support\Str;

class GenerateTicketToken
{
    public function generate(int $eventId): string
    {
        $uuid = (string) Str::uuid();
        $hmac = hash_hmac('sha256', $uuid.'.'.$eventId, config('app.key'));
        $payload = $uuid.'.'.$hmac;

        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    public function decode(string $token): ?array
    {
        $payload = base64_decode(strtr($token, '-_', '+/'));

        if ($payload === false) {
            return null;
        }

        $parts = explode('.', $payload, 2);

        if (count($parts) !== 2) {
            return null;
        }

        [$uuid, $hmac] = $parts;

        return compact('uuid', 'hmac');
    }

    public function verify(string $token, int $eventId): bool
    {
        $decoded = $this->decode($token);

        if ($decoded === null) {
            return false;
        }

        $expected = hash_hmac('sha256', $decoded['uuid'].'.'.$eventId, config('app.key'));

        return hash_equals($expected, $decoded['hmac']);
    }
}
