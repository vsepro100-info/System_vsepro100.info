Status: CANON
Owner: Architect
Last updated: 2026-01-28

# Webinars — Canonical Data Model

## Назначение
Зафиксировать канонические сущности Webinars, их назначение, источники истины, принципы хранения и связи. Документ не описывает реализацию и согласован с канонической моделью состояний, ролей и публичных контрактов.

## Источники и приоритет
- Модель состояний: [CANON/STATE_MODEL](STATE_MODEL.md)
- Роли и разрешения: [CANON/PERMISSIONS_ROLES](PERMISSIONS_ROLES.md)
- Публичные контракты: [CANON/PUBLIC_CONTRACTS](PUBLIC_CONTRACTS.md)
- Архитектурные правила: [CANON/ARCHITECTURE](ARCHITECTURE.md)

---

## Канонические сущности

### 1) `webinar`
**Назначение:** Каноническая сущность вебинара (live/auto), отражает расписание, статус, базовые бизнес‑поля и управление CTA.

- **Источник истины:** Core Webinars.
- **Хранение:** WP post (CPT `webinar_event`) + post_meta.
- **Ключевые поля (минимум):**
  - `webinar_id`
  - `type` (`live` | `auto`)
  - `status` (`draft` | `scheduled` | `live` | `paused` | `finished` | `archived`)
  - `title`, `description`
  - `scheduled_at` (для live)
  - `cta_visibility` (`hidden` | `visible`)
  - `stream_type` (obs/zoom/telegram и т.п.)
  - `owner_user_id` (organizer)

### 2) `session`
**Назначение:** Сессия участия в вебинаре (контекст live‑слота или автосессии пользователя).

- **Источник истины:** Webinars Runtime / AutoWebinar Runtime.
- **Хранение:**
  - Live: custom table (ledger по `session_id`) или post_meta в рамках `webinar_event`.
  - Auto: `user_meta` с неймспейсом `autowebinar_*` (см. SPEC/WEBINARS/OVERVIEW).
- **Ключевые поля:**
  - `session_id`
  - `webinar_id`
  - `attendee_id` (lead/user)
  - `session_type` (`live` | `auto`)
  - `started_at`, `ended_at`
  - `progress` (для auto, 0/25/50/75/100)
  - `last_position_sec` (для auto)

### 3) `speaker`
**Назначение:** Спикер вебинара, инициирует старт/стоп и управляет CTA через публичные hooks.

- **Источник истины:** Core User/Role.
- **Хранение:** WP users + user_meta, привязка роли к `webinar_id` (mapping table или post_meta).
- **Ключевые поля:**
  - `user_id`
  - `display_name`
  - `role` (`speaker`)
  - `webinar_id`

### 4) `attendee`
**Назначение:** Участник вебинара (зарегистрированный пользователь/lead).

- **Источник истины:** Core (lead/user сущности).
- **Хранение:** WP users / CRM‑lead storage в Core; в Webinars хранится только ссылка на `lead_id`/`user_id`.
- **Ключевые поля:**
  - `lead_id` или `user_id`
  - `webinar_id`
  - `role` (`attendee`)
  - `registered_at` (если есть)

### 5) `registration`
**Назначение:** Факт регистрации пользователя на вебинар/слот.

- **Источник истины:** событие `client_webinar_registered` (Public Contracts).
- **Хранение:** custom table (ledger регистрации) или post_meta `webinar_event`.
- **Ключевые поля:**
  - `registration_id`
  - `webinar_id`
  - `lead_id`
  - `webinar_type` (`live` | `auto`)
  - `slot_id` (для live)
  - `registered_at`
  - `source`

### 6) `attendance`
**Назначение:** Факты входа/выхода/завершения участия.

- **Источник истины:** события `client_webinar_entered`, `client_webinar_left`, `client_webinar_completed`, `webinar_completed`.
- **Хранение:** custom table (ledger attendance) с привязкой к `session_id`.
- **Ключевые поля:**
  - `attendance_id`
  - `webinar_id`
  - `lead_id`
  - `session_id`
  - `entered_at`
  - `left_at`
  - `completed_at`
  - `leave_reason`

