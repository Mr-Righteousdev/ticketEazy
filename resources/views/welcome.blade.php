<!DOCTYPE html>
{{--
    DIRECTION CONTRACT — "The Doorman's Ticket" (user-pinned)
    THESIS: This page proves TicketEezy by showing the admission itself — a single
            server-verified ticket stub — not a screenshot or a headline claim.
    OWN-WORLD: near-black venue stage; one warm paper stub as the only lit object;
            amber working accent; perforation, serial codes and a QR module as the
            recurring grammar; micro-labels tracked uppercase.
    STORY: A guest at the door sees a real ticket, understands it is issued once
            and checked in by a single scan, and signs in (or opens the scanner).
    FIRST VIEWPORT: brand + Sign in top bar; eyebrow, "Admit one. Admit once."
            headline and lede on the left; the tilted, perforated ticket stub with a
            live QR module on the right; the primary action sits at the foot of the copy.
    FORM: user-pinned direction (no concept roll). The stub settles in with one
            authored tear/stamp entrance, respecting prefers-reduced-motion.
--}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="TicketEezy issues server-verified event tickets and checks them in at the gate with a single scan.">

        <title>TicketEezy — Server-verified event tickets</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        <style>
            :root {
                --stage: #0b0c0e;
                --stage-raise: #121317;
                --paper: #f1ebdd;
                --paper-mid: #ece4d1;
                --ink: #1b1b1b;
                --muted: #6e6757;
                --amber: #f4a825;
                --fog: #a29b88;
                --serif: none;
            }

            * {
                box-sizing: border-box;
            }

            html {
                color-scheme: dark;
            }

            body {
                margin: 0;
                min-height: 100dvh;
                display: flex;
                flex-direction: column;
                background: var(--stage);
                color: #e9e4d6;
                font-family: "Instrument Sans", ui-sans-serif, system-ui, -apple-system, sans-serif;
                -webkit-font-smoothing: antialiased;
                overflow-x: hidden;
            }

            a {
                color: inherit;
            }

            :focus-visible {
                outline: 2px solid var(--amber);
                outline-offset: 3px;
                border-radius: 2px;
            }

            .sr-only {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0 0 0 0);
                white-space: nowrap;
                border: 0;
            }

            /* ---------- Stage ---------- */

            .stage {
                position: fixed;
                inset: 0;
                z-index: -1;
                pointer-events: none;
                background:
                    radial-gradient(720px 480px at 68% 42%, rgba(244, 168, 37, 0.10), transparent 62%),
                    radial-gradient(560px 420px at 24% 8%, rgba(255, 255, 255, 0.045), transparent 60%),
                    var(--stage);
            }

            .stage::after {
                content: "";
                position: absolute;
                inset: 0;
                background-image:
                    repeating-linear-gradient(0deg, transparent 0 3px, rgba(255, 255, 255, 0.012) 3px 4px),
                    repeating-linear-gradient(90deg, transparent 0 3px, rgba(255, 255, 255, 0.012) 3px 4px);
                opacity: 0.55;
                pointer-events: none;
            }

            /* ---------- Header ---------- */

            .masthead {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 1.5rem clamp(1.25rem, 4vw, 3.5rem);
            }

            .brand {
                display: inline-flex;
                align-items: center;
                gap: 0.6rem;
                color: #f3eee2;
                text-decoration: none;
                letter-spacing: -0.01em;
            }

            .brand svg {
                height: 1.35rem;
                width: auto;
                fill: var(--amber);
                flex-shrink: 0;
            }

            .brand strong {
                font-size: 1.05rem;
                font-weight: 600;
            }

            .brand small {
                font-size: 0.7rem;
                font-weight: 500;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: var(--fog);
                padding-left: 0.6rem;
                border-left: 1px solid rgba(255, 255, 255, 0.14);
            }

            .nav-actions {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .nav-link {
                font-size: 0.82rem;
                font-weight: 600;
                letter-spacing: 0.02em;
                color: #d6d0c0;
                text-decoration: none;
                padding: 0.55rem 0.9rem;
                border-radius: 4px;
                transition: color 0.18s ease;
            }

            .nav-link:hover {
                color: #ffffff;
            }

            .nav-cta {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                font-size: 0.82rem;
                font-weight: 600;
                letter-spacing: 0.02em;
                color: var(--ink);
                background: var(--amber);
                text-decoration: none;
                padding: 0.6rem 1.05rem;
                border-radius: 4px;
                box-shadow: 0 10px 26px -12px rgba(244, 168, 37, 0.55);
                transition: transform 0.16s ease, background 0.16s ease;
            }

            .nav-cta:hover {
                transform: translateY(-1px);
                background: #ffb436;
            }

            /* ---------- Hero ---------- */

            .hero {
                flex: 1;
                display: grid;
                grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
                align-items: center;
                gap: clamp(2rem, 5vw, 5rem);
                width: min(1180px, 100%);
                margin: 0 auto;
                padding: clamp(1.5rem, 5vh, 4rem) clamp(1.25rem, 4vw, 3.5rem) clamp(2.5rem, 7vh, 5rem);
            }

            .hero-copy {
                animation: rise 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
                animation-delay: 0.06s;
            }

            .eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 0.6rem;
                margin: 0 0 1.1rem;
                font-size: 0.72rem;
                font-weight: 600;
                letter-spacing: 0.22em;
                text-transform: uppercase;
                color: var(--amber);
            }

            .eyebrow::before {
                content: "";
                width: 2rem;
                height: 1px;
                background: var(--amber);
                opacity: 0.7;
            }

            .hero h1 {
                margin: 0;
                font-size: clamp(2.6rem, 5.2vw, 4.4rem);
                font-weight: 650;
                letter-spacing: -0.03em;
                line-height: 1.02;
                color: #f4f0e5;
                text-wrap: balance;
            }

            .hero h1 em {
                font-style: normal;
                color: var(--amber);
            }

            .lede {
                margin: 1.25rem 0 0;
                max-width: 34rem;
                font-size: clamp(1.02rem, 1.35vw, 1.14rem);
                line-height: 1.6;
                color: #cfc8b6;
            }

            .hero-actions {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 1rem;
                margin-top: 2rem;
            }

            .btn-primary {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.95rem;
                font-weight: 650;
                letter-spacing: 0.01em;
                color: var(--ink);
                background: var(--amber);
                text-decoration: none;
                padding: 0.85rem 1.6rem;
                border-radius: 4px;
                box-shadow: 0 16px 40px -14px rgba(244, 168, 37, 0.6);
                transition: transform 0.16s ease, background 0.16s ease;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                background: #ffb436;
            }

            .btn-secondary {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                font-size: 0.9rem;
                font-weight: 600;
                color: #d6d0c0;
                text-decoration: none;
                padding: 0.85rem 0.4rem;
                transition: color 0.16s ease;
            }

            .btn-secondary:hover {
                color: #ffffff;
            }

            .btn-secondary svg {
                height: 0.95rem;
                width: auto;
            }

            /* ---------- Ticket ---------- */

            .ticket-stage {
                display: flex;
                justify-content: center;
                align-items: center;
                animation: ticket-in 0.85s cubic-bezier(0.16, 1, 0.3, 1) both;
                animation-delay: 0.18s;
            }

            .ticket {
                --perf: 152px;
                --notch: 11px;
                position: relative;
                display: grid;
                grid-template-columns: var(--perf) 1fr;
                width: min(34rem, 100%);
                background:
                    radial-gradient(rgba(27, 27, 27, 0.05) 0.6px, transparent 0.7px),
                    linear-gradient(180deg, rgba(244, 168, 37, 0.11), transparent 15%),
                    linear-gradient(180deg, var(--paper), var(--paper-mid));
                background-size: 3px 3px, auto, auto;
                color: var(--ink);
                border-radius: 4px;
                box-shadow:
                    inset 0 1px 0 rgba(244, 168, 37, 0.45),
                    inset 0 0 0 1px rgba(27, 27, 27, 0.09),
                    0 44px 90px -28px rgba(0, 0, 0, 0.75),
                    0 8px 22px -12px rgba(244, 168, 37, 0.18);
                transform: rotate(-1.2deg);
                transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
                -webkit-mask-image:
                    radial-gradient(circle var(--notch) at var(--perf) 0, transparent var(--notch), #000 calc(var(--notch) + 0.6px)),
                    radial-gradient(circle var(--notch) at var(--perf) 100%, transparent var(--notch), #000 calc(var(--notch) + 0.6px));
                -webkit-mask-composite: source-in;
                mask-image:
                    radial-gradient(circle var(--notch) at var(--perf) 0, transparent var(--notch), #000 calc(var(--notch) + 0.6px)),
                    radial-gradient(circle var(--notch) at var(--perf) 100%, transparent var(--notch), #000 calc(var(--notch) + 0.6px));
                mask-composite: intersect;
            }

            .ticket:hover {
                transform: rotate(0deg) translateY(-3px);
            }

            /* Perforation hole-track along the stub tear line. */
            .ticket::after {
                content: "";
                position: absolute;
                top: 15px;
                bottom: 15px;
                left: calc(var(--perf) - 6px);
                width: 12px;
                z-index: 1;
                pointer-events: none;
                background-image: radial-gradient(circle 2px at center, rgba(27, 27, 27, 0.55) 1.7px, transparent 2.3px);
                background-size: 12px 13px;
                background-repeat: repeat-y;
            }

            .stub {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 1.6rem 1.1rem 1.3rem;
                border-right: 1px solid rgba(27, 27, 27, 0.22);
                text-align: center;
            }

            .stub .stub-icon {
                height: 1.5rem;
                width: auto;
                fill: var(--ink);
                opacity: 0.85;
            }

            .stub .stub-word {
                font-size: 0.92rem;
                font-weight: 650;
                letter-spacing: -0.01em;
            }

            .stub .stub-admit {
                font-size: 1.15rem;
                font-weight: 700;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                writing-mode: vertical-rl;
                transform: rotate(180deg);
            }

            .stub .stub-label {
                font-size: 0.6rem;
                font-weight: 600;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                color: var(--muted);
            }

            .stub .stub-serial {
                font-size: 0.62rem;
                font-weight: 600;
                letter-spacing: 0.22em;
                color: var(--ink);
            }

            .ticket-body {
                display: flex;
                align-items: center;
                gap: clamp(1rem, 3vw, 2rem);
                padding: 1.6rem clamp(1.2rem, 3vw, 2rem);
            }

            .ticket-info {
                flex: 1;
                min-width: 0;
            }

            .ticket-event {
                margin: 0;
                font-size: clamp(1.35rem, 2.6vw, 1.9rem);
                font-weight: 700;
                letter-spacing: -0.02em;
                line-height: 1.05;
                text-transform: uppercase;
            }

            .ticket-meta {
                margin: 0.55rem 0 0;
                font-size: 0.72rem;
                font-weight: 600;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: var(--muted);
            }

            .ticket-meta span {
                display: inline-block;
            }

            .ticket-meta span + span::before {
                content: "·";
                margin: 0 0.45rem;
                opacity: 0.7;
            }

            .ticket-reg {
                display: flex;
                gap: 1rem;
                margin: 0.65rem 0 0;
                font-size: 0.6rem;
                font-weight: 600;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: var(--muted);
            }

            .ticket-reg b {
                font-weight: 650;
                color: var(--ink);
                letter-spacing: 0.1em;
            }

            .ticket-divider {
                height: 1px;
                margin: 1.05rem 0 0.9rem;
                background: rgba(27, 27, 27, 0.14);
            }

            .ticket-foot {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                gap: 1rem;
            }

            .ticket-foot .verify {
                font-size: 0.62rem;
                font-weight: 650;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: var(--ink);
            }

            .ticket-foot .verify i {
                font-style: normal;
                display: inline-block;
                width: 0.5rem;
                height: 0.5rem;
                margin-right: 0.45rem;
                border: 1.5px solid var(--ink);
                border-radius: 1px;
                vertical-align: 1px;
            }

            .barcode {
                display: flex;
                align-items: center;
                height: 1.05rem;
                gap: 0.14rem;
            }

            .barcode i {
                display: block;
                width: 1px;
                background: var(--ink);
                opacity: 0.8;
            }

            .barcode i:nth-child(4n + 1) { width: 2px; }
            .barcode i:nth-child(7n) { width: 3px; height: 0.85rem; }
            .barcode i:nth-child(10n + 3) { height: 0.7rem; }

            .qr-wrap {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.55rem;
                flex-shrink: 0;
            }

            .qr-wrap svg {
                display: block;
                width: clamp(5.4rem, 7.5vw, 7rem);
                height: auto;
                fill: var(--ink);
                shape-rendering: crispEdges;
            }

            .qr-wrap .qr-label {
                font-size: 0.58rem;
                font-weight: 650;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                color: var(--muted);
            }

            /* ---------- Gate ledger ---------- */

            .ledger {
                border-top: 1px solid rgba(255, 255, 255, 0.09);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.016), rgba(255, 255, 255, 0));
            }

            .ledger-inner {
                width: min(1180px, 100%);
                margin: 0 auto;
                padding: 2.6rem clamp(1.25rem, 4vw, 3.5rem) 3rem;
            }

            .ledger-head {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 0.5rem 1.5rem;
                margin-bottom: 1.4rem;
            }

            .ledger-head h2 {
                margin: 0;
                font-size: clamp(1.05rem, 2vw, 1.3rem);
                font-weight: 650;
                letter-spacing: 0.01em;
                color: #f1ecdf;
            }

            .ledger-head p {
                margin: 0;
                font-size: 0.72rem;
                font-weight: 600;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                color: var(--amber);
            }

            .ledger-rows {
                display: flex;
                flex-direction: column;
                font-size: 0.85rem;
            }

            .ledger-row {
                display: grid;
                grid-template-columns: 7.5rem minmax(0, 1fr) auto;
                align-items: baseline;
                gap: 1rem;
                padding: 0.7rem 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            }

            .ledger-row:last-child {
                border-bottom: none;
            }

            .ledger-row .when {
                font-size: 0.68rem;
                font-weight: 600;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: var(--fog);
            }

            .ledger-row .what {
                font-weight: 500;
                color: #dcd6c6;
            }

            .ledger-row .what b {
                font-weight: 650;
                color: #f4f0e5;
            }

            .ledger-row .verdict {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                font-size: 0.66rem;
                font-weight: 650;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                color: var(--amber);
            }

            .ledger-row .verdict::before {
                content: "";
                width: 0.45rem;
                height: 0.45rem;
                border: 1px solid var(--amber);
            }

            /* ---------- Footer ---------- */

            .footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 0.75rem;
                padding: 1.4rem clamp(1.25rem, 4vw, 3.5rem);
                border-top: 1px solid rgba(255, 255, 255, 0.07);
                font-size: 0.78rem;
                color: var(--fog);
            }

            .footer .foot-links {
                display: flex;
                align-items: center;
                gap: 1.25rem;
            }

            .footer a {
                color: #cfc8b6;
                text-decoration: none;
            }

            .footer a:hover {
                color: #ffffff;
            }

            /* ---------- Motion ---------- */

            @keyframes rise {
                from { opacity: 0; transform: translateY(14px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @keyframes ticket-in {
                from {
                    opacity: 0;
                    transform: translateY(22px) rotate(3.5deg) scale(0.97);
                    filter: blur(7px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) rotate(0) scale(1);
                    filter: blur(0);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .hero-copy, .ticket-stage { animation: none; }
                .ticket, .ticket:hover { transform: none; transition: none; }
            }

            /* ---------- Responsive ---------- */

            @media (max-width: 880px) {
                .hero {
                    grid-template-columns: 1fr;
                    gap: 2.5rem;
                    text-align: left;
                }

                .ticket-stage {
                    order: -1;
                }

                .ticket {
                    transform: none;
                }

                .ticket:hover {
                    transform: translateY(-3px);
                }

                .eyebrow::before {
                    display: none;
                }
            }

            @media (max-width: 480px) {
                .ticket {
                    --perf: 104px;
                }

                .ticket-body {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 1.1rem;
                }

                .qr-wrap {
                    flex-direction: row;
                    justify-content: flex-start;
                    gap: 0.85rem;
                }

                .qr-wrap svg {
                    width: 4.6rem;
                }

                .proof-inner {
                    grid-template-columns: 1fr;
                    gap: 1.6rem;
                }

                .ledger-row {
                    grid-template-columns: 6.5rem 1fr;
                }

                .ledger-row .verdict {
                    grid-column: 2;
                    justify-self: start;
                }

                .brand small {
                    display: none;
                }

                .hero-actions {
                    flex-direction: column;
                    align-items: stretch;
                }

                .btn-primary, .btn-secondary {
                    justify-content: center;
                }
            }
        </style>
    </head>
    <body>
        <div class="stage" aria-hidden="true"></div>

        <header class="masthead">
            <a class="brand" href="{{ url('/') }}" aria-label="TicketEezy — home">
                <x-app-logo-icon />
                <strong>TicketEezy</strong>
                <small>Event access</small>
            </a>

            <nav class="nav-actions" aria-label="Primary">
                @auth
                    <a class="nav-link" href="{{ route('filament.admin.pages.dashboard') }}">Dashboard</a>
                    <a class="nav-cta" href="{{ route('filament.admin.pages.dashboard') }}">Open the panel</a>
                @else
                    @if (Route::has('login'))
                        <a class="nav-link" href="{{ route('scan.dashboard') }}">Scanner</a>
                        <a class="nav-cta" href="{{ route('login') }}">Sign in</a>
                    @endif
                @endauth
            </nav>
        </header>

        <main class="hero">
            <div class="hero-copy">
                <p class="eyebrow">Server-verified tickets</p>
                <h1>Admit one.<br>Admit <em>once</em>.</h1>
                <p class="lede">
                    TicketEezy issues server-verified tickets and checks them in at the gate
                    with a single scan. Fakes are rejected, a used ticket stays used, and the
                    line keeps moving.
                </p>
                <div class="hero-actions">
                    @auth
                        <a class="btn-primary" href="{{ route('filament.admin.pages.dashboard') }}">
                            Open the panel
                            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <a class="btn-secondary" href="{{ route('scan.dashboard') }}">
                            Scan a ticket
                            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a class="btn-primary" href="{{ route('login') }}">
                                Sign in
                                <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <a class="btn-secondary" href="{{ route('scan.dashboard') }}">
                                Open the scanner
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            @php
                $qrSize = 25;
                $g = array_fill(0, $qrSize, array_fill(0, $qrSize, false));

                foreach ([[0, 0], [0, 18], [18, 0]] as [$fx, $fy]) {
                    for ($i = $fx; $i < $fx + 7; $i++) {
                        for ($j = $fy; $j < $fy + 7; $j++) {
                            $ix = $i - $fx;
                            $iy = $j - $fy;
                            $g[$j][$i] = $ix === 0 || $ix === 6 || $iy === 0 || $iy === 6
                                || ($ix >= 2 && $ix <= 4 && $iy >= 2 && $iy <= 4);
                        }
                    }
                }

                for ($i = 0; $i < $qrSize; $i++) {
                    $g[6][$i] = $i % 2 === 0;
                    $g[$i][6] = $i % 2 === 0;
                }

                mt_srand(7);
                for ($i = 0; $i < $qrSize; $i++) {
                    for ($j = 0; $j < $qrSize; $j++) {
                        if (($i < 9 && $j < 9) || ($i < 9 && $j > 15) || ($i > 15 && $j < 9)) continue;
                        if ($i === 6 || $j === 6) continue;
                        $g[$j][$i] = mt_rand(0, 3) >= 2;
                    }
                }
            @endphp

            <div class="ticket-stage">
                <article class="ticket" aria-label="Example TicketEezy ticket">
                    <div class="stub">
                        <x-app-logo-icon class="stub-icon" />
                        <p class="stub-admit">Admit one</p>
                        <div>
                            <p class="stub-label">TicketEezy</p>
                            <p class="stub-serial">SER&nbsp;00-4821</p>
                        </div>
                    </div>

                    <div class="ticket-body">
                        <div class="ticket-info">
                            <h2 class="ticket-event">Grand Opening</h2>
                            <p class="ticket-meta">
                                <span>Main Hall</span>
                                <span>Gate 1</span>
                                <span>Doors 19:00</span>
                            </p>
                            <p class="ticket-reg">
                                <span>Order <b>000-4821</b></span>
                                <span>Batch <b>B-07</b></span>
                            </p>

                            <div class="ticket-divider" aria-hidden="true"></div>

                            <div class="ticket-foot">
                                <span class="verify"><i aria-hidden="true"></i>One use</span>
                                <span class="barcode" aria-hidden="true">
                                    <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                                    <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                                    <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                                </span>
                            </div>
                        </div>

                        <div class="qr-wrap">
                            <svg viewBox="0 0 {{ $qrSize }} {{ $qrSize }}" role="img" aria-label="Example QR code">
                                @foreach ($g as $y => $row)
                                    @foreach ($row as $x => $on)
                                        @if ($on)
                                            <rect x="{{ $x }}" y="{{ $y }}" width="1.06" height="1.06"/>
                                        @endif
                                    @endforeach
                                @endforeach
                            </svg>
                            <span class="qr-label">Scan at gate</span>
                        </div>
                    </div>
                </article>
            </div>
        </main>

        <section class="ledger" aria-label="How it works">
            <div class="ledger-inner">
                <div class="ledger-head">
                    <h2>One ticket, one admit — server-verified at the gate.</h2>
                    <p>The scan log</p>
                </div>
                <div class="ledger-rows">
                    <div class="ledger-row">
                        <span class="when">14:02</span>
                        <span class="what"><b>Issued</b> — batch B-07, serial 00-4821, token minted by the server.</span>
                        <span class="verdict">Ok</span>
                    </div>
                    <div class="ledger-row">
                        <span class="when">18:47</span>
                        <span class="what"><b>Verified</b> — holder opened the verify link; the server returned genuine.</span>
                        <span class="verdict">Ok</span>
                    </div>
                    <div class="ledger-row">
                        <span class="when">19:00</span>
                        <span class="what"><b>Checked in</b> — one scan at the door; the ticket is now used, once.</span>
                        <span class="verdict">Admitted</span>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer">
            <span>&copy; {{ date('Y') }} TicketEezy — server-verified single-use tickets.</span>
            <nav class="foot-links" aria-label="Footer">
                <a href="{{ url('/admin') }}">Admin panel</a>
                <a href="{{ route('scan.dashboard') }}">Scanner</a>
                <a href="{{ url('/') }}">Home</a>
            </nav>
        </footer>
    </body>
</html>
