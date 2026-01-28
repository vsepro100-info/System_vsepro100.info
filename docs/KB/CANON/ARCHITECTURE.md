Status: CANON
Owner: Architect
Last updated: 2026-01-28

# Архитектура

## Принципы
- Модули независимы.
- Взаимодействие между модулями — только через hooks и контракты.
- Core создаёт канонические сущности и источники правды (ref/CRM).
- Канонический жизненный цикл лидов использует только `core_ingest_event` и события `core_lead_*`.
- Каноническая сущность лида: `lead_entry` (internal-only CPT).

## Канон владения чатом (недопустимы двойные чаты)
Источник чата зависит от `stream_type`:
- obs: внутренний чат включён, управляется speaker.
- zoom: внутренний чат выключен, источник истины — внешний чат платформы.
- telegram: внутренний чат выключен, источник истины — внешний чат группы.

## UX правила (высокоуровневые)
- Видимость внутреннего чата определяется только `stream_type`.
- Пользователь никогда не видит два чата для одного вебинара.
- Внешние платформы открываются в новой вкладке, страница платформы остаётся открытой.

## Канон админского UX вебинара
- `/admin_webinar/` по умолчанию открывается в режиме Speaker UI.
- Спикер видит только бизнес-поля и не может сломать конфигурацию вебинара.
- Технические поля спрятаны за настройками только для admin (иконка "gear").

## Канон CTA вебинара (системно утверждено)
- Вебинар хранит только гостевой CTA.
  - Текст по умолчанию: "Забронировать место на вебинар".
  - Ссылка по умолчанию: `/signup/`.
- Для авторизованных пользователей CTA из сущности не используется.
- UI всегда переопределяет CTA для авторизованных:
  - Текст: "Перейти в комнату вебинара".
  - Ссылка: `/account/webinar_room/`.
- Core не хранит и не мутирует "CTA для логина".
- Видимость CTA хранится в канонической сущности вебинара (`cta_visibility`).
- CTA скрыта по умолчанию (`hidden`) и показывается только по прямой команде спикера.
- Изменение видимости CTA допустимо только через Core hook `core_webinar_set_cta_visibility`.

## Webinars & Conversion Canon
1) **Первичная цель вебинара**
- Вебинар предназначен прежде всего для конверсии, а не обучения.
- Конверсия = переход к личному контакту с пригласившим / консультантом.

2) **Каноническая воронка вебинара**
- Точки входа:
  - внешние лендинги
  - расписание вебинаров
  - партнёрские ссылки
- Перед входом в комнату вебинара — обязательный логин.
- Комната вебинара существует внутри личного кабинета пользователя.

3) **Принципы комнаты вебинара**
- Внутри комнаты нет навигационных меню.
- Порядок фокуса:
  1) видео
  2) доверие
  3) CTA
- CTA:
  - единственный канонический CTA
  - контролируется спикером
  - скрыт по умолчанию
  - появляется в конце или по действию спикера
  - ведёт на `/account/contacts/`
  - открывается в новой вкладке

4) **Участники и доверие**
- Единый список участников для всех пользователей.
- Роли видимы всем (speaker / VIP / partner / guest).
- Флаги стран видимы всем.
- Структура рефералов и контактов не раскрывается.
- Цель: социальное доказательство и ощущение масштаба.

5) **Непрерывность конверсии**
- Пользователь всегда остаётся в контексте личного кабинета.
- CTA не прерывает поток вебинара.
- Контакт происходит после вебинара, не во время.

## Pre-Dialog Behavioral Signals
- Auto-detected country is allowed as a context signal.
- Allowed sources for auto-detected country: IP, timezone, locale.
- Country-level only; city or precise location is explicitly forbidden.
- Priority: self-reported country overrides auto-detected country.
- Notes:
  - Used for chat flags.
  - Used for CRM context.
  - No interpretations are allowed.

## Webinar Chat Module
- Внутренний чат комнаты вебинара — отдельный модуль с собственным UI, AJAX и хранением.

## Webinar Room State Sync
- Realtime UI state in Webinar Room is synchronized exclusively via REST API.
- admin-ajax is forbidden for webinar UI state.

## Legacy Runtime
- Legacy Runtime (vsepro100.info) не переносим.
- Используем его как эталон поведения и валидации решений.

## Текущее состояние системы
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
- UI: `/admin_webinar/` по умолчанию открывается в Speaker UI; технические поля спрятаны за admin-настройками (иконка "gear").
- CTA: вебинар хранит только гостевой CTA (по умолчанию "Забронировать место на вебинар" → `/signup/`).
- CTA: для авторизованных пользователей UI всегда переопределяет CTA ("Перейти в комнату вебинара" → `/account/webinar_room/`), Core это не хранит.
- CTA: видимость хранится в Core (`cta_visibility`), по умолчанию скрыта и управляется спикером.
- Webinar MVP = STABLE.
- Next step: Traffic & Training modules.

## Дорожная карта
- Текущие этапы: поддержка и стабилизация Legacy Runtime.
- Следующий этап: Webinars Layer.
- Следующий этап после Webinars Layer: Сценарии клиентского вебинара — поведенческая маршрутизация и напоминания на базе канонических событий вебинара без партнёрских уведомлений.

## Запреты (архитектурные)
- Не трогать /signup/.
- Не создавать вторую CRM.
- Не объединять LIVE и AUTO.

## Связанные документы
- [ROLES](ROLES.md)
- [MODULE_BOUNDARIES](MODULE_BOUNDARIES.md)
- [SPEC/WEBINARS/OVERVIEW](../SPEC/WEBINARS/OVERVIEW.md)
- [SPEC/WEBINARS/FLOWS](../SPEC/WEBINARS/FLOWS.md)
