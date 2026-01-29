Status: CANON
Owner: Architect
Last updated: 2026-01-28

# Webinars — Event & Hook Map

## Назначение
Зафиксировать каноническую карту событий (actions/filters) между модулями Webinars. Документ описывает только **hooks‑контракты** и границы эмиттера/слушателей без реализации, прямых вызовов или include/require.

## Правила
- Взаимодействие модулей — **только через actions/filters**.
- Никаких прямых вызовов/require/include между модулями.
- Эмиттер отвечает только за факт события; бизнес‑логика и уведомления — дело слушателей.
- Состояние реализации отмечается, если известно из SPEC.

---

## Слой: Domain (lifecycle / Core)

| Hook (action/filter) | Тип | Эмиттер (модуль) | Слушатели (модули) | Назначение | Примечание |
| --- | --- | --- | --- | --- | --- |
| `core_ingest_event` | action | integration-web-form | Core | Входная точка для инжеста лида из веб‑формы. | Канонический вход в Core ingest. |
| `core_lead_created` | action | Core | scenario-engine | Триггер запуска сценариев после создания лида. | Канонический lead‑события Core. |
| `core_lead_updated` | action | Core | scenario-engine | Сигнал обновления лида для сценарного слоя. | Канонический lead‑события Core. |
| `core_lead_deleted` | action | Core | scenario-engine | Сигнал удаления лида для сценарного слоя. | Канонический lead‑события Core. |
| `core_lead_merged` | action | Core | scenario-engine | Сигнал слияния лида для сценарного слоя. | Канонический lead‑события Core. |
| `core_webinar_set_status` | action | client-webinar-control-integration | Core | Канонический старт/стоп вебинара через Core. | Управление статусом разрешено только через Core hook. |
| `core_webinar_set_cta_visibility` | action | Webinar Room UI (speaker) / Webinar Public UI (admin) | Core | Каноническое управление видимостью CTA. | UI не хранит CTA, только вызывает Core hook. |
| `core_user_registered` | action | Core | autowebinar-delivery | Фиксация регистрации пользователя для AutoWebinar. | AutoWebinar runtime contract. |
| `core_user_login` | action | Core | autowebinar-delivery | Фиксация входа пользователя для AutoWebinar. | AutoWebinar runtime contract. |
| `core_ref_context_resolved` | action | Core | autowebinar-delivery | Получение канонического referral context. | AutoWebinar runtime contract. |

---

## Слой: Client/User (client webinar behavior)

| Hook (action/filter) | Тип | Эмиттер (модуль) | Слушатели (модули) | Назначение | Примечание |
| --- | --- | --- | --- | --- | --- |
| `client_webinar_registered` | action | client-webinar-tracker-v2 | client-webinar-action-consumer | Факт регистрации клиента на вебинар. | Запланировано / не реализовано. |
| `client_webinar_entered` | action | client-webinar-tracker-v2 | client-webinar-attendance-service, client-webinar-action-consumer | Факт входа клиента в вебинар. | Реально реализовано. |
| `client_webinar_left` | action | client-webinar-tracker-v2 | client-webinar-attendance-service, client-webinar-action-consumer | Факт выхода клиента. | Запланировано / не реализовано. |
| `client_webinar_completed` | action | client-webinar-tracker-v2 | client-webinar-event-emitter, client-webinar-attendance-service, client-webinar-action-consumer | Факт завершения участия клиента. | Реально реализовано. |
| `client_webinar_form_submitted` | action | client-webinar-tracker-v2 | client-webinar-action-consumer | Факт отправки пост‑вебинарной формы. | Запланировано / не реализовано. |
| `webinar_completed` | action | client-webinar-event-emitter | client-webinar-scenario-service, client-webinar-action-consumer | Канонический downstream‑факт завершения. | Нормализация client_webinar_completed. |
| `client_webinar_attendance_classified` | action | client-webinar-attendance-service | client-webinar-attendance-telegram, post-webinar-routing-service, client-webinar-action-consumer | Классификация посещения (attended/not). | Реально реализовано. |
| `post_webinar_route` | action | post-webinar-routing-service | post-webinar-followup-telegram, post-webinar-recording-followup-telegram | Маршрут post‑webinar (attended/not_attended). | Реально реализовано. |

---

## Слой: Delivery / AutoWebinar

| Hook (action/filter) | Тип | Эмиттер (модуль) | Слушатели (модули) | Назначение | Примечание |
| --- | --- | --- | --- | --- | --- |
| `autowebinar_session_created` | action | autowebinar-delivery | Core / аналитика | Создание сессии AutoWebinar. | AutoWebinar runtime contract. |
| `autowebinar_join` | action | autowebinar-delivery | Core / аналитика | Факт входа пользователя в AutoWebinar. | AutoWebinar runtime contract. |
| `autowebinar_progress` | action | autowebinar-delivery | Core / аналитика | Прогресс просмотра (25/50/75/100). | Canon: progress = 25/50/75/100. |
| `autowebinar_cta_click` | action | autowebinar-delivery | Core / аналитика | Факт клика по CTA в AutoWebinar. | AutoWebinar runtime contract. |
| `autowebinar_completed` | action | autowebinar-delivery | Core / аналитика | Завершение просмотра AutoWebinar. | AutoWebinar runtime contract. |

---

## Слой: UI

| Hook (action/filter) | Тип | Эмиттер (модуль) | Слушатели (модули) | Назначение | Примечание |
| --- | --- | --- | --- | --- | --- |
| `core_webinar_set_cta_visibility` | action | Webinar Room UI (speaker) / Webinar Public UI (admin) | Core | Единственный разрешённый способ показать/скрыть CTA. | Повтор для UI‑слоя как системное правило. |
| `core_webinar_set_status` | action | client-webinar-control-integration (UI‑triggered) | Core | Старт/стоп вебинара по команде спикера. | UI не меняет статус напрямую. |

---

## Слой: Integrations (Telegram и др.)

| Hook (action/filter) | Тип | Эмиттер (модуль) | Слушатели (модули) | Назначение | Примечание |
| --- | --- | --- | --- | --- | --- |
| `client_webinar_attendance_classified` | action | client-webinar-attendance-service | client-webinar-attendance-telegram | Уведомление партнёра о посещении. | Telegram‑интеграция. |
| `post_webinar_route` | action | post-webinar-routing-service | post-webinar-followup-telegram | Follow‑up кандидату после вебинара. | Telegram‑интеграция. |
| `post_webinar_route` (not_attended) | action | post-webinar-routing-service | post-webinar-recording-followup-telegram | Отправка записи тем, кто не посетил. | Telegram‑интеграция. |

---

## Источники и связи
- Канонический список модулей и границы: [MODULE_BOUNDARIES](MODULE_BOUNDARIES.md)
- Канонические принципы hooks: [ARCHITECTURE](ARCHITECTURE.md)
- Контракт событий client‑webinar: [SPEC/WEBINARS/FLOWS](../SPEC/WEBINARS/FLOWS.md)
- AutoWebinar runtime contract: [SPEC/WEBINARS/OVERVIEW](../SPEC/WEBINARS/OVERVIEW.md)
