Status: SPEC
Owner: Architect
Last updated: 2026-01-28

# Webinars — MVP v1 Functional Scope

## Назначение
Зафиксировать **фактический** функциональный объём Webinars MVP v1 на основе текущего кода и KB. Документ описывает только то, что уже реализовано в runtime и доступно пользователям/ролям без предположений о будущем.

## Источники истины
- [SPEC/MVP_RUNTIME_BOUNDARIES](MVP_RUNTIME_BOUNDARIES.md)
- [SPEC/MVP_ENTRY_POINTS](MVP_ENTRY_POINTS.md)
- [SPEC/WEBINARS/PAGES](WEBINARS/PAGES.md)
- [SPEC/WEBINARS/ADMIN](WEBINARS/ADMIN.md)
- [SPEC/WEBINARS/FLOWS](WEBINARS/FLOWS.md)
- [CANON/ROLES](../CANON/ROLES.md)

---

## Поддерживаемые роли (MVP v1)
- **admin / speaker** — управление запуском и завершением вебинара через канонический hook `core_webinar_set_status`; доступ к админской странице `/admin_webinar/` с бизнес‑полями вебинара. 
- **candidate (авторизованный участник)** — участие в вебинаре в комнате `/account/webinar_room/`, просмотр презентации и CTA.
- **guest (неавторизованный)** — просмотр публичной страницы расписания `/webinars/`.
- **partner** — та же базовая возможность участия, что и у candidate; доступ к записям не входит в MVP v1.

> Роль «moderator» отсутствует; права определяются через capabilities, без введения новых ролей.

---

## IN MVP v1 (реально работает)

### 1) Публичные страницы и UI
- Публичная страница расписания `/webinars/` (shortcode `[whieda_webinar_public]`).
- Комната вебинара в кабинете `/account/webinar_room/` (shortcode `[whieda_live_room]`).
- Админ/спикер‑интерфейс `/admin_webinar/` (shortcode `[whieda_live_admin]`).

### 2) LIVE‑вебинар: основной runtime‑контур
- **Core webinar_event** как источник статуса и расписания; запуск/остановка только через `core_webinar_set_status`.
- **Webinar Room UI** и **Webinar Public UI** работают в режиме render‑only, данные поступают из Core.
- **Client Webinar Tracker v2** фиксирует факты участия: `client_webinar_entered` и `client_webinar_completed`.
- **Client Webinar Attendance Service** классифицирует посещение и эмитит `client_webinar_attendance_classified`.
- **Post Webinar Routing + Telegram‑follow‑up** обрабатывают маршрут после вебинара и отправляют уведомления кандидату/партнёру.

### 3) Канонические entry points (happy‑path)
- `core_webinar_set_status` — старт/стоп вебинара.
- `client_webinar_entered` → `client_webinar_completed` → `webinar_completed`.
- `client_webinar_attendance_classified` → `post_webinar_route`.
- REST Room State Sync (read‑only) — синхронизация состояния комнаты.

---

## Happy‑path сценарии (MVP v1)

### Сценарий A: гость видит расписание
1) Гость открывает `/webinars/`.
2) Видит публичное расписание вебинаров.

### Сценарий B: спикер/админ запускает и завершает вебинар
1) Спикер/админ открывает `/admin_webinar/` и управляет бизнес‑полями (заголовок, дата/время, постер, iframe‑плеер/чат, CTA).
2) Запускает вебинар через `core_webinar_set_status(start)`.
3) По окончании останавливает через `core_webinar_set_status(stop)`.

### Сценарий C: кандидат участвует в LIVE‑вебинаре
1) Авторизованный кандидат заходит в `/account/webinar_room/`.
2) Входит в вебинар, фиксируется `client_webinar_entered`.
3) Завершает участие, фиксируется `client_webinar_completed` → `webinar_completed`.
4) Сервис классификации создаёт `client_webinar_attendance_classified`.
5) Post‑webinar роутинг запускает follow‑up сообщения (attended / not_attended).

---

## OUT OF MVP v1 (post‑MVP / не реализовано)

### Функциональность
- **AUTO Webinars / AutoWebinar Delivery** и все `autowebinar_*` события.
- Регистрация на вебинар как событие: `client_webinar_registered`.
- События выхода и формы: `client_webinar_left`, `client_webinar_form_submitted`.
- Любые новые модули Traffic & Training.
- Записи вебинаров для партнёров (явно помечено как «позже»).

### Технические контуры
- REST write‑операции (изменение статуса/CTA через REST) — отсутствуют.
- Любые обходы `/signup/` или вмешательства в ref/CRM контуры (актуально только для AUTO, не в MVP).

---

## Ограничения MVP v1 (осознанные)
- Только LIVE‑вебинарный контур; AUTO‑контур выключен.
- UI комнаты и публичной страницы — render‑only; изменения статуса выполняются только через публичный hook Core.
- Реально реализованы только события `client_webinar_entered`, `client_webinar_completed`, `client_webinar_attendance_classified` (остальные канонические события не работают).
- Нет отдельной роли «moderator»; доступы управляются capabilities.
- Все post‑webinar действия ограничены маршрутизацией и уведомлениями по `post_webinar_route`.

