<?php

defined('ABSPATH') || exit;

function post_webinar_routing_service_handle_attendance($payload) {
    $payload = is_array($payload) ? $payload : array();

    if (!array_key_exists('attended', $payload)) {
        return;
    }

    $attended = (bool) $payload['attended'];

    $context = array(
        'lead_id' => $payload['lead_id'] ?? null,
        'webinar_id' => $payload['webinar_id'] ?? null,
        'attended' => $attended,
        'timestamp' => $payload['timestamp'] ?? null,
    );

    error_log(
        'post_webinar_routing_service: route ' .
        ($attended ? 'attended' : 'not_attended') .
        ' for webinar ' . ($context['webinar_id'] ?? '') .
        ' lead ' . ($context['lead_id'] ?? '')
    );

    do_action('post_webinar_route', $attended ? 'attended' : 'not_attended', $context);
}
