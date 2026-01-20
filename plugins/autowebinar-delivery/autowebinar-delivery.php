<?php
/**
 * Plugin Name: AutoWebinar Delivery
 * Description: Skeleton plugin for AutoWebinar delivery hooks (no logic).
 * Version: 0.1.0
 */

defined('ABSPATH') || exit;

add_action('init', 'autowebinar_delivery_init');

function autowebinar_delivery_init() {
    do_action('autowebinar_delivery_init');
}

function autowebinar_delivery_payload(array $payload) {
    do_action('autowebinar_delivery_payload', $payload);
}
