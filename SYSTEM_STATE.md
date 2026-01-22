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

## Lead basic metrics (MVP)
- lead_source: источник лида из payload.source (если доступен).
- first_touch_at: unix timestamp первого касания (time()).
- lead_created_at: unix timestamp создания лида (time()).
- lead_created_via: канал создания лида, фиксированное значение core_ingest.

## Lead entity (canonical)
- lead_entry: internal-only CPT, создаётся на `core_ingest_event` и используется как канонический lead_id.

## События клиентского вебинара (MVP)
- Эмиссия событий сервиса client-webinar-tracker-v2 только через do_action.
- События: client_webinar_entered, client_webinar_completed, client_webinar_form_submitted.
- Минимальный контекст: lead_id (если доступен), webinar_id (string), timestamp.

## Сценарии клиентского вебинара (концептуально / ещё не реализовано)
Лид зарегистрирован
 → Сценарии клиентского вебинара
   → Напоминания перед вебинаром (условно)
   → Напоминания во время вебинара (условно)
   → Маршрутизация после вебинара
     → посетил
     → не посетил
