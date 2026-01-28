Status: SPEC
Owner: Architect
Last updated: 2026-01-28

# Webinars — Pages & Shortcodes

## Основные страницы Webinars

| URL | Назначение | Shortcode | Статус / примечание |
| --- | --- | --- | --- |
| `/webinars/` | Публичная страница с расписанием вебинаров | `[whieda_webinar_public]` | WORKING / FINAL UX |
| `/live/` | Legacy URL для совместимости | `[webinar_public]` | Не использовать в новой навигации |
| `/admin_webinar/` | Админ/спикер‑интерфейс вебинара | `[whieda_live_admin]` | WORKING / TO MOVE TO PLUGIN |
| `/account/webinar_room/` | Комната вебинара в личном кабинете | `[whieda_live_room]` / `[webinar_room]` | UX READY / ARCHITECTURE LEGACY |
| `/live_room/` | Legacy URL комнаты | `[whieda_live_room]` | Не использовать в новой навигации |

## Зависимости комнаты вебинара
- `[whieda_live_presence]`
- `[whieda_room_chat]`

## Реестр страниц (общий)

| URL | Назначение | Доступ | Зависимые шорткоды/плагины |
| --- | --- | --- | --- |
| `/` | Главная страница | Публичный | — |
| `<URL>` | `<Назначение>` | `<Роль/доступ>` | `<шорткоды/модули>` |

## Реестр шорткодов (общий)

| Шорткод | Что делает | Где используется (страницы) | Модуль/плагин-источник |
| --- | --- | --- | --- |
| `<шорткод>` | `<описание>` | `<страницы>` | `<модуль>` |

Связанные документы:
- [SPEC/WEBINARS/ADMIN](ADMIN.md)
- [SPEC/WEBINARS/FLOWS](FLOWS.md)
- [CANON/ARCHITECTURE](../../CANON/ARCHITECTURE.md)
