Status: SPEC
Owner: Architect
Last updated: 2026-01-28

# Webinars — MVP v1+ Scope (фиксация)

## Назначение
Зафиксировать **фактический** объём MVP v1+ на основе текущего кода и KB. Документ фиксирует, что добавилось поверх v1, подтверждённые сценарии использования и ограничения. Никаких новых функций или предположений.

## Источники истины
- [SPEC/MVP_V1_SCOPE](MVP_V1_SCOPE.md)
- [SPEC/MVP_RUNTIME_BOUNDARIES](MVP_RUNTIME_BOUNDARIES.md)
- [SPEC/WEBINARS/PAGES](WEBINARS/PAGES.md)
- [CANON/EVENT_HOOK_MAP](../CANON/EVENT_HOOK_MAP.md)
- [CANON/MODULE_BOUNDARIES](../CANON/MODULE_BOUNDARIES.md)

---

## Что добавилось поверх v1 (MVP v1+)

### 1) UI polish (подтверждённый уровень UX)
- Публичная страница расписания `/webinars/` зафиксирована как **WORKING / FINAL UX**.
- Комната вебинара `/account/webinar_room/` зафиксирована как **UX READY**.
- Админ/спикер‑интерфейс `/admin_webinar/` подтверждён как **WORKING**.

> Это фиксация качества текущих UI‑страниц без расширения функциональности.

### 2) Telegram‑уведомления (интеграции после вебинара)
- Уведомление партнёра по событию `client_webinar_attendance_classified`.
- Follow‑up кандидату по маршруту `post_webinar_route`.
- Отправка записи при `post_webinar_route (not_attended)`.

---

## Подтверждённые сценарии использования (MVP v1+)

### Сценарий A: гость видит расписание
1) Гость открывает `/webinars/`.
2) Видит публичное расписание вебинаров.

### Сценарий B: спикер/админ запускает и завершает вебинар
1) Спикер/админ управляет вебинаром через `/admin_webinar/`.
2) Запускает/останавливает вебинар только через `core_webinar_set_status`.

### Сценарий C: кандидат участвует в LIVE‑вебинаре
1) Авторизованный кандидат заходит в `/account/webinar_room/`.
2) Фиксируются события `client_webinar_entered` и `client_webinar_completed`.
3) Классификация посещения создаёт `client_webinar_attendance_classified`.

### Сценарий D: Telegram‑follow‑up после вебинара
1) При `client_webinar_attendance_classified` партнёр получает уведомление в Telegram.
2) При `post_webinar_route` кандидат получает follow‑up.
3) При `post_webinar_route (not_attended)` отправляется запись вебинара.

---

## Ограничения MVP v1+
- Только LIVE‑контур; AUTO‑вебинары и связанные события не реализованы.
- UI‑страницы работают в режиме render‑only; изменения статуса только через `core_webinar_set_status`.
- Реально работают события: `client_webinar_entered`, `client_webinar_completed`, `client_webinar_attendance_classified`, `post_webinar_route`.
- Нет отдельной роли «moderator»; управление доступом через capabilities.
- Пост‑вебинарные действия ограничены маршрутизацией и Telegram‑уведомлениями (без иных каналов).
