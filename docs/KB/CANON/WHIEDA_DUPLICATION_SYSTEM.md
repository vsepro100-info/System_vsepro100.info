Status: CANON
Owner: Architect
Last updated: 2026-01-28

# WHIEDA Duplication System — CANON v1.0

## 1. Purpose of WHIEDA Duplication System
- One closed contour.
- From end of presentation to initiated action (S5).

## 2. System Boundaries
- Explicitly excludes purchase.
- Explicitly excludes onboarding.
- Explicitly excludes education.
- Explicitly excludes earnings.

## 3. Core Principles (Non-Negotiable)
- Trust over conversion.
- System stronger than partner.
- No personalization in S2–S3.
- No pressure, no urgency.

## 4. User States
- S1 — End of presentation: presentation completed and user enters system contour.
- S2 — Orientation: user sees canonical next steps without personalization.
- S3 — Confirmation: user validates understanding of the path without personalization.
- S4 — Decision: user confirms intent to act within the contour.
- S5 — Initiated action: user triggers the first action in the system.

## 5. Architectural Modules
- M1 — Entry capture: records the transition from presentation to system contour.
- M2 — Orientation flow: provides canonical steps for S2.
- M3 — Confirmation flow: provides canonical steps for S3.
- M4 — Decision gate: captures the S4 decision.
- M5 — Action trigger: initiates S5 action.
- M6 — State tracking: stores and exposes S1–S5 status.
- M7 — Audit log: immutable record of transitions and actions.

## 6. Entities
- E1 — User mapped to M6.
- E2 — Presentation end event mapped to M1.
- E3 — Orientation step mapped to M2.
- E4 — Confirmation marker mapped to M3.
- E5 — Decision record mapped to M4.
- E6 — Action trigger mapped to M5.
- E7 — Transition log mapped to M7.

## 7. Plugin Grouping
- Plugin A: M1 and M6.
- Plugin B: M2.
- Plugin C: M3.
- Plugin D: M4 and M5.
- Plugin E: M7.

## 8. Build Order
- Canonical order: M1 → M6 → M2 → M3 → M4 → M5 → M7.
- Rule: no backward dependency.

## 9. Change Rule
- Any logic change requires new CANON version.
