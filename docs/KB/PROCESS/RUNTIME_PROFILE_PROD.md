Status: PROCESS
Owner: Architect
Last updated: 2026-01-28

# Runtime Profile — PROD (Webinars MVP v1++)

## Назначение
Зафиксировать runtime‑профиль **PROD** для Webinars MVP v1++ перед деплоем. Профиль описывает, какие модули должны быть включены в production‑контуре, и какие явно отключены.

## Источники истины
- [CANON/MODULE_BOUNDARIES](../CANON/MODULE_BOUNDARIES.md)
- [SPEC/MVP_RUNTIME_BOUNDARIES](../SPEC/MVP_RUNTIME_BOUNDARIES.md)
- [PROCESS/PRE_RELEASE_CHECKLIST](PRE_RELEASE_CHECKLIST.md)

## Определение Runtime Profile: PROD (Webinars)
- **PROD (Webinars)** = LIVE‑контур Webinars MVP v1++.
- **AUTO‑контур выключен** (AutoWebinar Delivery и `autowebinar_*` события не исполняются).
- В PROD допускаются **только модули со статусом required/optional** ниже.
- **Disabled** модули не должны быть активированы в prod‑окружении.

## Таблица модулей Webinars (PROD)

| Module name | Role (core / ui / integration / analytics / dev) | Status in PROD (required / optional / disabled) | Notes |
| --- | --- | --- | --- |
| Core webinar_event | core | required | Каноническая сущность вебинара и контроль статуса через `core_webinar_set_status`. |
| Webinar Entry UI (ui-webinar-entry) | ui | disabled | Не участвует в MVP runtime. |
| Webinar Room UI (ui-webinar-room) | ui | required | Комната вебинара (render-only). |
| Webinar Public UI (ui-webinar-public) | ui | required | Публичная страница и админ‑форма расписания (render-only). |
| Webinar Chat (webinar-chat) | ui | disabled | Чат не входит в MVP runtime. |
| Client Webinar Tracker v2 (client-webinar-tracker-v2) | analytics | required | Фиксация `client_webinar_entered` и `client_webinar_completed`. |
| Client Webinar Event Emitter (client-webinar-event-emitter) | analytics | required | Нормализация `client_webinar_completed` → `webinar_completed`. |
| Client Webinar Scenario Service (client-webinar-scenario-service) | integration | disabled | Сценарии не входят в MVP runtime. |
| Client Webinar Attendance Service (client-webinar-attendance-service) | analytics | required | Классификация посещения → `client_webinar_attendance_classified`. |
| Client Webinar Attendance Telegram (client-webinar-attendance-telegram) | integration | required | Уведомление партнёра по `client_webinar_attendance_classified`. |
| Client Webinar Action Consumer (client-webinar-action-consumer) | integration | disabled | Реакции на сценарные события не входят в MVP runtime. |
| Post Webinar Routing Service (post-webinar-routing-service) | integration | required | Маршрутизация `post_webinar_route`. |
| Post Webinar Follow-up Telegram (post-webinar-followup-telegram) | integration | required | Follow-up кандидату по `post_webinar_route`. |
| Post Webinar Recording Follow-up Telegram (post-webinar-recording-followup-telegram) | integration | required | Отправка записи при `post_webinar_route (not_attended)`. |
| Client Webinar Control Integration (client-webinar-control-integration) | integration | required | Старт/стоп вебинара через Core hook. |
| AutoWebinar Delivery (autowebinar-delivery) | integration | disabled | AUTO‑контур отключён в PROD MVP v1++. |

## Отдельные отметки

### Dev-only
- Нет модулей dev-only в PROD профиле.

### Analytics-only
- Client Webinar Tracker v2
- Client Webinar Event Emitter
- Client Webinar Attendance Service

## Правило применения
- Любое включение **optional/disabled** модулей требует обновления этого профиля перед релизом.
