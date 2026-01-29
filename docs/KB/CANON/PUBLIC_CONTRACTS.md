Status: CANON
Owner: Architect
Last updated: 2026-01-28

# Webinars — Public Contracts & Extension Points

## Назначение
Зафиксировать **публичные** actions/filters Webinars, которые разрешены для внешних модулей, и обеспечить стабильность интеграций без доступа к внутренним/приватным хукам.

## Источник и приоритет
- Источник истины: [CANON/EVENT_HOOK_MAP](EVENT_HOOK_MAP.md).
- Этот документ описывает только **публичные точки расширения** и не заменяет архитектурные ограничения.

## Гарантии совместимости
- Имена хуков и типы (action/filter) стабильны в рамках CANON.
- Публичный контракт гарантирует **факт события** и минимальный payload, но не бизнес‑логику.
- Слушатели не должны полагаться на внутренние детали реализации или порядок выполнения других слушателей.

## Общие запреты для слушателей
Запрещено: 
- Вызывать прямые методы/модули Webinars или Core в обход hooks.
- Модифицировать Core‑данные (ref/CRM, статус вебинара, CTA) напрямую.
- Блокировать выполнение (исключения, die/exit), подменять глобальное состояние, или изменять входной payload «на месте».
- Использовать события для партнёрской логики, продаж или уведомлений, если контракт их не предусматривает.

---

## Lifecycle

### `core_webinar_set_status` (action)
**Сигнатура:** `core_webinar_set_status(webinar_id, status, context)`

**Параметры:**
- `webinar_id` — идентификатор вебинара.
- `status` — целевой статус (`start` | `stop`).
- `context` — минимальный контекст источника (например, `actor_id`, `timestamp`, `source`).

**Ожидаемое поведение / инварианты:**
- Единственный публичный способ инициировать старт/стоп вебинара через Core.
- UI/интеграции не меняют статус напрямую, только через этот hook.

**Запрещено слушателю:**
- Менять статус вебинара напрямую в базе или через приватные API.
- Подменять/нормализовать `status` вне допустимых значений.

---

## UI Extension

### `core_webinar_set_cta_visibility` (action)
**Сигнатура:** `core_webinar_set_cta_visibility(webinar_id, visible, context)`

**Параметры:**
- `webinar_id` — идентификатор вебинара.
- `visible` — флаг видимости CTA (`true` | `false`).
- `context` — минимальный контекст источника (например, `actor_id`, `timestamp`, `source`).

**Ожидаемое поведение / инварианты:**
- Единственный разрешённый публичный способ показать/скрыть CTA.
- UI не хранит состояние CTA и не изменяет его напрямую.

**Запрещено слушателю:**
- Хранить/кешировать состояние CTA вне Core.
- Влиять на отображение CTA обходными способами (прямая правка UI или данных).

---

## Client/User

### `client_webinar_registered` (action)
**Сигнатура:** `client_webinar_registered(payload)`

**Payload:**
- `lead_id`
- `webinar_id`
- `webinar_type` (`live` | `auto`)
- `slot_id` (для live, если применимо)
- `registered_at`
- `source`

**Ожидаемое поведение / инварианты:**
- Факт регистрации клиента на вебинар.
- Эмитируется при успешной регистрации.

**Запрещено слушателю:**
- Триггерить партнёрские коммуникации или продажи.
- Генерировать повторные регистрации или изменять `lead_id`.

### `client_webinar_entered` (action)
**Сигнатура:** `client_webinar_entered(payload)`

**Payload:**
- `lead_id`
- `webinar_id`
- `timestamp`

**Ожидаемое поведение / инварианты:**
- Факт входа клиента в вебинарный опыт.
- Эмитируется один раз на сессию участия.

**Запрещено слушателю:**
- Использовать событие для таймингов или расписаний.
- Модифицировать участие клиента напрямую.

### `client_webinar_left` (action)
**Сигнатура:** `client_webinar_left(payload)`

**Payload:**
- `lead_id`
- `webinar_id`
- `webinar_type` (`live` | `auto`)
- `session_id`
- `left_at`
- `leave_reason`

**Ожидаемое поведение / инварианты:**
- Факт выхода клиента из вебинарного опыта.
- Эмитируется один раз на сессию участия.

**Запрещено слушателю:**
- Переопределять причину выхода.
- Использовать событие как финал участия (для этого есть `client_webinar_completed`).

### `client_webinar_completed` (action)
**Сигнатура:** `client_webinar_completed(payload)`

**Payload:**
- `lead_id`
- `webinar_id`
- `webinar_type` (`live` | `auto`)
- `session_id`
- `completed_at`

**Ожидаемое поведение / инварианты:**
- Факт завершения участия клиента.
- Может следовать после `client_webinar_left`.

**Запрещено слушателю:**
- Подменять критерий завершения участия.
- Использовать событие как сигнал для партнёрских уведомлений.

