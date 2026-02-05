<?php

defined('ABSPATH') || exit;

function autowebinar_delivery_init() {
    do_action('autowebinar_delivery_init');
}

function autowebinar_delivery_payload(array $payload) {
    do_action('autowebinar_delivery_payload', $payload);
}

function autowebinar_delivery_handle_ingest($event) {
}
