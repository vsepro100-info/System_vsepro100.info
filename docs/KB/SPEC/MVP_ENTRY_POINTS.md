Status: SPEC
Owner: Architect
Last updated: 2026-01-28

# Webinars — MVP Entry Points

## Назначение
Зафиксировать **минимальный исполняемый набор entry points** для Webinars MVP: публичные WordPress hooks и REST‑вход (если используется), строго в рамках текущего runtime‑контура.

## Источники истины
- [SPEC/MVP_RUNTIME_BOUNDARIES](MVP_RUNTIME_BOUNDARIES.md)
- [CANON/EVENT_HOOK_MAP](../CANON/EVENT_HOOK_MAP.md)
- [CANON/STATE_MODEL](../CANON/STATE_MODEL.md)
- [CANON/PUBLIC_CONTRACTS](../CANON/PUBLIC_CONTRACTS.md)
- [CANON/API_CONTRACTS](../CANON/API_CONTRACTS.md)
- [CANON/PERMISSIONS_ROLES](../CANON/PERMISSIONS_ROLES.md)

## Контур MVP (только реально исполняемые entry points)
> В список включены **только** entry points, которые реально исполняются в MVP runtime (LIVE‑вебинар + post‑webinar).

### 1) WordPress hooks (actions)

| Тип | Имя | Инициатор (роль/модуль) | Назначение | Затрагиваемые модули |
| --- | --- | --- | --- | --- |
| hook | `core_webinar_set_status` | `speaker`/`organizer` → Client Webinar Control Integration | Канонический старт/стоп вебинара через Core. | Core, Client Webinar Control Integration, Webinar Room UI (косвенно) |
| hook | `client_webinar_entered` | `attendee` → Client Webinar Tracker v2 | Факт входа участника в вебинар. | Client Webinar Tracker v2, Client Webinar Attendance Service |
| hook | `client_webinar_completed` | `attendee` → Client Webinar Tracker v2 | Факт завершения участия. | Client Webinar Tracker v2, Client Webinar Event Emitter, Client Webinar Attendance Service |
| hook | `webinar_completed` | Client Webinar Event Emitter | Нормализованный downstream‑факт завершения участия. | Client Webinar Event Emitter, Client Webinar Attendance Service |
| hook | `client_webinar_attendance_classified` | Client Webinar Attendance Service | Классификация посещения (attended/not_attended). | Client Webinar Attendance Service, Client Webinar Attendance Telegram, Post Webinar Routing Service |
| hook | `post_webinar_route` | Post Webinar Routing Service | Маршрут post‑webinar сценария (attended/not_attended). | Post Webinar Routing Service, Post Webinar Follow‑up Telegram, Post Webinar Recording Follow‑up Telegram |

### 2) REST entry point (если используется в MVP)

| Тип | Имя | Инициатор (роль/модуль) | Назначение | Затрагиваемые модули |
| --- | --- | --- | --- | --- |
| REST | Webinar Room State Sync (канонический REST контракт, путь не зафиксирован) | `attendee`/`speaker`/`organizer` → Webinar Room UI | Read‑only синхронизация состояния комнаты (status/CTA/минимальные атрибуты сессии). | Webinar Room UI, Core |

> Примечание: REST‑синхронизация **не** изменяет статус/CTA и не заменяет hooks. Любые write‑операции выполняются через публичные hooks.

---

## Связь entry points с state transitions (happy‑path)

| Entry point | Переход состояния (CANON) | Happy‑path описание |
| --- | --- | --- |
| `core_webinar_set_status` (`start`) | `scheduled` → `live` | Спикер/админ инициирует старт; Core фиксирует переход в `live`.
| `core_webinar_set_status` (`stop`) | `live/paused` → `finished` | Спикер/админ инициирует остановку; Core фиксирует завершение. |
| `client_webinar_entered` | (нет смены статуса) | Участник входит в комнату в состоянии `live`; фиксируется факт участия. |
| `client_webinar_completed` → `webinar_completed` | (нет смены статуса) | Участник завершает участие; downstream‑событие нормализуется. |
| `client_webinar_attendance_classified` | (post‑webinar) | После завершения участия фиксируется классификация посещения. |
| `post_webinar_route` | (post‑webinar) | На основе классификации определяется маршрут follow‑up. |
| REST Room State Sync | (нет смены статуса) | UI получает актуальное состояние комнаты для отображения. |

---

## Permissions enforcement (happy‑path)

| Entry point | Разрешённые роли (CANON) | Контроль доступа (happy‑path) |
| --- | --- | --- |
| `core_webinar_set_status` | `organizer`, `speaker` | Проверка роли и допустимого статуса (`start`/`stop`); запрет прямых изменений статуса. |
| `client_webinar_entered` | `attendee`/`system` | Эмиссия только из контекста участия; гость не может инициировать событие. |
| `client_webinar_completed` | `attendee`/`system` | Эмиссия после корректного участия; гость не может инициировать событие. |
| `webinar_completed` | `system` | Нормализация выполняется сервисом‑эмиттером; внешний инициатор отсутствует. |
| `client_webinar_attendance_classified` | `system` | Классификация выполняется сервисом и не зависит от внешней роли. |
| `post_webinar_route` | `system` | Роутинг выполняется сервисом после классификации. |
| REST Room State Sync | `attendee`, `speaker`, `organizer` | Требуется аутентифицированный контекст; только чтение состояния. |

---

## Явные исключения (не в MVP)
- AutoWebinar‑entry points (`autowebinar_*`, `core_user_*`) исключены из MVP runtime.
- Не реализованные client‑events (`client_webinar_registered`, `client_webinar_left`, `client_webinar_form_submitted`) исключены.
- Любые REST write‑операции (изменение статуса/CTA) исключены; допускается только read‑sync.