### `client_webinar_form_submitted` (action)
**Сигнатура:** `client_webinar_form_submitted(payload)`

**Payload:**
- `lead_id`
- `webinar_id`
- `webinar_type` (`live` | `auto`)
- `session_id`
- `submitted_at`
- `form_id`
- `form_payload`

**Ожидаемое поведение / инварианты:**
- Факт отправки пост‑вебинарной формы.
- Допускается повторная эмиссия при повторной отправке.

**Запрещено слушателю:**
- Пытаться интерпретировать форму как изменение CRM‑состояния.

### `webinar_completed` (action)
**Сигнатура:** `webinar_completed(payload)`

**Payload:**
- Совпадает с `client_webinar_completed`.

**Ожидаемое поведение / инварианты:**
- Нормализованный downstream‑факт завершения участия.
- Используется для унификации сценарных потребителей.

**Запрещено слушателю:**
- Предполагать, что событие всегда идёт из конкретного эмиттера.

---

## Delivery / AutoWebinar

### Входы AutoWebinar (слушает)

#### `core_user_registered` (action)
**Сигнатура:** `core_user_registered(event_id, user_id, ref_id, timestamp)`

**Ожидаемое поведение / инварианты:**
- Факт регистрации пользователя в Core.

**Запрещено слушателю:**
- Менять ref/CRM или инициировать регистрацию самостоятельно.

#### `core_user_login` (action)
**Сигнатура:** `core_user_login(event_id, user_id, ref_id, timestamp)`

**Ожидаемое поведение / инварианты:**
- Факт входа пользователя в систему.

**Запрещено слушателю:**
- Считать событие источником бизнес‑логики и продаж.

#### `core_ref_context_resolved` (action)
**Сигнатура:** `core_ref_context_resolved(event_id, user_id, ref_id, timestamp)`

**Ожидаемое поведение / инварианты:**
- Канонический referral context от Core.

**Запрещено слушателю:**
- Перезаписывать ref/invited_by или использовать неканонический ref.

### Исходы AutoWebinar (эмитит)

#### `autowebinar_session_created` (action)
**Сигнатура:** `autowebinar_session_created(event_id, user_id, ref_id, timestamp)`

**Ожидаемое поведение / инварианты:**
- Факт создания сессии AutoWebinar.

**Запрещено слушателю:**
- Использовать событие для записи данных вне `autowebinar_*`.

#### `autowebinar_join` (action)
**Сигнатура:** `autowebinar_join(event_id, user_id, ref_id, timestamp)`

**Ожидаемое поведение / инварианты:**
- Факт входа пользователя в AutoWebinar.

**Запрещено слушателю:**
- Привязывать событие к изменению CRM‑статусов.

#### `autowebinar_progress` (action)
**Сигнатура:** `autowebinar_progress(event_id, user_id, ref_id, timestamp, progress)`

**Ожидаемое поведение / инварианты:**
- Прогресс просмотра фиксируется только как 25/50/75/100.

**Запрещено слушателю:**
- Использовать любые иные значения прогресса.

#### `autowebinar_cta_click` (action)
**Сигнатура:** `autowebinar_cta_click(event_id, user_id, ref_id, timestamp)`

**Ожидаемое поведение / инварианты:**
- Факт клика по CTA в AutoWebinar.

**Запрещено слушателю:**
- Считать клик CTA подтверждением заявки или сделки.

#### `autowebinar_completed` (action)
**Сигнатура:** `autowebinar_completed(event_id, user_id, ref_id, timestamp)`

**Ожидаемое поведение / инварианты:**
- Факт завершения просмотра AutoWebinar.

**Запрещено слушателю:**
- Эмитировать follow‑up в обход сценариев/маршрутизации.

---

## Integrations

### `client_webinar_attendance_classified` (action)
**Сигнатура:** `client_webinar_attendance_classified(payload)`

**Payload:**
- `lead_id`
- `webinar_id`
- `attended` (`true` | `false`)
- `timestamp`

**Ожидаемое поведение / инварианты:**
- Классификация участия (attended/not_attended) на основе канонических клиентских событий.

**Запрещено слушателю:**
- Пересчитывать факт посещения без собственного источника.
- Добавлять партнёрскую логику внутри обработки классификации.

### `post_webinar_route` (action)
**Сигнатура:** `post_webinar_route(payload)`

**Payload:**
- `lead_id`
- `webinar_id`
- `attended` (`true` | `false`)
- `timestamp`
- `route` (`attended` | `not_attended`)

**Ожидаемое поведение / инварианты:**
- Роутинг post‑webinar сценария на основе классификации.

**Запрещено слушателю:**
- Менять `route` или подменять смысл `attended`.
- Создавать альтернативные маршруты без отдельного канонического решения.

---

## Явные исключения
- Внутренние/приватные хуки не являются частью публичного контракта.
- Любые новые публичные hooks добавляются только через обновление этого документа и [CANON/EVENT_HOOK_MAP](EVENT_HOOK_MAP.md).
