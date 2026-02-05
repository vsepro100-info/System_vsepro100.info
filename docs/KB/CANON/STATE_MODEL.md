Status: CANON
Owner: Architect
Last updated: 2026-01-28

# Webinars — Canonical State Model

## Назначение
Зафиксировать канонические состояния вебинара и допустимые переходы между ними. Модель опирается на **Event/Hook Map** и **Public Contracts** и не описывает реализацию или UI.

## Источники и приоритет
- Истина событий и инициаторов: [CANON/EVENT_HOOK_MAP](EVENT_HOOK_MAP.md)
- Публичные контракты: [CANON/PUBLIC_CONTRACTS](PUBLIC_CONTRACTS.md)

## Состояния (lifecycle)
| Состояние | Смысл | Инварианты |
| --- | --- | --- |
| `draft` | Черновик вебинара, ещё не объявлен. | Нет публичного слота; старт запрещён. |
| `scheduled` | Запланирован, есть дата/время и слот. | Определены время и тип (live/auto), публичная регистрация разрешена. |
| `live` | Идёт живой эфир. | Доступ в комнату открыт; CTA управляется только через Core. |
| `paused` | Временная пауза эфира. | Нельзя завершать участие клиентов как «completed» без возобновления или явного `stop`. |
| `finished` | Эфир завершён. | Новые входы в live‑комнату запрещены; допускаются post‑webinar события. |
| `archived` | Архив, историческое хранение. | Никаких изменений статуса, только чтение/аналитика. |

## Переходы между состояниями
> Принцип: внешние модули не меняют статус напрямую. Единственный публичный вход на старт/стоп — `core_webinar_set_status`.

| Откуда → Куда | Триггер (event) | Инициатор (модуль/роль) | Инварианты |
| --- | --- | --- | --- |
| `draft` → `scheduled` | Внутреннее действие Core (планирование вебинара). | Core (admin/speaker UI через Core). | Заполнены дата/время, тип вебинара, публичный слот опубликован. |
| `scheduled` → `live` | `core_webinar_set_status(webinar_id, start, context)` | Speaker/Admin UI → client-webinar-control-integration → Core | Вебинар не архивирован, слот активен, доступ в комнату разрешён. |
| `live` → `paused` | Внутреннее действие Core (техническая пауза). | Core runtime / speaker action (не публичный hook). | Пауза не завершает участие клиента; время эфира сохраняется. |
| `paused` → `live` | Внутреннее действие Core (возобновление). | Core runtime / speaker action (не публичный hook). | Возврат возможен только если не было `stop`. |
| `live` → `finished` | `core_webinar_set_status(webinar_id, stop, context)` | Speaker/Admin UI → client-webinar-control-integration → Core | Завершение фиксируется один раз; post‑webinar события допустимы. |
| `paused` → `finished` | `core_webinar_set_status(webinar_id, stop, context)` | Speaker/Admin UI → client-webinar-control-integration → Core | Пауза не отменяет стоп; завершение фиксируется один раз. |
| `finished` → `archived` | Внутреннее действие Core (архивация). | Core (admin). | Архивирование возможно только после `finished`. |

## Запрещённые переходы и ошибки
| Переход / действие | Почему запрещено | Каноническая ошибка/реакция |
| --- | --- | --- |
| `draft` → `live` | Старт без расписания нарушает канон live‑формата. | Ошибка `webinar_state_invalid_start`; требовать `scheduled`. |
| `scheduled` → `archived` | Нельзя архивировать без проведения и фиксации завершения. | Ошибка `webinar_state_invalid_archive`. |
| `live` → `scheduled` | Нельзя откатывать эфир в планирование. | Ошибка `webinar_state_invalid_rewind`. |
| `finished` → `live` | Повторный старт без нового слота. | Ошибка `webinar_state_invalid_restart`. |
| Любой прямой статус‑update вне `core_webinar_set_status` | Нарушение публичного контракта. | Ошибка `webinar_status_mutation_forbidden`; изменения отклоняются. |
| Любые изменения статуса после `archived` | Архив неизменяем. | Ошибка `webinar_state_locked`. |

## Примечания
- `core_webinar_set_status` принимает только `start` и `stop`; любые другие значения статуса отклоняются на уровне Core. 
- UI/интеграции не изменяют состояние напрямую — только через публичный hook. 
- Пауза (`paused`) — внутреннее состояние Core и не расширяет публичный контракт.

Связанные документы:
- [CANON/EVENT_HOOK_MAP](EVENT_HOOK_MAP.md)
- [CANON/PUBLIC_CONTRACTS](PUBLIC_CONTRACTS.md)
- [SPEC/WEBINARS/OVERVIEW](../SPEC/WEBINARS/OVERVIEW.md)
