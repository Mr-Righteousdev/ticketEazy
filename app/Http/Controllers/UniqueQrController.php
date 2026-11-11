<?php

namespace App\Http\Controllers;

use App\Models\UniqueQr;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;

class UniqueQrController extends Controller
{
    public function go(Request $request, string $code)
    {
        $qr = UniqueQr::where('code', $code)->first();

        $url = match (true) {
            $qr === null => config('uniqueqr.fallback_url'),
            $request->cookie(config('uniqueqr.cookie_name')) === '1' => $qr->owner_url,
            default => $qr->fallback_url ?? config('uniqueqr.fallback_url'),
        };

        return redirect()->away($url)->header('Cache-Control', 'no-store');
    }

    public function authorized(Request $request)
    {
        if ($request->cookie(config('uniqueqr.cookie_name')) !== '1') {
            return redirect()->away(config('uniqueqr.fallback_url'))->header('Cache-Control', 'no-store');
        }

        return response()->view('uqr.authorized')->header('Cache-Control', 'no-store');
    }

    public function setupForm()
    {
        return view('uqr.setup');
    }

    public function setup(Request $request)
    {
        $secret = (string) config('uniqueqr.secret');

        if ($secret === '' || ! hash_equals($secret, (string) $request->input('password'))) {
            return view('uqr.setup', ['error' => 'Incorrect phrase']);
        }

        $cookie = cookie(
            config('uniqueqr.cookie_name'), '1', config('uniqueqr.cookie_days') * 24 * 60,
            '/', null, app()->isProduction(), true, false, 'lax',
        );

        return response()->view('uqr.done')->withCookie($cookie);
    }

    public function forget()
    {
        return response()->view('uqr.done')->withoutCookie(config('uniqueqr.cookie_name'));
    }

    public function createForm()
    {
        return view('uqr.create');
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'owner_url' => ['nullable', 'url:http,https', 'max:2048'],
            'fallback_url' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        $data['owner_url'] ??= route('uqr.authorized');

        $qr = UniqueQr::create($data);

        $png = (new QRCode(new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'outputBase64' => false,
            'eccLevel' => EccLevel::H,
            'addQuietzone' => true,
            'quietzoneSize' => 4,
            'scale' => 30,
        ])))->render(route('uqr.go', $qr->code));

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => "attachment; filename=\"qr-{$qr->code}.png\"",
        ]);
    }
}
