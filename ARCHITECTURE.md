# Архитектура

## Принципы
- Модули независимы.
- Взаимодействие между модулями — только через hooks и контракты.
- Core создаёт канонические сущности и источники правды (ref/CRM).
- Канонический жизненный цикл лидов использует только `core_ingest_event` и события `core_lead_*`.
- Каноническая сущность лида: `lead_entry` (internal-only CPT).

## Pre-Dialog Behavioral Signals
- Auto-detected country is allowed as a context signal.
- Allowed sources for auto-detected country: IP, timezone, locale.
- Country-level only; city or precise location is explicitly forbidden.
- Priority: self-reported country overrides auto-detected country.
- Notes:
  - Used for chat flags.
  - Used for CRM context.
  - No interpretations are allowed.

## Legacy Runtime
- Legacy Runtime (vsepro100.info) не переносим.
- Используем его как эталон поведения и валидации решений.

## Сценарии клиентского вебинара
- Это отдельный класс сценариев.
- Целевая аудитория: лид (клиент), а не партнёр.
- Цель: посещаемость вебинара, удержание и действия после вебинара.
- Основаны на поведенческих событиях, а не только на времени.

Канонические клиентские события:
- webinar_registered
- webinar_entered
- webinar_left
- webinar_completed
- post_webinar_form_submitted

Требования разделения:
- Сценарии клиентского вебинара НЕ должны уведомлять партнёров.
- Партнёрские уведомления обрабатываются отдельно.

Примечание по Webinar Layer:
- Архитектурно утверждён.
- Аудит пройден.
- Реализация нейтральна; на текущем этапе — только события.
- Ссылка: WEBINAR_ARCHITECTURE.md.
