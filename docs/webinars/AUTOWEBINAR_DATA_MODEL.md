# AutoWebinar Data Model (MVP)

## Общие принципы хранения данных
- Хранение строго в `user_meta`.
- Один источник истины по пользователю.
- Никаких новых таблиц.
- Неймспейс ключей: `autowebinar_*`.
- Данные должны поддерживать «эффект живого» (смещение старта + продолжение с позиции).
- Масштабирование: только компактные скалярные значения.

## Namespace
`autowebinar_*`

## user_meta ключи
| meta_key | Тип | Назначение |
| --- | --- | --- |
| autowebinar_session_id | string | Идентификатор текущей сессии AutoWebinar. |
| autowebinar_started_at | datetime | Время старта сессии (базовая точка «живого» смещения). |
| autowebinar_last_position_sec | int | Последняя сохранённая позиция просмотра в секундах. |
| autowebinar_progress_percent | int | Прогресс просмотра (0/25/50/75/100). |
| autowebinar_completed | bool | Флаг завершения просмотра. |
| autowebinar_cta_clicked_at | datetime | Время клика по CTA, если был. |
| autowebinar_last_seen_at | datetime | Время последней активности в AutoWebinar. |

## Что обновляется

### При входе
- `autowebinar_session_id` (создать при первом входе, иначе использовать текущий).
- `autowebinar_started_at` (если сессия новая).
- `autowebinar_last_seen_at`.

### При просмотре
- `autowebinar_last_position_sec`.
- `autowebinar_progress_percent` (25/50/75/100 по факту достижения).
- `autowebinar_completed` (при достижении 100%).
- `autowebinar_last_seen_at`.

### При выходе
- `autowebinar_last_position_sec`.
- `autowebinar_last_seen_at`.

### При повторном входе
- Использовать `autowebinar_last_position_sec` для продолжения.
- `autowebinar_last_seen_at`.
- Не сбрасывать прогресс и завершение.

## Какие данные НЕ храним и почему
- Историю событий и таймлайны (не масштабируется в `user_meta`).
- Любые массивы/JSON со списками просмотров (избыточно для MVP).
- Псевдоаналитику и агрегаты по всем пользователям (не место `user_meta`).
- Внешние ref/CRM данные (управляются Core, неизменяемы).

## Ограничения и запреты
- Запрещено создавать новые таблицы.
- Запрещено писать вне `user_meta`.
- Запрещено хранить данные вне неймспейса `autowebinar_*`.
- Запрещено хранить чужие данные (ref/CRM) в AutoWebinar.
- Запрещено хранить полные логи просмотров.
