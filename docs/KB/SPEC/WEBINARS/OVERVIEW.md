Status: SPEC
Owner: Architect
Last updated: 2026-01-28

# Webinars — Overview

## Основные принципы
- Честность: никаких имитаций живого эфира.
- Прозрачность: пользователь всегда понимает, где живой формат, а где запись.
- Приоритет живых вебинаров как основной канал конверсии.
- Авто‑вебинары используются только как удобный запасной вариант.

## Live‑вебинар (по расписанию)
- Проводится в заранее объявленные даты и время.
- Пользователь регистрируется на конкретный слот.
- Коммуникация указывает, что это живой эфир.
- Все взаимодействия происходят без имитаций.

## Auto‑вебинар (запись)
- Это запись ранее проведённого живого вебинара.
- Помечается как запись на всех экранах.
- Доступ предоставляется вне расписания, для удобства пользователя.

## Post‑webinar UX‑flow (3 экрана, без форм)
1. Экран 1: подтверждение окончания вебинара.
2. Экран 2: следующая логическая опция действия без ввода данных.
3. Экран 3: финальный экран с выбором дальнейшего шага без форм.

## Допустимые pre‑dialog CRM‑сигналы
- Время регистрации на вебинар.
- Выбор слота живого вебинара.
- Факт открытия письма/уведомления.
- Факт перехода по ссылке.

## Явно запрещённые практики
- Фейковый чат.
- Фейковые пользователи.
- Боты, имитирующие активность или вопросы.

## AutoWebinar Delivery (MVP)

### Назначение
AutoWebinar — модуль доставки автоматического вебинара для пользователей, пришедших по реферальному трафику. Решает задачу масштабируемой выдачи контента без зависимости от LIVE-вебинара и без изменения существующих ref/CRM контуров.

### Канонический путь пользователя
Traffic → Landing → /signup/ → CRM → AutoWebinar Session → CTA → Partner.

### Эффект «живого»
- Смещение старта видео относительно момента входа пользователя.
- Повторные заходы продолжаются с ранее достигнутой позиции.
- Перемотка запрещена как принцип поведения.

### Используемые данные
- Referral Context: ref / wh_ref / invited_by.
- user_meta: статусы просмотра и ключевые события сессии.

### Генерируемые события (требования)
- autowebinar_join
- autowebinar_view_25
- autowebinar_view_50
- autowebinar_view_75
- autowebinar_view_100
- autowebinar_cta_click

### Категорически запрещено
- Трогать или обходить /signup/.
- Создавать вторую CRM или дублировать её сущности.
- Связывать AutoWebinar с LIVE-вебинаром на уровне выполнения.
- Разрывать или менять каноническую цепочку ref → wh_ref → invited_by.

## AutoWebinar Data Model (MVP)

### Общие принципы хранения данных
- Хранение строго в `user_meta`.
- Один источник истины по пользователю.
- Никаких новых таблиц.
- Неймспейс ключей: `autowebinar_*`.
- Данные должны поддерживать «эффект живого» (смещение старта + продолжение с позиции).
- Масштабирование: только компактные скалярные значения.

### Namespace
`autowebinar_*`

### user_meta ключи
| meta_key | Тип | Назначение |
| --- | --- | --- |
| autowebinar_session_id | string | Идентификатор текущей сессии AutoWebinar. |
| autowebinar_started_at | datetime | Время старта сессии (базовая точка «живого» смещения). |
| autowebinar_last_position_sec | int | Последняя сохранённая позиция просмотра в секундах. |
| autowebinar_progress_percent | int | Прогресс просмотра (0/25/50/75/100). |
| autowebinar_completed | bool | Флаг завершения просмотра. |
| autowebinar_cta_clicked_at | datetime | Время клика по CTA, если был. |
| autowebinar_last_seen_at | datetime | Время последней активности в AutoWebinar. |

### Что обновляется

**При входе**
- `autowebinar_session_id` (создать при первом входе, иначе использовать текущий).
- `autowebinar_started_at` (если сессия новая).
- `autowebinar_last_seen_at`.

**При просмотре**
- `autowebinar_last_position_sec`.
- `autowebinar_progress_percent` (25/50/75/100 по факту достижения).
- `autowebinar_completed` (при достижении 100%).
- `autowebinar_last_seen_at`.

**При выходе**
- `autowebinar_last_position_sec`.
- `autowebinar_last_seen_at`.

**При повторном входе**
- Использовать `autowebinar_last_position_sec` для продолжения.
- `autowebinar_last_seen_at`.
- Не сбрасывать прогресс и завершение.

### Какие данные НЕ храним и почему
- Историю событий и таймлайны (не масштабируется в `user_meta`).
- Любые массивы/JSON со списками просмотров (избыточно для MVP).
- Псевдоаналитику и агрегаты по всем пользователям (не место `user_meta`).
- Внешние ref/CRM данные (управляются Core, неизменяемы).

### Ограничения и запреты
- Запрещено создавать новые таблицы.
- Запрещено писать вне `user_meta`.
- Запрещено хранить данные вне неймспейса `autowebinar_*`.
- Запрещено хранить чужие данные (ref/CRM) в AutoWebinar.
- Запрещено хранить полные логи просмотров.

## AutoWebinar Runtime Contract

### Назначение
Канонический контракт взаимодействия AutoWebinar с Core (ref/CRM): какие события слушает и излучает модуль, какие минимальные данные передаются, и какие действия строго запрещены.

### Входные события (AutoWebinar слушает)
| Событие | Назначение | Минимальный payload |
| --- | --- | --- |
| core_user_registered | Фиксация регистрации пользователя в Core | event_id, user_id, ref_id, timestamp |
| core_user_login | Фиксация входа пользователя в систему | event_id, user_id, ref_id, timestamp |
| core_ref_context_resolved | Получение канонического referral context | event_id, user_id, ref_id, timestamp |

### Исходящие события (AutoWebinar излучает)
| Событие | Назначение | Минимальный payload |
| --- | --- | --- |
| autowebinar_session_created | Создана сессия AutoWebinar | event_id, user_id, ref_id, timestamp |
| autowebinar_join | Пользователь вошёл в AutoWebinar | event_id, user_id, ref_id, timestamp |
| autowebinar_progress | Прогресс просмотра (25/50/75/100) | event_id, user_id, ref_id, timestamp, progress |
| autowebinar_cta_click | Клик по CTA | event_id, user_id, ref_id, timestamp |
| autowebinar_completed | Завершение просмотра | event_id, user_id, ref_id, timestamp |

### Разрешённые записи данных
- Только в `user_meta`.
- Строго в неймспейсе `autowebinar_*`.

### Категорически запрещено
- Писать в CRM напрямую.
- Менять ref / invited_by.
- Вызывать signup или регистрацию.
- Читать чужие таблицы.

Связанные документы:
- [CANON/ARCHITECTURE](../../CANON/ARCHITECTURE.md)
- [CANON/MODULE_BOUNDARIES](../../CANON/MODULE_BOUNDARIES.md)
- [SPEC/WEBINARS/PAGES](PAGES.md)
- [SPEC/WEBINARS/FLOWS](FLOWS.md)
- [SPEC/WEBINARS/ADMIN](ADMIN.md)
