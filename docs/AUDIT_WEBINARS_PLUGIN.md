# Webinars Plugin — Audit & Skeleton Normalization

## Scope
- Audit only: structure and module layout for Webinars-related plugins.
- No behavior/logic changes; only file layout and bootstrap wiring.

---

## Канонический список модулей (KB/SPEC)
Модули Webinars, закреплённые в KB/SPEC и соответствующие каноническим границам:

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
- `ui-webinar-entry/`
- `ui-webinar-public/`
- `ui-webinar-room/`
- `webinar-chat/`

## Entrypoints (единый скелет)
Для каждого канонического модуля:
```
plugins/<module>/
  <module>.php            # заголовок плагина + загрузчик
  includes/
    bootstrap.php         # entrypoint модуля (без изменения логики)
```

## Псевдомодули / вне канона (без удаления логики)
Эти плагины присутствуют в `plugins/`, но не входят в список канонических модулей Webinars в KB/SPEC:

- `access-speaker/` — служебная роль/доступ для управления вебинарами.
- `scenario-client-webinar-telegram/` — узкоцелевой сценарный модуль (legacy), не описан в KB/SPEC.

---

## Итог
- Канонический список модулей зафиксирован.
- Entrypoints унифицированы (`includes/bootstrap.php`) без изменения поведения.
- Псевдомодули помечены, логика сохранена.
