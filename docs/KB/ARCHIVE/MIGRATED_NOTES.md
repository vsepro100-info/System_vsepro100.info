# Mиграционные заметки

Этот файл фиксирует, почему канонические документы были перенесены в архив
или заменены новой канонической записью.

## Переносы из корня репозитория

| Документ | Новое место | Причина | Каноническая замена |
| --- | --- | --- | --- |
| `ROADMAP.md` | `docs/KB/ARCHIVE/ROADMAP.md` | Исторический план больше не отражает текущую работу. | Статус и приоритеты фиксируются через KB (INDEX, MODULES_STATUS, CANON/DECISIONS_LOG). |
| `SYSTEM_STATE.md` | `docs/KB/ARCHIVE/SYSTEM_STATE.md` | Состояние системы устарело и содержит снимок прошлого. | Актуальный статус фиксируется в KB (INDEX, KNOWN_ISSUES, MODULES_STATUS). |
| `CONTRACTS.md` | `docs/KB/ARCHIVE/CONTRACTS.md` | Контракты описаны как исторический срез. | Текущие решения закрепляются через KB и отдельные канонические документы при появлении. |
| `WEBINAR_ARCHITECTURE.md` | `docs/KB/ARCHIVE/WEBINAR_ARCHITECTURE.md` | Архитектурное описание устарело и дублируется новыми KB-решениями. | Канон: KB (CANON/DECISIONS_LOG, ARCHITECT_MODE) и актуальные регистры страниц/шорткодов. |

## Архив ADR

ADR-файлы перенесены в архив как исторический источник. Ключевые решения перенесены в канонический лог решений KB.

| ADR | Новое место | Каноническая замена |
| --- | --- | --- |
| `ADR-001-live-vs-auto-webinars.md` | `docs/KB/ARCHIVE/ADR-001-live-vs-auto-webinars.md` | `docs/KB/CANON/DECISIONS_LOG.md` (строка про LIVE/AUTO). |
