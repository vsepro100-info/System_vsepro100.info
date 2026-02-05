Status: PROCESS
Owner: Architect
Last updated: 2026-01-28

# Pre-Release Checklist — Webinars MVP v1++

## Release Tag
- **Webinars MVP v1++**

## Источник истины
- Только факты из KB и текущего кода. Если факта нет в KB — его не существует в системе.

## Включённые модули (MVP runtime, LIVE)
- Core webinar_event (каноническая сущность статуса/расписания; старт/стоп через `core_webinar_set_status`).
- Webinar Room UI (render-only, данные из Core).
- Webinar Public UI (render-only, данные из Core).
- Client Webinar Control Integration (start/stop через Core hook).
- Client Webinar Tracker v2 (`client_webinar_entered`, `client_webinar_completed`).
- Client Webinar Event Emitter (нормализация `client_webinar_completed` → `webinar_completed`).
- Client Webinar Attendance Service (`client_webinar_attendance_classified`).
- Client Webinar Attendance Telegram (уведомление партнёра по `client_webinar_attendance_classified`).
- Post Webinar Routing Service (`client_webinar_attendance_classified` → `post_webinar_route`).
- Post Webinar Follow-up Telegram (follow-up кандидату по `post_webinar_route`).
- Post Webinar Recording Follow-up Telegram (отправка записи при `post_webinar_route (not_attended)`).

## Включённые UI-страницы
- `/webinars/` — публичное расписание.
- `/account/webinar_room/` — комната вебинара.
- `/admin_webinar/` — админ/спикер интерфейс.

## Отключённые/отложенные части (out-of-scope)
- AUTO Webinars / AutoWebinar Delivery и все `autowebinar_*` события.
- `client_webinar_registered`, `client_webinar_left`, `client_webinar_form_submitted`.
- REST write-операции для изменения статуса/CTA.
- Отдельная роль "moderator" (доступы только через capabilities).
- Записи вебинаров для партнёров (помечено как «позже»).
- Любые модули Traffic & Training.

## Зависимости (platform & plugins)
- Платформа: WordPress (Webinars работают через hooks и WordPress плагины).
- PHP: версия не зафиксирована в KB (требуется верификация перед деплоем).
- Плагины Webinars (канонический список модулей и entrypoints):
  - ui-webinar-entry
  - ui-webinar-room
  - ui-webinar-public
  - webinar-chat
  - client-webinar-tracker-v2
  - client-webinar-event-emitter
  - client-webinar-scenario-service
  - client-webinar-attendance-service
  - client-webinar-attendance-telegram
  - client-webinar-action-consumer
  - post-webinar-routing-service
  - post-webinar-followup-telegram
  - post-webinar-recording-followup-telegram
  - client-webinar-control-integration
  - autowebinar-delivery (в системе присутствует, но вне MVP runtime)

## Контроль готовности к деплою (MVP v1++)
- Webinars runtime ограничен LIVE контуром (AUTO выключен).
- UI страниц работает в режиме render-only; изменения статуса — только через Core hook.
- Канонические события, реально работающие в системе: `client_webinar_entered`, `client_webinar_completed`, `client_webinar_attendance_classified`, `post_webinar_route`.
- Telegram follow-up активен только в рамках пост-вебинарного маршрута.
- Роли и доступы соответствуют канону (guest / candidate / partner / admin/speaker).
