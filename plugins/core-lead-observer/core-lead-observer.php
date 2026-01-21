<?php
/**
 * Plugin Name: Core Lead Observer
 * Description: Observer-only diagnostic logger for core_lead_created.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('core_lead_created', 'core_lead_observer_log_event', 10, 1);

function core_lead_observer_log_event($lead_payload) {
    $timestamp = current_time('c');
    error_log("[Core Lead Observer] core_lead_created received at {$timestamp}");
}
