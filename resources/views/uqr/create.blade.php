<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Create QR</title>
</head>
<body style="font-family: sans-serif; max-width: 480px; margin: 4rem auto; padding: 0 1rem;">
    <form method="POST" action="{{ route('uqr.create') }}">
        @csrf
        <label>Private link (owner's phone, optional)<br>
            <input type="url" name="owner_url" value="{{ old('owner_url') }}" placeholder="{{ route('uqr.authorized') }}" maxlength="2048" style="width: 100%; padding: .5rem; box-sizing: border-box;">
        </label>
        @error('owner_url')<p style="color: #b00;">{{ $message }}</p>@enderror
        <br><br>
        <label>Fallback link (everyone else, optional)<br>
            <input type="url" name="fallback_url" value="{{ old('fallback_url') }}" maxlength="2048" placeholder="{{ config('uniqueqr.fallback_url') }}" style="width: 100%; padding: .5rem; box-sizing: border-box;">
        </label>
        @error('fallback_url')<p style="color: #b00;">{{ $message }}</p>@enderror
        <br><br>
        <button type="submit" style="padding: .5rem 1rem;">Download QR</button>
    </form>
</body>
</html>
