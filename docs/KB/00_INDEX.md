Status: CANON
Owner: Architect
Last updated: 2026-01-28

# Knowledge Backbone — Index

## Краткое описание проекта
System_vsepro100.info — модульная WordPress‑система, где Core фиксирует канонические сущности и события, а надстройки (включая Webinars) работают через контракты и hooks.

## Текущий статус
- CORE — заморожен.
- Webinars — в разработке.

## Быстрые входы (entry‑points)
- **Старт нового модуля** → [TEMPLATE_NEW_MODULE](TEMPLATE_NEW_MODULE.md) → [CANON/MODULE_BOUNDARIES](CANON/MODULE_BOUNDARIES.md) → [CANON/DECISIONS_LOG](CANON/DECISIONS_LOG.md).
- **Доработка Webinars** → [SPEC/WEBINARS/OVERVIEW](SPEC/WEBINARS/OVERVIEW.md) → [SPEC/WEBINARS/PAGES](SPEC/WEBINARS/PAGES.md) → [SPEC/WEBINARS/FLOWS](SPEC/WEBINARS/FLOWS.md).
- **Эксперимент** → [DRAFTS/IDEAS](DRAFTS/IDEAS.md) (фиксировать гипотезу) → [CANON/DECISIONS_LOG](CANON/DECISIONS_LOG.md) при утверждении.
- **Рефакторинг** → [CANON/ARCHITECTURE](CANON/ARCHITECTURE.md) → [CANON/MODULE_BOUNDARIES](CANON/MODULE_BOUNDARIES.md) → [SPEC/RUNBOOKS/TEST_RELEASE](SPEC/RUNBOOKS/TEST_RELEASE.md).

## Навигация по слоям
- **CANON (истина проекта)**
  - [CANON](CANON/CANON.md)
  - [ARCHITECTURE](CANON/ARCHITECTURE.md)
  - [PRINCIPLES](CANON/PRINCIPLES.md)
  - [DECISIONS_LOG](CANON/DECISIONS_LOG.md)
  - [ROLES](CANON/ROLES.md)
  - [BOOTSTRAP_CONTRACT](CANON/BOOTSTRAP_CONTRACT.md)
  - [EVENT_HOOK_MAP](CANON/EVENT_HOOK_MAP.md)
  - [PERMISSIONS_ROLES](CANON/PERMISSIONS_ROLES.md)
  - [PUBLIC_CONTRACTS](CANON/PUBLIC_CONTRACTS.md)
  - [STATE_MODEL](CANON/STATE_MODEL.md)
  - [MODULE_BOUNDARIES](CANON/MODULE_BOUNDARIES.md)

- **PROCESS (как работает архитектор)**
  - [ARCHITECT_MODE](PROCESS/ARCHITECT_MODE.md)
  - [DOCUMENT_TYPES](DOCUMENT_TYPES.md)

- **SPEC / RUNBOOKS (как действовать)**
  - [WEBINARS/OVERVIEW](SPEC/WEBINARS/OVERVIEW.md)
  - [WEBINARS/PAGES](SPEC/WEBINARS/PAGES.md)
  - [WEBINARS/FLOWS](SPEC/WEBINARS/FLOWS.md)
  - [WEBINARS/ADMIN](SPEC/WEBINARS/ADMIN.md)
  - [RUNBOOKS/ADD_WEBINAR](SPEC/RUNBOOKS/ADD_WEBINAR.md)
  - [RUNBOOKS/TEST_RELEASE](SPEC/RUNBOOKS/TEST_RELEASE.md)
  - [RUNBOOKS/DEPLOY](SPEC/RUNBOOKS/DEPLOY.md)

- **DRAFTS (идеи и сырьё)**
  - [IDEAS](DRAFTS/IDEAS.md)
  - [LANDING_NOTES](DRAFTS/LANDING_NOTES.md)
  - [SYSTEM_THOUGHTS](DRAFTS/SYSTEM_THOUGHTS.md)

- **ARCHIVE / LEGACY (история)**
  - [LEGACY_DOCS](ARCHIVE/LEGACY_DOCS.md)
  - [MIGRATED_NOTES](ARCHIVE/MIGRATED_NOTES.md)

## Правило канона
- CANON — высший приоритет.
- PROCESS — регламент работы архитектора (не истина проекта).
- Если факта нет в KB — его не существует в системе.
