# Модули

## Core (Legacy)
- Legacy Runtime (vsepro100.info) как эталон.
- Канонические сущности ref/CRM.

## Webinars Layer
- LIVE вебинары.
- AUTO вебинары.
- Core Webinar Entity — Core: каноническая сущность вебинара (CPT webinar_event), статус/расписание/источники.

## Client Webinar
- Webinar Entry UI — UI: входная страница, без логики, только отображение.
- Webinar Room UI — UI: интерфейс комнаты вебинара, только отображение, данные из Core.
- Webinar Public UI — UI: публичная страница и админ‑форма расписания (сохранение через Core).
- Client Webinar Tracker v2 — Integration: inbound (template_redirect/wp_ajax) → client_webinar_entered/client_webinar_completed.
- Client Webinar Event Emitter — Service: нормализует client_webinar_completed → webinar_completed.
- Client Webinar Scenario Service — Service: подписка на webinar_completed и запуск сценариев.
- Client Webinar Attendance Service — Service: классификация посещения → client_webinar_attendance_classified.
- Client Webinar Attendance Telegram — Service: client_webinar_attendance_classified → уведомление в Telegram.
- Client Webinar Action Consumer — Service: реагирует на client_webinar_* и сценарные события.
- Post Webinar Routing Service — Service: client_webinar_attendance_classified → post_webinar_route.
- Post Webinar Follow-up Telegram — Service: post_webinar_route → follow-up Telegram.
- Post Webinar Recording Follow-up Telegram — Service: post_webinar_route(not_attended) → Telegram с записью.

## Integrations

### integration-web-form (Integration)
**Назначение:** базовая интеграция веб-формы с Core Ingest.

**Границы ответственности:**
- Отрисовка минимальной HTML-формы для сбора name и email.
- Формирование payload и вызов хука core_ingest_event.

**Не входит в ответственность:**
- Создание сущностей или lead_meta в Core.
- Любая бизнес-логика Core или прямые записи в БД.

### client-webinar-control-integration (Integration)
**Назначение:** старт/стоп вебинара через AJAX и Core hook.

**Границы ответственности:**
- Только проверка доступа, nonce и вызов `core_webinar_set_status`.

**Не входит в ответственность:**
- Любая логика UI или хранение состояния.

### autowebinar-delivery (Service/UI)
**Назначение:** доставка AutoWebinar как независимого модуля без зависимости от LIVE.

**Границы ответственности:**
- Показывает AutoWebinar с «эффектом живого» и управляет сессией просмотра.
- Использует только канонический referral/CRM контур и user_meta.
- Генерирует события AutoWebinar для аналитики и CRM.

**Не входит в ответственность:**
- Регистрация пользователей (любой обход /signup/).
- Создание или дублирование CRM.
- Логика LIVE-вебинара или общая подсистема live-доставки.

## Services

### scenario-engine (Service)
**Назначение:** диспетчер сценариев на основе канонических событий Core.

**Границы ответственности:**
- Подписка на канонические события Core и запуск сценариев.

**Не входит в ответственность:**
- Любая отправка сообщений или прямой контакт с внешними сервисами.

### scenario-welcome-telegram (Service)
**Назначение:** исполнитель сценария welcome с отправкой сообщения в Telegram.

**Границы ответственности:**
- Подписка на событие scenario_start и отправка Telegram-сообщения для сценария welcome.

**Не входит в ответственность:**
- Любая логика сценариев кроме welcome.

### scenario-followup-telegram (Service)
**Назначение:** исполнитель follow-up сценария с отложенной отправкой сообщения в Telegram.

**Границы ответственности:**
- Подписка на событие scenario_start, планирование отложенного запуска и отправка follow-up сообщения в Telegram.

**Не входит в ответственность:**
- Запуск новых сценариев или изменение логики core/scenario-engine.
