<?php
/**
 * Plugin Name: AutoWebinar Delivery
 * Description: Технический каркас хуков доставки AutoWebinar.
 * Version: 0.1.1
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('init', 'autowebinar_delivery_init');
add_action('core_ingest_event', 'autowebinar_delivery_handle_ingest', 10, 1);

function autowebinar_delivery_init() {
    do_action('autowebinar_delivery_init');
}

function autowebinar_delivery_payload(array $payload) {
    do_action('autowebinar_delivery_payload', $payload);
}

function autowebinar_delivery_handle_ingest($event) {
}
