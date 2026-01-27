# Состояние системы

## Текущее состояние
- Продакшен активен.
- ref/CRM работают.
- LIVE вебинар существует.
- AUTO вебинар планируется.
- Ingest entry: Web Form → core_ingest_event.
- Core Webinar entity: канонический webinar_event (CPT) с расписанием/статусом.
- Scenario: New web_form lead → Telegram notify.
- Scenario: core_lead_created → scenario_start (welcome) для web_form.
- Scenario: scenario_start(welcome) → Telegram welcome message.
- Scenario: scenario_start(welcome) → (delay 10 min) → follow-up Telegram.
- Integration: Client Webinar Tracker v2 принимает inbound (template_redirect/wp_ajax) и эмитит client_webinar_entered/client_webinar_completed.
- Integration: Client Webinar Control Integration управляет start/stop через core_webinar_set_status.
- Service: Client Webinar Event Emitter нормализует client_webinar_completed → webinar_completed.
- Service: client_webinar_attendance_classified → Telegram уведомление партнеру.
- Service: client_webinar_attendance_classified → post_webinar_route (attended | not_attended).
- Service: post_webinar_route → Telegram follow-up кандидату (attended | not_attended).
- Service: post_webinar_route (not_attended) → Telegram предложение записи вебинара.
- UI: Webinar Room UI (MVP) реализован.
- UI: Webinar Public UI и Webinar Room UI используют только Core данные (render-only).
- Webinar MVP = STABLE.
- Next step: Traffic & Training modules.

## Канон ролей, прав и вебинаров (системно утверждено)
- Каноническая иерархия ролей: admin → speaker → leader → partner → candidate.
- Отдельной роли "moderator" не существует.
- Роль speaker — системная доверенная роль, не ограниченная вебинарами.
- Права определяются capabilities, а не введением новых ролей.
- Примеры capabilities для speaker: webinar_manage_chat, webinar_manage_status, approve_users / approve_profiles (опционально).
- Роль "host / speaker" в вебинаре — это контекст `webinar.speaker_id`, а не отдельная роль.
- Все пользователи с ролью speaker сохраняют права модерации вне зависимости от `webinar.speaker_id`.
- Владение чатом зависит от `stream_type`:
  - obs: внутренний чат включён, управляется speaker.
  - zoom: внутренний чат выключен, источник истины — внешний чат платформы.
  - telegram: внутренний чат выключен, источник истины — внешний чат группы.
- Запрещены двойные чаты для одного вебинара.
- Внешние платформы открываются в новой вкладке, страница вебинара остаётся открытой.

## Lead basic metrics (MVP)
- lead_source: источник лида из payload.source (если доступен).
- first_touch_at: unix timestamp первого касания (time()).
- lead_created_at: unix timestamp создания лида (time()).
- lead_created_via: канал создания лида, фиксированное значение core_ingest.

## Lead entity (canonical)
- lead_entry: internal-only CPT, создаётся на `core_ingest_event` и используется как канонический lead_id.

## События клиентского вебинара (MVP)
- Inbound (Integration): client-webinar-tracker-v2 эмитит client_webinar_entered/client_webinar_completed через do_action.
- Нормализация (Service): client-webinar-event-emitter эмитит канонический webinar_completed.
- Обработчики (Service): attendance/routing/telegram подписываются на webinar_completed и/или client_webinar_attendance_classified.
- События (реально реализовано): client_webinar_completed, client_webinar_entered, webinar_completed, client_webinar_attendance_classified.
- Минимальный контекст: lead_id (если доступен), webinar_id (string), timestamp (unix).

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
