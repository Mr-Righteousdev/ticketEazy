# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **Event organizers** (primary): create events and ticket types, generate batches of PDF tickets, download them, and monitor issue/scan status from the Filament admin panel at `/admin`.
- **Gate operators** (primary at venue): scan a guest's QR code at `/scan` to verify authenticity and check the ticket in; may fall back to entering the token manually.
- **Ticket holders** (peripheral): open the verify URL baked into their ticket's QR to confirm it is genuine and (once scanned) used.

## Product Purpose

TicketEezy issues single-use, server-verified tickets for events and lets a venue gate check them in. Success is a guest with a scannable ticket and an operator who can tell "genuine, unused, admit" from "already used / expired / fake" in under a second, with an audit trail of every scan.

## Positioning

Verification is cryptographic, not visual: each ticket carries a unique `uuid.hmac` token (base64url), the QR encodes the full verify URL, and check-in is one atomic transaction that marks a ticket used exactly once. A batch of PDFs is a side effect of issuing real, server-known tokens — not a picture. The product guarantees single-use admission; a gate can trust the screen over the paper.

## Operating Context

- Admin work happens on desktop in the Filament panel: create event → ticket type → generate batch → download zip of PDFs → hand out.
- Gate work happens on a phone or tablet with a camera at the venue entrance; the scanner is bundled (html5-qrcode) so it works even with poor venue connectivity, verifying against the server.
- QR encodes a URL to `ticket.verify`, so any holder can check authenticity themselves before arriving.
- Every scan writes a row to `scan_logs` (valid, already-used, expired, failed).

## Capabilities and Constraints

- Laravel app with Filament v4 admin panel at `/admin`, Livewire, Flux, and a sidebar layout; Filament primary color is **Amber**.
- Roles via spatie: `admin`, `operator`. `/scan` requires auth + verified + `role:operator`.
- PDF generation runs on the queue worker (`QUEUE_CONNECTION=database`); there is no scheduler/cron.
- QR generated with chillerlan/php-qrcode 5.0.5 (must be a fresh instance per ticket — the renderer accumulates segments).
- QR position picker (PDF layout) exists; its preview pngs are served via `/previews/{hash}.png`.
- Storage: `sqlite` in dev/prod; ticket PDFs + zips under `storage/app/local/tickets/...`.
- Ticket statuses: `generated`, `sent`, `used`, `expired`, `failed`. Check-in results: ok / already_used / expired / invalid.
- Deployment: PHP 8.4 FPM, domain `ticketeeazy.novaspand.com`, APP_URL is baked into QRs (must be the real domain before generating real batches).
- Ticket count in the current DB is small (~26 real tickets, 1 event "Grand Opening", 1 ticket type "ordinary"); it is a real, working system, not a demo.

## Brand Commitments

- Product name: **TicketEezy** (user-confirmed); domain `ticketeeazy.novaspand.com`.
- Parent company: **Novaspand** (inferred from domain + event naming; treat as soft, not a legal claim).
- The shipped app-logo components still read "Laravel Starter Kit" — this is obsolete and must be replaced with TicketEezy.
- Font in use: **Instrument Sans** (via `@fonts`); Filament admin is dark-sidebar with Amber primary.

## Evidence on Hand

- Real working data: 26 tickets with real tokens/QRs, 1 event, 1 ticket type, 3 users (roles admin/operator).
- Public `ticket.verify` page renders a green "valid" state for unused tickets; the admin panel shows statuses and batch downloads.
- No marketing copy, testimonials, pricing, or press exist. The welcome page currently is the framework's default starter page — there is no incumbent product identity to preserve.

## Product Principles

- **Server truth over paper**: the ticket is its token; the PDF is only the carrier. Never render a claim the server can't verify.
- **One admit, once**: single-use is enforced atomically; no double-check-in, ever.
- **Trust the screen**: the gate outcome (valid / used / expired / fake) is legible at a glance and logged for every scan.
- **Operators move fast**: scan → verdict → next guest; the interface must not interrupt the queue.
- **Real-first**: this is a live system with real data; anything that looks like proof must actually hold.

## Accessibility & Inclusion

No product-specific requirement has been established. Standard web accessibility applies (contrast, keyboard focus, responsive).
