<?php
/**
 * Plugin Name: Core Lead Observer
 * Description: Записывает минимальные метрики при создании лида через core_lead_created.
 * Version: 0.1.2
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('core_lead_created', 'core_lead_observer_record_basic_metrics', 10, 2);

function core_lead_observer_record_basic_metrics($lead_id, $lead_meta = null) {
    if (is_array($lead_id) && $lead_meta === null) {
        $lead_meta = $lead_id;
        $lead_id = $lead_meta['lead_id'] ?? null;
    }

    if (empty($lead_id) || !is_numeric($lead_id)) {
        return;
    }

    if (!is_array($lead_meta)) {
        $lead_meta = array();
    }

    $lead_id = (int) $lead_id;
    $timestamp = time();
    $source = $lead_meta['source'] ?? null;

    $meta_updates = array(
        'lead_source' => $source,
        'first_touch_at' => $timestamp,
        'lead_created_at' => $timestamp,
        'lead_created_via' => 'core_ingest',
    );

    foreach ($meta_updates as $meta_key => $meta_value) {
        if (metadata_exists('post', $lead_id, $meta_key)) {
            continue;
        }

        update_post_meta($lead_id, $meta_key, $meta_value);
    }
}