### 7) `message` / `chat`
**Назначение:** Сообщения чата вебинарной комнаты (если чат внутренний).

- **Источник истины:** Webinar Chat Module (внутренний чат) или внешняя платформа (zoom/telegram).
- **Хранение:**
  - Внутренний чат: custom table `webinar_chat_messages`.
  - Внешний чат: хранение вне системы, в Webinars только `external_chat_ref`.
- **Ключевые поля:**
  - `message_id`
  - `webinar_id`
  - `session_id` (опционально)
  - `author_id` (user/lead)
  - `message_body`
  - `created_at`
  - `moderation_status`

### 8) `delivery` / `autowebinar_artifact`
**Назначение:** Артефакты доставки AutoWebinar и прогресс просмотра пользователя.

- **Источник истины:** AutoWebinar Runtime (см. SPEC/WEBINARS/OVERVIEW).
- **Хранение:** `user_meta` (неймспейс `autowebinar_*`).
- **Ключевые поля:**
  - `autowebinar_session_id`
  - `autowebinar_started_at`
  - `autowebinar_last_position_sec`
  - `autowebinar_progress_percent`
  - `autowebinar_completed`
  - `autowebinar_cta_clicked_at`
  - `autowebinar_last_seen_at`

---

## Связи между сущностями
- `webinar` 1—N `session` (live слот или auto‑сессия пользователя).
- `webinar` 1—N `registration`.
- `registration` N—1 `attendee` (lead/user).
- `attendance` N—1 `session` и N—1 `attendee`.
- `webinar` 1—N `speaker` (роль `speaker` назначается на уровне вебинара).
- `webinar` 1—N `message` (если внутренний чат).
- `attendee` 1—1 `delivery/autowebinar_artifact` (на пользователя, если auto).

---

## Правила владения данными и мутаций

### Общие принципы
- Канонический статус вебинара изменяется **только** через публичный hook `core_webinar_set_status` с `start|stop`.
- Видимость CTA изменяется **только** через `core_webinar_set_cta_visibility`.
- Внешние модули не имеют права мутировать канонические сущности напрямую.

### Владение по сущностям
- **`webinar`**: владелец Core Webinars. Любые изменения статуса, расписания и CTA — только через Core и публичные контракты.
- **`session`**: владелец Webinars Runtime / AutoWebinar Runtime. Сессии создаются и закрываются системой, не UI напрямую.
- **`speaker` / `attendee`**: владелец Core (users/leads). Webinars хранит только ссылки и роль.
- **`registration`**: факт фиксируется событием `client_webinar_registered`; внешние системы не создают регистрацию напрямую.
- **`attendance`**: создаётся и обновляется только на основании `client_webinar_*` и нормализованных событий.
- **`message/chat`**: владелец Chat Module или внешняя платформа (при `stream_type` ≠ obs); Webinars не дублирует внешние чаты.
- **`delivery/autowebinar_artifact`**: владелец AutoWebinar Runtime; хранение строго в `user_meta` (`autowebinar_*`).

### Ограничения и инварианты
- Запрещены прямые записи в status/CTA поля `webinar` мимо публичных хуков.
- `paused` и `archived` — внутренние состояния Core; прямые изменения запрещены.
- Любые попытки изменения ref/CRM или Core‑данных из Webinars запрещены.
- Внешние чаты не копируются внутрь системы; хранится только ссылка/идентификатор.

Связанные документы:
- [CANON/STATE_MODEL](STATE_MODEL.md)
- [CANON/PERMISSIONS_ROLES](PERMISSIONS_ROLES.md)
- [CANON/PUBLIC_CONTRACTS](PUBLIC_CONTRACTS.md)
- [SPEC/WEBINARS/OVERVIEW](../SPEC/WEBINARS/OVERVIEW.md)
