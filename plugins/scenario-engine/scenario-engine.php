<?php
/**
 * Plugin Name: Scenario Engine
 * Description: Диспетчер сценариев на основе канонических событий Core
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('core_lead_created', 'scenario_engine_dispatch', 10, 2);
add_action('client_webinar_entered', 'scenario_engine_dispatch_client_webinar', 10, 1);
add_action('client_webinar_completed', 'scenario_engine_dispatch_client_webinar', 10, 1);
add_action('client_webinar_form_submitted', 'scenario_engine_dispatch_client_webinar', 10, 1);

function scenario_engine_dispatch(int $lead_id, array $payload) {
    if (empty($lead_id) || !is_numeric($lead_id)) {
        return;
    }

    if (!is_array($payload)) {
        return;
    }

    $source = $payload['source'] ?? '';

    if ($source !== 'web_form') {
        return;
    }

    do_action(
        'scenario_start',
        'welcome',
        array(
            'lead_id' => (int) $lead_id,
            'payload' => $payload,
        )
    );
}

function scenario_engine_dispatch_client_webinar($ctx) {
    if (!is_array($ctx)) {
        return;
    }

    $event = current_action();
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $trace = function_exists('wp_debug_backtrace_summary')
        ? wp_debug_backtrace_summary(null, 8, false)
        : 'no_backtrace';

    error_log('[scenario-engine] webinar_event=' . $event . ' uri=' . $uri . ' trace=' . $trace);

    do_action(
        'scenario_start',
        'client_webinar',
        array(
            'event' => $event,
            'lead_id' => (int) ($ctx['lead_id'] ?? 0),
            'webinar_id' => (string) ($ctx['webinar_id'] ?? ''),
            'timestamp' => (int) ($ctx['timestamp'] ?? time()),
            'context' => is_array($ctx) ? $ctx : array(),
        )
    );
}
