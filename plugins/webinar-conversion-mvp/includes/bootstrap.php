<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('cta_clicked', 'webinar_conversion_mvp_handle_cta_clicked', 10, 1);
add_action('interest_confirmed', 'webinar_conversion_mvp_handle_interest_confirmed', 10, 1);
