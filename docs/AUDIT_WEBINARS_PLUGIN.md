# Webinars Plugin — Audit & Skeleton Normalization

## Scope
- Audit only: structure and module layout for Webinars-related plugins.
- No behavior/logic changes; only file layout and bootstrap wiring.

---

## Как есть (до нормализации)

### Дерево директорий (модули Webinars)
Реализация Webinars распределена по набору независимых плагинов в `plugins/`:

- `access-speaker/`
- `autowebinar-delivery/`
- `client-webinar-action-consumer/`
- `client-webinar-attendance-service/`
- `client-webinar-attendance-telegram/`
- `client-webinar-control-integration/`
- `client-webinar-event-emitter/`
- `client-webinar-scenario-service/`
- `client-webinar-tracker-v2/`
- `post-webinar-followup-telegram/`
- `post-webinar-recording-followup-telegram/`
- `post-webinar-routing-service/`
- `scenario-client-webinar-telegram/`
- `ui-webinar-entry/`
- `ui-webinar-public/`
- `ui-webinar-room/`
- `webinar-chat/`

### Где лежит главный файл плагина
- Для каждого модуля главный файл лежит прямо в корне директории:
  - `<module>/<module>.php`

### Слои (admin/public/includes/etc.)
- Формальной слоистой структуры нет: весь код живёт в одном файле.
- UI-слои смешаны в рамках одного файла модуля (например, публичные страницы и админ‑UI в `ui-webinar-public`).
- В `ui-webinar-room` вместе живут: UI, REST-эндпоинты, проверки доступа.
- В `webinar-chat` совмещены: модель данных/таблицы, AJAX, UI‑рендер.

### Что выглядит как «псевдоплагин» / смешение модулей
- **Смешение слоёв внутри одного модуля** (UI + admin + transport):
  - `ui-webinar-public` — публичный лендинг + админ‑настройки.
  - `ui-webinar-room` — UI + REST‑слой управления состоянием.
  - `webinar-chat` — инфраструктура хранения + AJAX + UI.
- **Разделение по доменным ролям есть**, но скелет одинаково «плоский» и не подчёркивает границы слоёв.

---

## Как должно быть (целевая структура)

### Принципы
- **Один плагин = один модуль.**
- **Никаких require/include чужих модулей.** Взаимодействие только через hooks.
- Скелет каждого модуля единообразен и подчёркивает границы (bootstrap отдельно от логики).

### Единый скелет модуля-плагина
```
plugins/<module>/
  <module>.php            # заголовок плагина + константы + bootstrap include
  includes/
    bootstrap.php         # фактическая логика модуля (без изменения логики)
```

### Что меняется в текущем PR
- Все Webinars‑модули переведены на единый скелет: код вынесен в `includes/bootstrap.php`, главный файл остался загрузчиком.
- Поведение/логика не меняются, только расположение файлов и единый шаблон загрузки.

---

## Шаги миграции (без кода)
1. Для каждого модуля создать директорию `includes/`.
2. Перенести содержимое модуля из `<module>.php` в `includes/bootstrap.php`.
3. В `<module>.php` оставить только:
   - заголовок плагина,
   - `defined('ABSPATH') || exit;`,
   - (при необходимости) константы пути к основному файлу,
   - `require_once __DIR__ . '/includes/bootstrap.php';`.
4. Если используются activation hooks — привязать их к основному файлу плагина через константу.
5. Проверить, что в модулях нет include/require из других модулей; взаимодействие только через hooks.

---

## Итог
- Скелет унифицирован.
- Логика и поведение неизменны.
- Подготовлена база для дальнейшего разнесения слоёв (admin/public) без изменения контрактов.
