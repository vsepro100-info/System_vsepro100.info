<?php
/**
 * Plugin Name: Core Ingest Observer
 * Description: Observer-only diagnostic logger for core_ingest_event.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('core_ingest_event', 'core_ingest_observer_log_event', 10, 1);

function core_ingest_observer_log_event(array $lead_meta) {
    $timestamp = current_time('c');
    error_log("[Core Ingest Observer] core_ingest_event received at {$timestamp}");
}
