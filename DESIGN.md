<!-- SEED: established with the user before implementation; re-run /impeccable document once there's code to capture the actual tokens and components. -->
---
name: TicketEezy
description: Ticket generation, verification, and gate check-in for events. Dark ticket-stub premium identity.
colors:
  stage: "#0b0c0e"
  stage-raise: "#121317"
  paper: "#f1ebdd"
  paper-mid: "#ece4d1"
  ink: "#1b1b1b"
  muted: "#6e6757"
  amber: "#f4a825"
  fog: "#a29b88"
  stage-text: "#e9e4d6"
  lede-text: "#cfc8b6"
  paper-foreground: "#f4f0e5"
typography:
  body:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, -apple-system, sans-serif"
---

# Design System: TicketEezy

## Overview

**Creative North Star: "The Doorman's Ticket"**

TicketEezy lives in the moment a guest meets the gate: a printed ticket stub in a dark venue, torn in half, handed to a doorman who knows in one glance whether to step aside. The identity is the physical ticket-stub as a premium object — ink-dark grounds like the venue before doors open, a single warm paper ticket that carries the guest's admission, and an amber accent that reads like the working light of the venue staff. It refuses the generic SaaS hero: there is no gradient headline, no floating dashboard screenshot. The hero is the ticket itself.

**Key Characteristics:**
- Dark venue stage (near-black) with a warm ticket stub as the single object of light.
- Perforation, ticket serial/seat codes, and a live QR module as the signature grammar.
- Amber as the working accent (matches the Filament admin primary).
- Precision micro-labels (tracked uppercase) from the language of a ticket stock.
- One authored motion: the stub tears/fades in, never scattered effects.

## Colors

Dark stage + one warm paper object + amber working accent. Settled on first build of the welcome surface (see token frontmatter).

### Primary
- **Venue Ink** (`#0b0c0e`): the near-black stage and text-on-paper (`#1b1b1b`). Carries ~70% of the surface. Rises to `#121317` for panel surfaces.

### Secondary
- **Ticket Paper** (`#f1ebdd` → `#ece4d1`): the warm off-white of the ticket stub — the one lit object.

### Tertiary
- **Working Amber** (`#f4a825`): CTA, serial marks, ledger verdicts, the check-in accent; matches the Filament admin primary.

### Neutral
- **Muted Paper Text** (`#6e6757`): secondary text on the stub (4.7:1 on paper).
- **Faint Stage Text** (`#a29b88`): secondary text on the dark ground (7.1:1 on stage); foreground `#e9e4d6` and lede `#cfc8b6` at 11.7:1.

## Typography

Instrument Sans (loaded via `@fonts`) is the single family, carrying both the ticket wordmark and dense stub micro-labels.

### Hierarchy
- **Display** (650 weight, `clamp(2.6rem, 5.2vw, 4.4rem)`, `-0.03em` tracking): the hero headline and event name.
- **Body** (1.02–1.14rem, 1.6 line-height, max 34rem measure): supporting copy on the dark stage.
- **Label** (0.58–0.72rem, 600–650 weight, `0.14–0.22em` tracking, uppercase): serial/seat/venue micro-labels and tracked kickers.

## Layout

A single centered composition: one ticket stub as hero on a full-bleed dark stage, with a top bar carrying the sign-in action and the brand. Below the fold, a gate ledger (three scan-log lines) instead of a feature-card row. Responsive: below 880px the hero stacks (ticket on top, copy below); below 480px the ticket body column-stacks, the stub narrows (`--perf: 104px`), and buttons go full-width. One spacing rhythm; more space above a heading than below it.

## Elevation & Depth

Tonal layering on the dark stage: the paper stub is the lit plane, given an amber catch-light along its top edge (`inset 0 1px 0 rgba(244,168,37,.45)`) and a faint grain. Depth under the stub comes from hard offset shadows (`0 44px 90px -28px rgba(0,0,0,.75)` + a soft amber bleed), never blue glow.

## Shapes

Ticket grammar: a horizontal stub with a real perforation — a punched hole-track (`radial-gradient` dots at 12px intervals) and two notches cut through the edge via `mask-image` + `mask-composite: intersect` so the stage shows through, not a solid disc. Rectilinear; radius stays 4px.

## Components

### The Ticket Stub (signature)
- Left stub column: ticket icon, rotated vertical "Admit one" wordmark, brand label + serial (`SER 00-4821`, wide-tracked Instrument Sans — no mono).
- Body: event name (uppercase display), meta line (venue · gate · doors), order/batch register line, 1px divider, foot ("One use" printed mark + CSS barcode), and a pseudo-QR module generated as a 25×25 SVG grid (three finder patterns + timing + seeded modules) labelled "Scan at gate".
- The tear between stub and body is the recurring grammar (hole-track + notched mask).
- No green "valid" signal — paper never self-declares validity; the server does, and it lives in the ledger.

### Buttons
- **Primary (Sign in / Open the panel):** amber `#f4a825`, ink text, 4px radius, `0.85–0.95rem`/650 weight, offset amber shadow, hover lift 2px.
- **Secondary (Scanner / nav):** transparent, faint-foreground text, underline-free, hover to white.

### Gate Ledger
- Three scan-log rows (`when` / `what` / `verdict`) with hairline rules and amber boxed verdicts — "how it works" told through the world's own device.

## Do's and Don'ts

### Do:
- **Do** make the ticket stub the hero object of the first viewport.
- **Do** use amber for the single working action and ledger verdicts.
- **Do** use perforation, notches, and QR-module geometry as the recurring grammar.
- **Do** keep the stub's tear real: hole-track plus punched notches that show the stage through.

### Don't:
- **Don't** use gradient text, glass, or glowing edges on the dark stage — the paper stub is the light source.
- **Don't** fall into the generic hero: no dashboard screenshot, no three-card feature row (the ledger replaced it).
- **Don't** use a second font family (incl. mono as costume) — Instrument Sans carries all roles.
- **Don't** put a green/red validity signal on the paper; validity is a server verdict shown in the ledger.
- **Don't** render a fake scannable QR without a real `ticket.verify` target behind it — a decorative grid is fine if labelled as an example.
