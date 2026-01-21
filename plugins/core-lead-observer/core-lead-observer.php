<?php
/**
 * Plugin Name: Core Lead Observer
 * Description: Технический диагностический логгер для core_lead_created.
 * Version: 0.1.1
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

define('CORE_LEAD_OBSERVER_METRICS_OPTION', 'core_lead_observer_metrics');

add_action('core_lead_created', 'core_lead_observer_log_event', 10, 1);

function core_lead_observer_log_event($lead_payload) {
    $timestamp = current_time('c');
    $metrics = get_option(
        CORE_LEAD_OBSERVER_METRICS_OPTION,
        array(
            'total' => 0,
            'last_lead_created' => null,
        )
    );

    if (!is_array($metrics)) {
        $metrics = array(
            'total' => 0,
            'last_lead_created' => null,
        );
    }

    $metrics['total'] = isset($metrics['total']) ? (int) $metrics['total'] + 1 : 1;
    $metrics['last_lead_created'] = $timestamp;

    update_option(CORE_LEAD_OBSERVER_METRICS_OPTION, $metrics, false);
    error_log("[Core Lead Observer] core_lead_created received at {$timestamp}");
}
