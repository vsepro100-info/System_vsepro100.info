# Состояние системы

## Текущее состояние
- Продакшен активен.
- ref/CRM работают.
- LIVE вебинар существует.
- AUTO вебинар планируется.
- Ingest entry: Web Form → core_ingest_event.
- Scenario: New web_form lead → Telegram notify.
- Scenario: core_lead_created → scenario_start (welcome) для web_form.
- Scenario: scenario_start(welcome) → Telegram welcome message.
- Scenario: scenario_start(welcome) → (delay 10 min) → follow-up Telegram.
- Service: client_webinar_attendance_classified → Telegram уведомление партнеру.
- Service: client_webinar_attendance_classified → post_webinar_route (attended | not_attended).
- Service: post_webinar_route → Telegram follow-up кандидату (attended | not_attended).
- Service: post_webinar_route (not_attended) → Telegram предложение записи вебинара.
- UI: Webinar Room UI (MVP) реализован.

## Lead basic metrics (MVP)
- lead_source: источник лида из payload.source (если доступен).
- first_touch_at: unix timestamp первого касания (time()).
- lead_created_at: unix timestamp создания лида (time()).
- lead_created_via: канал создания лида, фиксированное значение core_ingest.

## Lead entity (canonical)
- lead_entry: internal-only CPT, создаётся на `core_ingest_event` и используется как канонический lead_id.

## События клиентского вебинара (MVP)
- Эмиссия событий сервиса client-webinar-tracker-v2 только через do_action.
- Сервисный классификатор посещения: client-webinar-attendance-service (observer-only).
- События (реально реализовано): client_webinar_completed, client_webinar_entered, client_webinar_attendance_classified.
- Минимальный контекст: lead_id (если доступен), webinar_id (string), timestamp.

## События клиентского вебинара (запланировано / не реализовано)
- client_webinar_registered.
- client_webinar_left.
- client_webinar_form_submitted.

## Сценарии клиентского вебинара (запланировано / не реализовано)
Лид зарегистрирован
 → Сценарии клиентского вебинара
   → Напоминания перед вебинаром (условно)
   → Напоминания во время вебинара (условно)
   → Маршрутизация после вебинара
     → посетил
     → не посетил

## Документация / архитектура / состояние
- WEBINAR_ARCHITECTURE.md — статус: активен/утвержден.
- CLIENT_WEBINAR_ACTION_CONSUMERS.md — статус: требует актуализации (вебинарные события частично запланированы).
- CLIENT_WEBINAR_EVENTS.md — статус: требует актуализации (канонические события частично запланированы).
- CLIENT_WEBINAR_EVENT_EMITTERS.md — статус: требует актуализации (реально реализованы client_webinar_completed, client_webinar_entered).
- CLIENT_WEBINAR_SCENARIOS.md — статус: требует актуализации (слой сценариев не реализован).
- CLIENT_WEBINAR_SCENARIO_SERVICE.md — статус: требует актуализации (слой сценариев не реализован).
