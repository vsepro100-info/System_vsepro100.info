Status: SPEC
Owner: Architect
Last updated: 2026-01-28

# Webinars — MVP Runtime Boundaries

## Назначение
Зафиксировать минимальный исполняемый runtime‑контур Webinars MVP на основе CANON и без прогнозов. Документ описывает только то, что реально работает в текущем MVP‑runtime.

## Источники истины
- [CANON/ARCHITECTURE](../CANON/ARCHITECTURE.md)
- [CANON/MODULE_BOUNDARIES](../CANON/MODULE_BOUNDARIES.md)
- [CANON/EVENT_HOOK_MAP](../CANON/EVENT_HOOK_MAP.md)
- [CANON/STATE_MODEL](../CANON/STATE_MODEL.md)
- [CANON/PUBLIC_CONTRACTS](../CANON/PUBLIC_CONTRACTS.md)
- [BASELINE](../BASELINE.md)

## MVP Runtime — IN‑SCOPE (реально исполняется)

### Участвующие модули (LIVE Webinars)
- **Core (webinar_event)** — источник канонического статуса и расписания; управление стартом/стопом только через `core_webinar_set_status`. 
- **Webinar Room UI** — MVP‑интерфейс комнаты вебинара (render‑only, данные из Core).
- **Webinar Public UI** — публичная страница и админ‑форма расписания (render‑only; сохранение через Core).
- **Client Webinar Control Integration** — старт/стоп вебинара через `core_webinar_set_status`.
- **Client Webinar Tracker v2** — фиксирует `client_webinar_entered` и `client_webinar_completed`.
- **Client Webinar Event Emitter** — нормализует `client_webinar_completed` → `webinar_completed`.
- **Client Webinar Attendance Service** — классификация посещения → `client_webinar_attendance_classified`.
- **Client Webinar Attendance Telegram** — уведомление партнёра по `client_webinar_attendance_classified`.
- **Post Webinar Routing Service** — `client_webinar_attendance_classified` → `post_webinar_route`.
- **Post Webinar Follow‑up Telegram** — follow‑up кандидату по `post_webinar_route`.
- **Post Webinar Recording Follow‑up Telegram** — отправка записи по `post_webinar_route (not_attended)`.

### Минимальный runtime‑поток (LIVE)
1) **Инициализация**
   - Core содержит каноническую сущность вебинара (`webinar_event`) и расписание.
   - Публичный контракт статуса: только `core_webinar_set_status(start|stop)`.

2) **Запуск вебинара**
   - Спикер/админ инициирует старт через Client Webinar Control Integration.
   - В Core фиксируется переход `scheduled` → `live`.

3) **Участие пользователя**
   - Пользователь входит в комнату вебинара через Webinar Room UI.
   - Client Webinar Tracker v2 эмитит `client_webinar_entered`.
   - По завершении участия эмитится `client_webinar_completed`.
   - Client Webinar Event Emitter преобразует событие в `webinar_completed`.
   - Client Webinar Attendance Service классифицирует участие и эмитит `client_webinar_attendance_classified`.

4) **Завершение**
   - Спикер/админ инициирует стоп через `core_webinar_set_status`.
   - Core фиксирует переход `live/paused` → `finished`.
   - Post Webinar Routing Service формирует `post_webinar_route`.
   - Telegram follow‑up выполняется по маршруту (attended / not_attended), включая отправку записи.

## MVP Runtime — OUT‑OF‑SCOPE (post‑MVP / не исполняется)

### Отложенные или не реализованные модули/события
- **AUTO Webinars / AutoWebinar Delivery** — AUTO вебинар помечен как планируемый; AutoWebinar runtime‑контур исключён.
- **AutoWebinar события** (`autowebinar_*`, а также `core_user_registered`, `core_user_login`, `core_ref_context_resolved`) — используются только в AutoWebinar runtime.
- **Client webinar события, помеченные как «запланировано / не реализовано»**:
  - `client_webinar_registered`
  - `client_webinar_left`
  - `client_webinar_form_submitted`
- **Будущие модули Traffic & Training** — отмечены как следующий этап, не участвуют в MVP runtime.

## Чёткая граница MVP
- MVP runtime = только LIVE‑вебинар с текущими реализованными UI/интеграциями и post‑webinar уведомлениями.
- Всё, что относится к AUTO, будущим модулям или не реализованным client‑events, находится за пределами текущего MVP.
