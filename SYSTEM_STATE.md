# Состояние системы

## Текущее состояние
- Продакшен активен.
- ref/CRM работают.
- LIVE вебинар существует.
- AUTO вебинар планируется.
- Ingest entry: Web Form → core_ingest_event.
- Scenario: New web_form lead → Telegram notify.

## Lead basic metrics (MVP)
- lead_source: источник лида из payload.source (если доступен).
- first_touch_at: unix timestamp первого касания (time()).
- lead_created_at: unix timestamp создания лида (time()).
- lead_created_via: канал создания лида, фиксированное значение core_ingest.

## Lead entity (canonical)
- lead_entry: internal-only CPT, создаётся на `core_ingest_event` и используется как канонический lead_id.
