Status: BASELINE
Owner: Architect
Last updated: 2026-01-28

# BASELINE

## Назначение
Этот документ фиксирует фактическое состояние системы по данным KB и служит точкой отсчёта для последующих изменений.

## Текущее состояние системы

Фактический статус (без прогнозов):

- Knowledge Backbone: KB является единственным источником истины; структура KB включает CANON/PROCESS/SPEC/DRAFTS/ARCHIVE, зафиксирована в индексе.
- CANON: существует набор канонических документов (CANON, ARCHITECTURE, PRINCIPLES, DECISIONS_LOG, ROLES, MODULE_BOUNDARIES) со статусом CANON.
- PROCESS: присутствуют документы PROCESS (ARCHITECT_MODE) и DOCUMENT_TYPES.
- SPEC: присутствуют спецификации Webinars и runbooks (ADD_WEBINAR, TEST_RELEASE, DEPLOY).
- Webinars: в индексе KB указан статус «в разработке»; в CANON/ARCHITECTURE зафиксирован Webinar MVP как STABLE и реализованный Webinar Room/Public UI.
- Core Engine: в индексе KB Core отмечен как «заморожен»; Core описан как источник канонических сущностей ref/CRM.
- Referral System: описан канонический Referral Context (ref → wh_ref → invited_by); реализация/модуль — Не зафиксировано в KB.
- CRM / Leads: ref/CRM отмечены как работающие; канонические сущности лидов (lead_entry) и события core_lead_* описаны в CANON.
- Telegram Integration: зафиксированы сценарии и сервисы Telegram-уведомлений (welcome/follow-up, post-webinar) в CANON.
- Frontend / UX: зафиксированы правила UI для Webinar Room/Public/Admin, CTA и чат, а также использование только Core-данных для рендера.

Если по модулю нет достоверной информации — указано «Не зафиксировано в KB».

## Стабильные элементы
- Webinar MVP = STABLE (CANON/ARCHITECTURE).

## В разработке
- Webinars — в разработке (согласно 00_INDEX).

## Заморожено / отложено
- Core — заморожен (согласно 00_INDEX).

## Известные ограничения и риски
Только факты:
- Legacy Runtime (vsepro100.info) не переносим и используется как эталон поведения.
- Запреты: не трогать /signup/, не создавать вторую CRM, не объединять LIVE и AUTO.
- CANON утверждает: если факта нет в KB — его не существует в системе (риск пробелов в документации).

## Связанные документы
- [00_INDEX](00_INDEX.md)
- [CANON](CANON/CANON.md)
- [CANON/ARCHITECTURE](CANON/ARCHITECTURE.md)
- [CANON/PRINCIPLES](CANON/PRINCIPLES.md)
- [CANON/DECISIONS_LOG](CANON/DECISIONS_LOG.md)
- [CANON/ROLES](CANON/ROLES.md)
- [CANON/MODULE_BOUNDARIES](CANON/MODULE_BOUNDARIES.md)
- [PROCESS/ARCHITECT_MODE](PROCESS/ARCHITECT_MODE.md)
- [DOCUMENT_TYPES](DOCUMENT_TYPES.md)
- [SPEC/WEBINARS/OVERVIEW](SPEC/WEBINARS/OVERVIEW.md)
- [SPEC/WEBINARS/PAGES](SPEC/WEBINARS/PAGES.md)
- [SPEC/WEBINARS/FLOWS](SPEC/WEBINARS/FLOWS.md)
- [SPEC/WEBINARS/ADMIN](SPEC/WEBINARS/ADMIN.md)
- [SPEC/RUNBOOKS/ADD_WEBINAR](SPEC/RUNBOOKS/ADD_WEBINAR.md)
- [SPEC/RUNBOOKS/TEST_RELEASE](SPEC/RUNBOOKS/TEST_RELEASE.md)
- [SPEC/RUNBOOKS/DEPLOY](SPEC/RUNBOOKS/DEPLOY.md)
