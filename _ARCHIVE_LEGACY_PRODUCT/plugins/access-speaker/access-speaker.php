<?php
/**
 * Plugin Name: Speaker Access
 * Description: Добавляет роль Спикер и capability speaker для управления вебинарами и CTA.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

if (!defined('ACCESS_SPEAKER_PLUGIN_FILE')) {
    define('ACCESS_SPEAKER_PLUGIN_FILE', __FILE__);
}

require_once __DIR__ . '/includes/bootstrap.php';
