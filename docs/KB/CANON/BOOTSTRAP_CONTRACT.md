Status: CANON
Owner: Architect
Last updated: 2026-01-28

# Bootstrap Contract (Webinars Modules)

## Назначение
Единый контракт bootstrap фиксирует минимальный и повторяемый скелет модулей Webinars.
Bootstrap отвечает только за wiring: регистрацию hooks/shortcodes/filters и подключение файлов.

## Базовые правила
- Один модуль = один bootstrap (`plugins/<module>/includes/bootstrap.php`).
- Entry‑файл плагина (`plugins/<module>/<module>.php`) не содержит логики и вызывает только bootstrap.
- Bootstrap не содержит бизнес‑логики и не определяет обработчики; только подключает файлы и регистрирует hooks.
- Запрещены прямые `require/include` между модулями.

## Канонический шаблон

**Entry‑файл (`plugins/<module>/<module>.php`):**
```php
<?php
/**
 * Plugin Name: <Module Name>
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/includes/bootstrap.php';
```

**Bootstrap (`plugins/<module>/includes/bootstrap.php`):**
```php
<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('init', 'module_register_hooks');
add_shortcode('module_shortcode', 'module_render_shortcode');
```

**Файл логики (`plugins/<module>/includes/module.php`):**
```php
<?php

defined('ABSPATH') || exit;

function module_register_hooks() {
    // только подготовка регистраций
}

function module_render_shortcode() {
    // обработчик
}
```

## Применение
Контракт обязателен для всех модулей Webinars, перечисленных в `CANON/MODULE_BOUNDARIES`.
