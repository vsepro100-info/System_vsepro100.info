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

## Границы системы

WHIEDA Duplication System является замкнутым контуром дупликации
и имеет строго определённые границы.

В данный контур НЕ входят:
— покупка продукта;
— регистрация в системе WHIEDA;
— обучение партнёра;
— заработок и маркетинг-план;
— онбординг нового партнёра.

Все перечисленные элементы находятся
ВНЕ границ данного контура
и реализуются в других системах.

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

### Инвариантное ядро системы (S2–S3)

Состояния S2 и S3 являются инвариантным ядром WHIEDA Duplication System.

В рамках состояний S2–S3 запрещены:
— персонализация контента;
— дожим и убеждение пользователя;
— призывы к срочности;
— изменение логики под конкретного пользователя.

Любые изменения логики состояний S2–S3
допускаются ТОЛЬКО через новую версию PRODUCT CANON.

## Критерий успеха контура дупликации

WHIEDA Duplication System не занимается продажей,
убеждением или принуждением пользователя к действию.

Критерием успеха данного контура является состояние,
в котором пользователь самостоятельно и осознанно
инициирует следующий шаг.

Все действия после этой точки
находятся за пределами данного контура.

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

Build Order является обязательным и каноническим.

Обратные зависимости между модулями запрещены.
Нарушение установленного Build Order
считается нарушением архитектуры системы.

## 9. Change Rule
- Any logic change requires new CANON version.
