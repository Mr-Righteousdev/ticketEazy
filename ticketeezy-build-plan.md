# TicketEezy — Build Plan
**Stack:** Laravel 11 · Livewire 3 · Filament 3 (standalone components + full panel for admin) · Alpine.js · Tailwind CSS  
**Novaspand Internal Document**

---

## Architecture Overview

| Zone | URL | Who | Tech |
|---|---|---|---|
| Admin Panel | `/admin` | Lillian only | Filament based component pages |
| Scanner App | `/scan` | Gate operators | Livewire standalone |
| Login Router | `/` | Both | Simple Livewire auth |

Gate operators log in at `/` and are redirected to `/scan`. Lillian logs in and is redirected to `/admin`. Role-based middleware handles the separation.

---

## Database Schema

### `users`
```
id, name, email, password, role (admin|operator), created_at, updated_at
```

### `events`
```
id, name, date, time, venue, capacity, status (draft|active|ended), created_at, updated_at
```

### `ticket_types`
```
id, event_id, name, price (nullable), quantity, template_path, is_discount (bool),
discount_label (nullable), parent_type_id (nullable → FK to ticket_types), 
created_at, updated_at
```
> Discount tickets are just ticket types with `is_discount = true` and a `parent_type_id` pointing to the type they belong to. They carry their own template.

### `tickets`
```
id, ticket_type_id, token (unique, string), status (generated|used|invalid),
used_at (nullable), scanned_by (nullable → FK users), created_at, updated_at
```
> `token` is the HMAC-SHA256 signed string encoded in the QR. Never sequential, never guessable.

### `scan_logs`
```
id, ticket_id, scanned_by (FK users), scanned_at, result (valid|already_used|invalid),
ip_address, user_agent
```

**5 tables. That's it.**

---

## QR Token Design

Each token is generated as:

```
token = base64url( ticket_uuid + '.' + HMAC-SHA256(ticket_uuid + event_id, APP_KEY) )
```

On scan:
1. Decode the token
2. Recompute the HMAC and compare — if it doesn't match, `invalid`
3. If valid, attempt atomic DB update:
   ```sql
   UPDATE tickets SET status='used', used_at=NOW(), scanned_by=?
   WHERE id=? AND status='generated'
   ```
4. If 0 rows affected → `already_used`
5. If 1 row affected → `valid` → write to `scan_logs`

This handles concurrent scans at multiple gates with no race condition.

---

## PDF Stamping Flow

**Package:** `setasign/fpdi` + `chillerlan/php-qrcode`

Flow:
1. Generate QR code as PNG in memory
2. Load Lillian's PDF template using FPDI
3. Place QR PNG at configured X, Y coordinates on the template page
4. Save output as a new PDF named `{ticket_type}-{token_short}.pdf`
5. Repeat for N tickets, collect all PDFs into a ZIP

QR position (X, Y, size) is configured per ticket type in the admin — a simple numeric input, not a drag UI (keep it simple for now, she can test and adjust).

**Generation is queued** — generating 500 PDFs in one request would timeout. Each batch goes through a Laravel job. Lillian sees a progress indicator and gets a download link when done.

---

## Roles & Middleware

```
admin     → access to /admin only
operator  → access to /scan only
```

Middleware `EnsureRole` applied to both route groups. An operator hitting `/admin` gets bounced back. An admin can optionally access `/scan` for testing.

---

## Sprint Plan

### Sprint 1 — Foundation
- Laravel install with Livewire starter kit
- Filament components installed 
- Auth system with roles (admin / operator)
- Login router — redirect by role
- Migrations for all 5 tables
- Seeders — 1 admin user, 2 operator users, 1 test event

---

### Sprint 2 — Event & Ticket Type Management (Admin )
**Livewire Components with Filament Components (tables and forms):**

- `EventManagement` — create, edit, list events. Fields: name, date, time, venue, capacity, status
- `TicketTypeManagement — create, edit, list ticket types per event. Fields: name, price, quantity, template upload, is_discount toggle, discount_label, parent_type selector
- `UserManagement — manage gate operator accounts (create, disable)

---

### Sprint 3 — QR Generation & PDF Stamping
- Install `setasign/fpdi` and `chillerlan/php-qrcode`
- `GenerateTicketsJob` — accepts ticket_type_id + quantity, generates tokens, stamps PDFs, zips output
- Filament action on TicketTypeResource — "Generate Tickets" button → triggers job → shows progress
- Store generated PDFs in `storage/tickets/{event_id}/{ticket_type_id}/`
- ZIP download link shown when job completes
- QR position configured per ticket type (X, Y, size fields in mm)

---

### Sprint 4 — Scanner Web App
**Livewire components at `/scan`:**
- `ScannerLogin` — simple email/password, role-checked, redirects to scanner
- `ScannerApp` — main scanner screen
  - Uses `jsQR` or `html5-qrcode` JS library via Alpine.js for camera access
  - On decode → hits `POST /api/scan` with token
  - Response drives full-screen UI:
    - GREEN: "VALID — {ticket_type_name}"
    - RED: "ALREADY USED" or "INVALID TICKET"
  - Auto-resets after 3 seconds, ready for next scan
- `ScannerHeader` — shows operator name, gate, logout button

**API endpoint:** `POST /api/scan`
- Middleware: `auth`, `role:operator`
- Validates token signature
- Atomic DB update
- Writes scan_log
- Returns JSON: `{ result, ticket_type, message }`

---

### Sprint 5 — Dashboard & Reports (Admin Panel)
**Filament Dashboard widgets:**
- Total tickets generated (per type)
- Total scanned / entered
- Remaining (not yet scanned)
- Live scan feed (last 20 scans, auto-refreshes)
- Capacity fill bar per event

**Reports page:**
- Full scan log table — ticket ID, type, scanned by, gate, time, result
- Filter by: ticket type, operator, result, time range
- Export to Excel (Laravel Excel) and PDF
- Summary report — total per type, peak entry time, operator activity breakdown

---

### Sprint 6 — Polish & Pre-launch
- Mobile responsiveness audit on scanner app
- Test concurrent scans (two browsers, same ticket, same second)
- Test forged QR (manually crafted token)
- Test with actual Lillian PDF template
- Operator onboarding — simple one-page guide (how to open scanner, what the screens mean)
- Final ZIP download UX — progress bar, error handling if job fails

---

## File Storage Structure

```
storage/app/
  templates/
    {ticket_type_id}/template.pdf        ← uploaded by Lillian
  tickets/
    {event_id}/
      {ticket_type_id}/
        ticket-{short_token}.pdf         ← generated tickets
      downloads/
        {ticket_type_id}-batch-{ts}.zip  ← zip for download
```

---

## Key Packages

| Package | Purpose |
|---|---|
| `filament/filament` | Standalone form/table components |
| `setasign/fpdi` | Load and manipulate existing PDFs |
| `chillerlan/php-qrcode` | Generate QR code images |
| `maatwebsite/excel` | Excel export for reports |
| `barryvdh/laravel-dompdf` | PDF export for reports |
| `html5-qrcode` (JS) | Camera QR scanning in browser |

---

## Notes

- No payment integration in this version
- No buyer details stored anywhere — tickets are anonymous QR codes
- The system does not sell tickets — it only generates, tracks, and validates them
- Phase 2 candidates: online request page, mobile money payment, offline scanner mode, SMS delivery
