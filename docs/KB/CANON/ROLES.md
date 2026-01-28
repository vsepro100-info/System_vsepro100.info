Status: CANON
Owner: Architect
Last updated: 2026-01-28

# Роли

## Каноническая модель ролей (системный уровень)
Иерархия ролей:
1) admin
2) speaker (trusted leader, расширенные возможности)
3) leader
4) partner
5) candidate

Правила:
- Отдельной роли "moderator" не существует.
- Роль speaker — системная доверенная роль для управления вебинарами, CTA и расписанием.
- Роль speaker назначается через WP Users (не через UI вебинара) администратором.
- Администраторы всегда имеют доступ speaker через capability `speaker`.
- Права определяются через capabilities, а не через добавление новых ролей.
- Права speaker выдаются через набор `webinar_*` capabilities и не зависят от конкретного вебинара.

Примеры capabilities для роли speaker (не исчерпывающий список):
- speaker (канонический доступ для вебинаров и CTA)
- webinar_manage_chat
- webinar_manage_status
- approve_users / approve_profiles (опционально, флагами)
- другие доверенные действия в будущем

## Канон вебинарного спикера (контекст, не роль)
- "Host / speaker" вебинара — это не роль.
- Это контекст вебинара: `webinar.speaker_id`.
- Все пользователи с ролью speaker сохраняют права модерации вне зависимости от `webinar.speaker_id`.

## Роли процесса
- Архитектор.
- Codex.
- Тестировщик.

Связанные документы:
- [ARCHITECT_MODE](../PROCESS/ARCHITECT_MODE.md)
- [SPEC/WEBINARS/ADMIN](../SPEC/WEBINARS/ADMIN.md)
