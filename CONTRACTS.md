# Контракты

## Канонический Referral Context
- ref → wh_ref → invited_by.
- Только одна цепочка, без дублирования полей.

## Канонические события лидов
- `core_ingest_event`
- `core_lead_created( $lead_id, $payload )` — canonical lead entity created from ingest payload.
- `core_lead_updated`
- `core_lead_deleted`
- `core_lead_merged`

## Регистрация
- /signup/ — единственная точка входа регистрации.

## Язык пользовательских коммуникаций
- Все пользовательские уведомления, сообщения, описания плагинов и интерфейсов должны быть на русском языке.
- Английский язык допускается только для технических идентификаторов и кода.

## Клиентские сценарии вебинара
- Клиентские сценарии требуют явных событий состояния клиента.
- Запрещены временные предположения без поведенческого подтверждения.

## Client Webinar State Events (raw/integration)
Events (inbound, от интеграции):
- `client_webinar_entered`
- `client_webinar_completed`
- `client_webinar_form_submitted`

Minimal payload keys (all optional but normalized when present):
- `lead_id` (int)
- `webinar_id` (string)
- `timestamp` (int, unix)

## Client Webinar Downstream Event (canonical)
- `webinar_completed` — каноническое downstream-событие для сервисов (emitter преобразует client_webinar_completed → webinar_completed).

## AutoWebinar Runtime Contract

### Неймспейсы и события
- Входные события Core: `core_*`.
- Исходящие события AutoWebinar: `autowebinar_*`.
- Разрешённые записи: только `user_meta` в неймспейсе `autowebinar_*`.

### Запреты
- Нельзя писать в CRM напрямую.
- Нельзя менять ref / invited_by.
- Нельзя вызывать signup или регистрацию.
- Нельзя читать чужие таблицы.
