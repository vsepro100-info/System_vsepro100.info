<?php
/**
 * Plugin Name: Core Ingest Observer
 * Description: Observer-only diagnostic logger for core_ingest_event.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

define('CORE_INGEST_OBSERVER_METRICS_OPTION', 'core_ingest_observer_metrics');

add_action('core_ingest_event', 'core_ingest_observer_log_event', 10, 1);

function core_ingest_observer_log_event(array $lead_meta) {
    $timestamp = current_time('c');
    $metrics = get_option(
        CORE_INGEST_OBSERVER_METRICS_OPTION,
        array(
            'total' => 0,
            'by_subtype' => array(),
            'last_ingest' => null,
        )
    );

    if (!is_array($metrics)) {
        $metrics = array(
            'total' => 0,
            'by_subtype' => array(),
            'last_ingest' => null,
        );
    }

    $metrics['total'] = isset($metrics['total']) ? (int) $metrics['total'] + 1 : 1;
    $metrics['by_subtype'] = isset($metrics['by_subtype']) && is_array($metrics['by_subtype'])
        ? $metrics['by_subtype']
        : array();

    $subtype = 'unknown';
    if (!empty($lead_meta['subtype']) && is_string($lead_meta['subtype'])) {
        $subtype = $lead_meta['subtype'];
    } elseif (!empty($lead_meta['source']) && is_string($lead_meta['source'])) {
        $subtype = $lead_meta['source'];
    } elseif (!empty($lead_meta['ingest_subtype']) && is_string($lead_meta['ingest_subtype'])) {
        $subtype = $lead_meta['ingest_subtype'];
    }

    if (!isset($metrics['by_subtype'][$subtype])) {
        $metrics['by_subtype'][$subtype] = 0;
    }

    $metrics['by_subtype'][$subtype] = (int) $metrics['by_subtype'][$subtype] + 1;
    $metrics['last_ingest'] = $timestamp;

    update_option(CORE_INGEST_OBSERVER_METRICS_OPTION, $metrics, false);
    error_log("[Core Ingest Observer] core_ingest_event received at {$timestamp}");
}
