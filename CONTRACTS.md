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
