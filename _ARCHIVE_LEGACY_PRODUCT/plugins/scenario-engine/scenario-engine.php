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
add_action('client_webinar_completed', 'scenario_engine_dispatch_client_webinar', 10, 1);

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
    $ctx = is_array($ctx) ? $ctx : array();

    do_action(
        'scenario_start',
        'client_webinar',
        array(
            'event' => 'client_webinar_completed',
            'lead_id' => $ctx['lead_id'] ?? null,
            'webinar_id' => $ctx['webinar_id'] ?? null,
            'timestamp' => $ctx['timestamp'] ?? time(),
            'context' => $ctx,
        )
    );
}
