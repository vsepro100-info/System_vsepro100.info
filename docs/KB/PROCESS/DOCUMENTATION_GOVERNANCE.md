# Назначение документа
Зафиксировать обязательные правила ведения процессной документации и фиксации решений в репозитории.

# Роли и ответственность
- **Product Owner / Documentation Owner**: определяет, где фиксируется решение; принимает результат; даёт OK на merge.
- **Codex (исполнитель)**: оформляет изменения в документации и готовит PR.
- **Инвестор / Заказчик (контроль)**: контролирует соблюдение правил и полноту фиксации.

Ответственность за структуру, порядок,
актуальность и целостность документации проекта
несёт Product Owner / Documentation Owner.

Только Product Owner принимает решения:
— что подлежит фиксации;
— в каком документе фиксируется решение;
— требуется ли создание нового документа или правка существующего.

Отсутствие одобрения Product Owner означает,
что изменения в документации не считаются принятыми.

# Базовые принципы документации
- **Чат ≠ Истина**: решения в чате не считаются зафиксированными.
- **Истина существует только в репозитории**.

# Stage Gate: Chat → Repository
Если в чате принято решение «фиксируем», движение дальше запрещено до фиксации в репозитории.

# Обязательный workflow фиксации решений
1. Решение принято в чате.
2. Product Owner определяет, **где фиксируется** (какой документ).
3. Формируется задача Codex.
4. Codex готовит PR.
5. Product Owner проверяет и даёт OK.
6. Только после merge этап считается завершённым.

# Правило «Один PR — один этап фиксации»
Каждый этап фиксации оформляется отдельным PR.

# Product Owner command format (concise)
- Chat messages must contain only:
  - PR verdict (MERGE APPROVED / STOP)
  - Next Codex command block
- No meta commentary, no step narration, no explanations.
- This rule applies across chats and sessions.

# Правило немедленной фиксации этапа
Любой этап работы считается завершённым только после фиксации
принятых решений в документации репозитория и merge соответствующего PR.

Если в рабочем чате явно принято решение
(например: «фиксируем», «принимаем», «это правило», «так и делаем»),
движение к следующему этапу или переход в новый чат
ЗАПРЕЩЁН до момента фиксации этого решения в документации.

Обсуждение без последующей фиксации не даёт права
считать этап завершённым.

Нарушение этого правила является основанием для STOP процесса.

# Запрет
- фиксировать решения только в чате;
- начинать новый этап без merge документации.

# Docs-only PR Review Rule
- If a pull request is docs-only, affects no more than 2–3 files, has a micro-diff, and touches only ARCHIVE/RAW materials or document headers, a structured Codex report is sufficient for Product Owner approval.
- Any changes to CANON, PROCESS, SYSTEM_STATE, or DECISIONS_LOG require a patch file or full document text for review.

# Documentation task scope, literal execution, and language rules
- For documentation tasks, Product Owner must explicitly specify:
  - target document
  - exact scope of change
  - language
- Codex must apply changes literally, without rewording, additions, or expansion beyond explicit Product Owner instructions.
- Any deviation requires STOP and clarification.
- PROCESS documents are ENGLISH ONLY (strict).
- Any language violation is grounds for STOP.

## Mandatory literal-doc-instructions template (PO → Codex)
Product Owner must use the template below for documentation tasks. Codex must apply the template verbatim and nothing else.

```
TARGET DOCUMENT:
LANGUAGE:
EXACT TEXT (verbatim):
INSERT LOCATION:
STOP CONDITIONS:
```
