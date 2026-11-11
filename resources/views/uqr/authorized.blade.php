<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0b0c0e">
    <title>Authorized — TicketEezy</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @fonts
    <style>
        :root {
            --stage: #0b0c0e;
            --paper: #f1ebdd;
            --paper-mid: #ece4d1;
            --ink: #1b1b1b;
            --muted: #6e6757;
            --amber: #f4a825;
            --fog: #a29b88;
            --ok: #1f7a4d;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            padding: 24px 16px;
            background:
                radial-gradient(ellipse 60% 45% at 50% 42%, rgba(244, 168, 37, .16), transparent 70%),
                var(--stage);
            color: var(--paper);
            font-family: "Instrument Sans", ui-sans-serif, system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }
        .label {
            font-size: .68rem;
            letter-spacing: .22em;
            text-transform: uppercase;
            font-weight: 600;
        }
        .wrap { width: 100%; max-width: 360px; text-align: center; }
        .top { color: var(--fog); margin-bottom: 22px; }
        .top b { color: var(--amber); font-weight: 600; }

        .stub {
            position: relative;
            background: linear-gradient(180deg, var(--paper), var(--paper-mid));
            color: var(--ink);
            border-radius: 14px;
            padding: 30px 26px 22px;
            box-shadow: 0 30px 60px -20px rgba(0, 0, 0, .8), 0 0 0 1px rgba(255, 255, 255, .04);
            animation: settle .7s cubic-bezier(.2, .8, .2, 1) both;
        }
        /* punched notches + perforation line */
        .stub::before, .stub::after {
            content: "";
            position: absolute;
            top: calc(100% - 78px);
            width: 26px; height: 26px;
            border-radius: 50%;
            background: var(--stage);
        }
        .stub::before { left: -13px; }
        .stub::after { right: -13px; }
        .perf {
            border: 0;
            border-top: 2px dashed rgba(27, 27, 27, .22);
            margin: 26px -6px 16px;
        }
        .seal {
            width: 92px; height: 92px;
            margin: 0 auto 18px;
            border-radius: 50%;
            display: grid; place-items: center;
            background: var(--ink);
            color: var(--amber);
            box-shadow: 0 0 0 6px rgba(244, 168, 37, .18);
            animation: pulse 2.4s ease-in-out 1s infinite;
        }
        .seal svg { width: 44px; height: 44px; }
        .seal path {
            stroke-dasharray: 40;
            stroke-dashoffset: 40;
            animation: draw .5s ease-out .55s forwards;
        }
        .eyebrow { color: var(--muted); }
        h1 {
            margin: 8px 0 6px;
            font-size: clamp(2.1rem, 11vw, 2.7rem);
            font-weight: 650;
            letter-spacing: -.03em;
            line-height: 1;
        }
        .lede { margin: 0; color: var(--muted); font-size: .98rem; line-height: 1.5; }

        .stamp {
            position: absolute;
            top: 14px; right: 12px;
            padding: 6px 10px;
            border: 2.5px solid var(--ok);
            border-radius: 6px;
            color: var(--ok);
            font-size: .72rem;
            letter-spacing: .18em;
            font-weight: 700;
            text-transform: uppercase;
            transform: rotate(12deg);
            opacity: 0;
            animation: stamp .35s cubic-bezier(.3, 1.6, .5, 1) .9s forwards;
        }
        .meta { display: flex; justify-content: space-between; color: var(--muted); font-variant-numeric: tabular-nums; }
        .meta span:last-child { color: var(--ink); }
        .foot { margin-top: 22px; color: var(--fog); }

        @keyframes settle { from { opacity: 0; transform: translateY(18px) rotate(-2deg); } }
        @keyframes draw { to { stroke-dashoffset: 0; } }
        @keyframes stamp {
            from { opacity: 0; transform: rotate(12deg) scale(2.2); }
            to { opacity: .9; transform: rotate(12deg) scale(1); }
        }
        @keyframes pulse { 50% { box-shadow: 0 0 0 14px rgba(244, 168, 37, 0); } }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0s !important; animation-delay: 0s !important; animation-iteration-count: 1 !important; }
        }
    </style>
</head>
<body>
    <main class="wrap">
        <p class="label top">Ticket<b>Eezy</b> · Verified device</p>

        <section class="stub" aria-labelledby="title">
            <span class="stamp" aria-hidden="true">Cleared</span>
            <div class="seal" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
            </div>
            <p class="label eyebrow">Access granted</p>
            <h1 id="title">Authorized</h1>
            <p class="lede">This device is recognised. You're cleared to proceed.</p>

            <hr class="perf">
            <div class="label meta">
                <span>Ref</span>
                <span>{{ strtoupper(substr(md5(now()->format('YmdHi')), 0, 8)) }}</span>
            </div>
            <div class="label meta" style="margin-top: 6px;">
                <span>Time</span>
                <span>{{ now()->format('d M Y · H:i') }}</span>
            </div>
        </section>

        <p class="label foot">Admit one · Device verified</p>
    </main>
</body>
</html>
