---
version: 1
slug: "resources-views-welcome-blade-php"
primary_target: "resources/views/welcome.blade.php"
related_targets: ["resources/views/components/app-logo.blade.php"]
---

# Surface Brief: Welcome Page

## Scope & Mode

**Primary target:** `resources/views/welcome.blade.php` (route `home`, `GET /`)
**Mode:** Persuade — the visitor decides and acts (signs in, or goes to admin/scan).
**Related:** `resources/views/components/app-logo.blade.php` (brand mark), app `name` config.

## Audience, Job, Action

- **Audience:** event organizers and gate operators arriving at the product for the first time; the public root of a real, working system.
- **Job:** understand what TicketEezy is and get to the action they came for.
- **Action:** sign in (primary). Secondary: enter the admin panel; on a scanner device, the operator route.
- **Proof/content:** real system facts — server-verified single-use tickets, QR check-in, scan log. No invented testimonials or metrics.

## Direction & Signature

- **Chosen world:** "The Doorman's Ticket" — dark ticket-stub premium (user-pinned; no roll).
- **Signature moment:** the hero ticket stub itself — a perforated paper ticket carrying the event/brand and a live QR module, the one lit object on a near-black venue stage. Sign-in sits in the top bar and/or on the stub.
- **Must demonstrate first viewport:** that TicketEezy issues real, verifiable tickets. The stub is the mechanism dramatized, not a stock hero.

## Constraints

- Self-contained Blade file; can use `@fonts` (Instrument Sans already loaded). Do not depend on the admin panel's Tailwind build — the current file uses an inlined style block.
- Preserve: `@auth`/`Route::has('login')` behavior (Dashboard vs Log in), `ticket.verify` link availability, dark-mode friendliness (the app has dark mode), accessibility, `home` route.
- Links: `route('login')`, `route('dashboard')` when authed, `route('admin')`/panel path `/admin`, `/scan` for operators.
- Brand: TicketEezy (replace any "Laravel Starter Kit" wording on this surface).

## Resolved

- Exact tokens settled and carbonized into DESIGN.md (stage `#0b0c0e`, paper `#f1ebdd`→`#ece4d1`, amber `#f4a825`).
- Below-fold element: a gate ledger (three scan-log lines in the stub's serial/verdict grammar), chosen deliberately over a three-card feature row — it demonstrates the mechanism through the world's own device.
