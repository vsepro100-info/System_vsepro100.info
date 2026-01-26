# Webinar system — module map & legacy inventory

## 1. Overview
- WordPress = платформа.
- REF‑система уже существует (cookies + user_meta).
- Вебинары = надстройка над REF и ролями.
- Доступы: роли + user_meta.

## 2. Public Webinar Landing (FINAL)
- URL: `/webinars/`
- Shortcode: `[whieda_webinar_public]`
- Назначение:
  - расписание ближайших вебинаров
  - таймер
  - CTA “Войти / Зарегистрироваться”
  - `?ref` автоподстановка (cookies уже есть)
  - OG cache-buster
- Статус: **WORKING / FINAL UX**
- Legacy URL: `/live/` (оставлен как совместимость, не использовать в новой навигации)

## 3. Webinar Admin / Speaker
- URL: `/admin_webinar/`
- Shortcode: `[whieda_live_admin]`
- Функции:
  - заголовок
  - дата/время
  - постер
  - iframe плеера
  - iframe чата
  - CTA
- Доступ: `edit_pages`
- Статус: **WORKING / TO MOVE TO PLUGIN**

## 4. Webinar Room (Core UX)
- URL: `/account/webinar_room/`
- Shortcode: `[whieda_live_room]` или `[webinar_room]`
- Функции:
  - lobby (15 минут)
  - scheduled / live / ended
  - постер → плеер
  - список участников
  - чат
  - start / stop эфир (AJAX)
- Зависимости:
  - `[whieda_live_presence]`
  - `[whieda_room_chat]`
- Статус:
  - **UX READY**
  - **ARCHITECTURE LEGACY**
- Legacy URL: `/live_room/` (оставлен как совместимость, не использовать в новой навигации)

## 5. Roles & Access (MVP RULES — FINAL)
- Guest (неавторизован):
  - видит публичную страницу `/webinars/`
  - не попадает в комнату `/account/webinar_room/`
- Candidate (авторизован, не партнёр):
  - может войти в вебинар
  - видит презентацию
  - имеет CTA “Связаться со спонсором”
  - не имеет доступа к обучению
- Partner:
  - всё выше
  - + записи вебинаров (позже)
- Moderator / Editor:
  - start / stop эфир
  - управление статусом
- Примечания:
  - `pending` не обязателен как WP‑роль.
  - анкета = user_meta (`is_approved`, `whieda_login`).

## 6. MVP Access Levels (CLOSED)
- Candidate:
  - презентации
  - публичные вебинары
- Partner:
  - обучение (следующий модуль)
  - записи (следующий модуль)
  - инструменты (следующий модуль)
- VIP Partner (future):
  - закрытые вебинары
  - лидерское обучение

> Webinar MVP = **CLOSED** (UX и URL зафиксированы, без архитектурного рефакторинга).

## 7. Legacy Inventory
Перечисление найденных сниппетов в текущем репозитории. Без удаления.

### LEGACY — UX SOURCE
- `plugins/ui-webinar-entry/ui-webinar-entry.php`
  - UI‑лендинг вебинара (shortcode `[webinar_entry]`).
- `plugins/ui-webinar-room/ui-webinar-room.php`
  - UI‑комната вебинара (shortcode `[webinar_room]`), базовая логика входа/просмотра/завершения.

### LEGACY — TO REFACTOR
- `plugins/client-webinar-tracker-v2/client-webinar-tracker-v2.php`
  - AJAX‑эмиссия `client_webinar_completed`, tracking входа/выхода.
- `plugins/client-webinar-event-emitter/client-webinar-event-emitter.php`
  - Приведение `client_webinar_*` к каноническим `webinar_*` событиям.

### LEGACY — REMOVE LATER
- `plugins/core-legacy-bridge/core-legacy-bridge.php`
  - Legacy bridge для сопоставления событий (адаптер к Core Engine).
