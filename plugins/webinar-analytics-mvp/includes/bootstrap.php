<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('webinar_started', 'webinar_analytics_mvp_handle_webinar_started', 10, 1);
add_action('user_attended', 'webinar_analytics_mvp_handle_user_attended', 10, 1);
add_action('webinar_finished', 'webinar_analytics_mvp_handle_webinar_finished', 10, 1);
add_action('cta_shown', 'webinar_analytics_mvp_handle_cta_shown', 10, 1);
add_action('cta_clicked', 'webinar_analytics_mvp_handle_cta_clicked', 10, 1);
add_action('webinar_ref_attribution', 'webinar_analytics_mvp_handle_ref_attribution', 10, 1);
