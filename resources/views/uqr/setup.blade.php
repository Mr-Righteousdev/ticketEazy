<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>&nbsp;</title>
</head>
<body style="font-family: sans-serif; max-width: 320px; margin: 4rem auto; padding: 0 1rem;">
    <form method="POST" action="{{ route('uqr.setup') }}">
        @csrf
        <input type="password" name="password" aria-label="Phrase" required autofocus style="width: 100%; padding: .5rem; box-sizing: border-box;">
        @isset($error)<p style="color: #b00;">{{ $error }}</p>@endisset
        <button type="submit" style="margin-top: .5rem; padding: .5rem 1rem;">Submit</button>
    </form>
</body>
</html>
