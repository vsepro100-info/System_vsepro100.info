# Webinar system — module map & legacy inventory

## 1. Overview
- WordPress = платформа.
- REF‑система уже существует (cookies + user_meta).
- Вебинары = надстройка над REF и ролями.
- Доступы: роли + user_meta.

## 2. Public Webinar Landing
- URL: `/live/`
- Shortcode: `[whieda_webinar_public]`
- Назначение:
  - таймер
  - постер
  - CTA
  - `?ref` автоподстановка
  - OG cache-buster
- Статус: **WORKING / LEGACY UX**

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
- URL: `/live_room/`
- Shortcode: `[whieda_live_room]`
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

## 5. Roles & Access (Current State)
- candidate
- pending (через user_meta)
- partner
- editor (moderator)
- administrator
- Зафиксировать:
  - `pending` не обязателен как WP‑роль.
  - анкета = user_meta (`is_approved`, `whieda_login`).

## 6. MVP Access Levels (PLAN)
- Candidate:
  - презентации
  - публичные вебинары
- Partner:
  - обучение
  - записи
  - инструменты
- VIP Partner (future):
  - закрытые вебинары
  - лидерское обучение

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
