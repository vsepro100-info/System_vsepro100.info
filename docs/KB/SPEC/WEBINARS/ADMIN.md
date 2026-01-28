Status: SPEC
Owner: Architect
Last updated: 2026-01-28

# Webinars — Admin & Access

## Админская страница вебинара
- URL: `/admin_webinar/`
- Shortcode: `[whieda_live_admin]`
- Доступ: `edit_pages`

### Функции
- Заголовок
- Дата/время
- Постер
- iframe плеера
- iframe чата
- CTA

### Канон UX (из архитектуры)
- `/admin_webinar/` по умолчанию открывается в режиме Speaker UI.
- Спикер видит только бизнес‑поля и не может сломать конфигурацию вебинара.
- Технические поля спрятаны за настройками только для admin (иконка "gear").

## Роли и доступы (Webinars)
- **Guest (неавторизован):** видит публичную страницу `/webinars/`, не попадает в `/account/webinar_room/`.
- **Candidate (авторизован, не партнёр):** может войти в вебинар, видит презентацию, имеет CTA “Связаться со спонсором”, не имеет доступа к обучению.
- **Partner:** всё выше + записи вебинаров (позже).

## Правила доступа и каноны
- Отдельной роли "moderator" не существует (см. [CANON/ROLES](../../CANON/ROLES.md)).
- Права определяются через capabilities, а не через добавление новых ролей.
- CTA вебинара — контекстное состояние, управляется только через Core hook `core_webinar_set_cta_visibility`.

Связанные документы:
- [CANON/ARCHITECTURE](../../CANON/ARCHITECTURE.md)
- [CANON/ROLES](../../CANON/ROLES.md)
- [SPEC/WEBINARS/PAGES](PAGES.md)
