<?php
/**
 * Plugin Name: Service Debug Listener
 * Description: Minimal listener for core_lead_ingest (no side effects beyond logging).
 * Version: 0.1.0
 */

defined('ABSPATH') || exit;

add_action('core_lead_ingest', 'service_debug_on_lead_ingest', 10, 1);

function service_debug_on_lead_ingest(array $lead_meta) {
    error_log('[service-debug] lead_ingest ' . json_encode($lead_meta));
}
