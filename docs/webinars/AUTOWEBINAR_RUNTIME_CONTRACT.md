# AutoWebinar Runtime Contract

## Назначение
Канонический контракт взаимодействия AutoWebinar с Core (ref/CRM): какие события слушает и излучает модуль, какие минимальные данные передаются, и какие действия строго запрещены.

## Входные события (AutoWebinar слушает)
| Событие | Назначение | Минимальный payload |
| --- | --- | --- |
| core_user_registered | Фиксация регистрации пользователя в Core | event_id, user_id, ref_id, timestamp |
| core_user_login | Фиксация входа пользователя в систему | event_id, user_id, ref_id, timestamp |
| core_ref_context_resolved | Получение канонического referral context | event_id, user_id, ref_id, timestamp |

## Исходящие события (AutoWebinar излучает)
| Событие | Назначение | Минимальный payload |
| --- | --- | --- |
| autowebinar_session_created | Создана сессия AutoWebinar | event_id, user_id, ref_id, timestamp |
| autowebinar_join | Пользователь вошёл в AutoWebinar | event_id, user_id, ref_id, timestamp |
| autowebinar_progress | Прогресс просмотра (25/50/75/100) | event_id, user_id, ref_id, timestamp, progress |
| autowebinar_cta_click | Клик по CTA | event_id, user_id, ref_id, timestamp |
| autowebinar_completed | Завершение просмотра | event_id, user_id, ref_id, timestamp |

## Разрешённые записи данных
- Только в `user_meta`.
- Строго в неймспейсе `autowebinar_*`.

## Категорически запрещено
- Писать в CRM напрямую.
- Менять ref / invited_by.
- Вызывать signup или регистрацию.
- Читать чужие таблицы.
