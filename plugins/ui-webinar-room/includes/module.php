<?php

defined('ABSPATH') || exit;

function ui_webinar_room_register_state_routes() {
    register_rest_route(
        'webinar/v1',
        '/state',
        array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => 'ui_webinar_room_handle_state_read',
                'permission_callback' => 'ui_webinar_room_can_read_state',
            ),
        )
    );

    register_rest_route(
        'webinar/v1',
        '/webinars',
        array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => 'ui_webinar_room_handle_webinars_list',
                'permission_callback' => 'ui_webinar_room_can_read_state',
            ),
        )
    );

    register_rest_route(
        'webinar/v1',
        '/webinars/(?P<id>\\d+)/state',
        array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => 'ui_webinar_room_handle_webinar_state_read',
                'permission_callback' => 'ui_webinar_room_can_read_state',
            ),
        )
    );

    register_rest_route(
        'webinar/v1',
        '/webinars/(?P<id>\\d+)/info',
        array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => 'ui_webinar_room_handle_webinar_info_read',
                'permission_callback' => 'ui_webinar_room_can_read_state',
            ),
        )
    );

    register_rest_route(
        'webinar/v1',
        '/webinars/(?P<id>\\d+)/cta',
        array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => 'ui_webinar_room_handle_cta_event',
                'permission_callback' => 'ui_webinar_room_can_emit_cta_event',
            ),
        )
    );
}

/**
 * @param WP_REST_Request $request
 * @return bool|WP_Error
 */
function ui_webinar_room_can_read_state($request) {
    if (strtoupper($request->get_method()) !== 'GET') {
        return new WP_Error('webinar_rest_method_not_allowed', 'Method not allowed', array('status' => 405));
    }

    if (!is_user_logged_in()) {
        error_log('ui_webinar_room_rest: denied unauthenticated access');
        return new WP_Error('webinar_rest_unauthorized', 'Authentication required', array('status' => 401));
    }

    if (ui_webinar_room_get_actor_role() === '') {
        error_log('ui_webinar_room_rest: denied access for user ' . (int) get_current_user_id());
        return new WP_Error('webinar_rest_forbidden', 'Forbidden', array('status' => 403));
    }

    return true;
}

/**
 * @param WP_REST_Request $request
 * @return bool|WP_Error
 */
function ui_webinar_room_can_emit_cta_event($request) {
    if (strtoupper($request->get_method()) !== 'POST') {
        return new WP_Error('webinar_rest_method_not_allowed', 'Method not allowed', array('status' => 405));
    }

    if (!is_user_logged_in()) {
        error_log('ui_webinar_room_rest: denied unauthenticated CTA emit');
        return new WP_Error('webinar_rest_unauthorized', 'Authentication required', array('status' => 401));
    }

    if (ui_webinar_room_get_actor_role() === '') {
        error_log('ui_webinar_room_rest: denied CTA emit for user ' . (int) get_current_user_id());
        return new WP_Error('webinar_rest_forbidden', 'Forbidden', array('status' => 403));
    }

    return true;
}

function ui_webinar_room_get_current_webinar_id() {
    return apply_filters('core_webinar_get_current', null);
}

/**
 * @param int $webinar_id
 * @return array<string, mixed>
 */
function ui_webinar_room_get_state_payload($webinar_id) {
    $webinar_data = apply_filters('core_webinar_get', (int) $webinar_id, array());
    $status = $webinar_data['status'] ?? 'scheduled';
    $cta_visibility = $webinar_data['cta_visibility'] ?? 'hidden';

    return array(
        'id' => (int) $webinar_id,
        'status' => $status,
        'state' => ui_webinar_room_normalize_state($status),
        'timing' => array(
            'start_datetime' => $webinar_data['start_datetime'] ?? '',
        ),
        'cta_visibility' => $cta_visibility,
        'cta_visible' => $cta_visibility === 'shown',
    );
}

function ui_webinar_room_handle_state_read() {
    $webinar_id = ui_webinar_room_get_current_webinar_id();
    if (empty($webinar_id)) {
        return new WP_Error('no_webinar', 'No webinar found', array('status' => 404));
    }

    $webinar_data = apply_filters('core_webinar_get', (int) $webinar_id, array());
    $status = $webinar_data['status'] ?? 'scheduled';
    $role = ui_webinar_room_get_actor_role();
    $state_error = ui_webinar_room_assert_state_access($role, $status, (int) $webinar_id, 'state');
    if (is_wp_error($state_error)) {
        return $state_error;
    }

    return rest_ensure_response(ui_webinar_room_get_state_payload((int) $webinar_id));
}

/**
 * @param string $status
 * @return string
 */
function ui_webinar_room_normalize_state($status) {
    $normalized = (string) sanitize_key($status);
    if ($normalized === 'ended') {
        return 'finished';
    }

    if ($normalized === '') {
        return 'scheduled';
    }

    return $normalized;
}

function ui_webinar_room_get_webinar_data($webinar_id = null) {
    if (empty($webinar_id)) {
        $webinar_id = ui_webinar_room_get_current_webinar_id();
    }

    if (empty($webinar_id)) {
        return array();
    }

    return apply_filters('core_webinar_get', (int) $webinar_id, array());
}

function ui_webinar_room_get_actor_role() {
    if (!is_user_logged_in()) {
        return '';
    }

    if (current_user_can('manage_options') || current_user_can('edit_pages')) {
        return 'organizer';
    }

    if (current_user_can('speaker')) {
        return 'speaker';
    }

    $user = wp_get_current_user();
    $roles = is_array($user->roles) ? $user->roles : array();
    $attendee_roles = array('candidate', 'partner');
    if (array_intersect($attendee_roles, $roles)) {
        return 'attendee';
    }

    return '';
}

/**
 * @param string $role
 * @param string $status
 * @param int $webinar_id
 * @param string $endpoint
 * @return true|WP_Error
 */
function ui_webinar_room_assert_state_access($role, $status, $webinar_id, $endpoint) {
    $state = ui_webinar_room_normalize_state($status);
    if ($role === 'attendee' && $state === 'draft') {
        error_log(
            'ui_webinar_room_rest: denied attendee access to ' .
            $endpoint .
            ' for webinar ' .
            (int) $webinar_id .
            ' state ' .
            $state
        );
        return new WP_Error('webinar_rest_forbidden_state', 'Forbidden', array('status' => 403));
    }

    return true;
}

/**
 * @param array<string, mixed> $webinar_data
 * @return array<string, mixed>
 */
function ui_webinar_room_build_webinar_info(array $webinar_data) {
    $status = $webinar_data['status'] ?? 'scheduled';
    return array(
        'id' => isset($webinar_data['id']) ? (int) $webinar_data['id'] : 0,
        'state' => ui_webinar_room_normalize_state($status),
        'timing' => array(
            'start_datetime' => $webinar_data['start_datetime'] ?? '',
        ),
    );
}

/**
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function ui_webinar_room_handle_webinars_list($request) {
    $role = ui_webinar_room_get_actor_role();
    $items = array();

    if (!defined('CORE_ENGINE_WEBINAR_CPT')) {
        return rest_ensure_response($items);
    }

    $query = new WP_Query(
        array(
            'post_type' => CORE_ENGINE_WEBINAR_CPT,
            'posts_per_page' => 20,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        )
    );

    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $webinar_data = apply_filters('core_webinar_get', (int) $post->ID, array());
            $status = $webinar_data['status'] ?? 'scheduled';
            $state = ui_webinar_room_normalize_state($status);
            if ($role === 'attendee' && $state === 'draft') {
                continue;
            }
            $items[] = ui_webinar_room_build_webinar_info($webinar_data);
        }
    }
    wp_reset_postdata();

    return rest_ensure_response($items);
}

/**
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function ui_webinar_room_handle_webinar_state_read($request) {
    $webinar_id = absint($request['id']);
    if (!$webinar_id) {
        return new WP_Error('invalid_webinar', 'Invalid webinar id', array('status' => 400));
    }

    $webinar_data = apply_filters('core_webinar_get', (int) $webinar_id, array());
    if (empty($webinar_data)) {
        return new WP_Error('no_webinar', 'No webinar found', array('status' => 404));
    }

    $role = ui_webinar_room_get_actor_role();
    $status = $webinar_data['status'] ?? 'scheduled';
    $state_error = ui_webinar_room_assert_state_access($role, $status, (int) $webinar_id, 'state');
    if (is_wp_error($state_error)) {
        return $state_error;
    }

    return rest_ensure_response(ui_webinar_room_get_state_payload((int) $webinar_id));
}

/**
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function ui_webinar_room_handle_webinar_info_read($request) {
    $webinar_id = absint($request['id']);
    if (!$webinar_id) {
        return new WP_Error('invalid_webinar', 'Invalid webinar id', array('status' => 400));
    }

    $webinar_data = apply_filters('core_webinar_get', (int) $webinar_id, array());
    if (empty($webinar_data)) {
        return new WP_Error('no_webinar', 'No webinar found', array('status' => 404));
    }

    $role = ui_webinar_room_get_actor_role();
    $status = $webinar_data['status'] ?? 'scheduled';
    $state_error = ui_webinar_room_assert_state_access($role, $status, (int) $webinar_id, 'info');
    if (is_wp_error($state_error)) {
        return $state_error;
    }

    return rest_ensure_response(ui_webinar_room_build_webinar_info($webinar_data));
}

/**
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function ui_webinar_room_handle_cta_event($request) {
    $webinar_id = absint($request['id']);
    if (!$webinar_id) {
        return new WP_Error('invalid_webinar', 'Invalid webinar id', array('status' => 400));
    }

    $event = $request->get_param('event');
    $event = is_string($event) ? sanitize_text_field($event) : '';
    if ($event !== 'shown' && $event !== 'clicked') {
        return new WP_Error('invalid_event', 'Invalid CTA event', array('status' => 400));
    }

    $webinar_data = apply_filters('core_webinar_get', (int) $webinar_id, array());
    if (empty($webinar_data)) {
        return new WP_Error('no_webinar', 'No webinar found', array('status' => 404));
    }

    $role = ui_webinar_room_get_actor_role();
    $status = $webinar_data['status'] ?? 'scheduled';
    $state_error = ui_webinar_room_assert_state_access($role, $status, (int) $webinar_id, 'cta');
    if (is_wp_error($state_error)) {
        return $state_error;
    }

    $context = array(
        'webinar_id' => (int) $webinar_id,
        'user_id' => (int) get_current_user_id(),
    );

    if ($event === 'shown') {
        do_action('cta_shown', $context);
        error_log('ui_webinar_room: cta_shown for webinar ' . (int) $webinar_id . ' user ' . (int) $context['user_id']);
    }

    if ($event === 'clicked') {
        do_action('cta_clicked', $context);
        error_log('ui_webinar_room: cta_clicked for webinar ' . (int) $webinar_id . ' user ' . (int) $context['user_id']);
    }

    return rest_ensure_response(array('ok' => true));
}

function ui_webinar_room_format_status_label($status) {
    switch ($status) {
        case 'live':
            return 'LIVE';
        case 'ended':
        case 'finished':
            return 'Завершён';
        case 'scheduled':
        default:
            return 'Запланирован';
    }
}

/**
 * @param array<string, string> $atts
 * @return string
 */
function ui_webinar_room_render_shortcode($atts = array()) {
    if (!is_user_logged_in()) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
        $login_url = wp_login_url((string) home_url(add_query_arg(array(), $request_uri)));
        return '<p class="ui-webinar-room__notice">' .
            esc_html__('Доступ в вебинарную комнату доступен только для авторизованных пользователей.', 'ui-webinar-room') .
            ' <a href="' . esc_url($login_url) . '">' .
            esc_html__('Войти', 'ui-webinar-room') .
            '</a></p>';
    }

    $role = ui_webinar_room_get_actor_role();

    if ($role === '') {
        return '<p class="ui-webinar-room__notice">' .
            esc_html__('Доступ в вебинарную комнату доступен только для участников.', 'ui-webinar-room') .
            '</p>';
    }

    $atts = shortcode_atts(
        array(
            'webinar_id' => '',
        ),
        $atts,
        'webinar_room'
    );

    $webinar_id = absint($atts['webinar_id']);
    $webinar_data = ui_webinar_room_get_webinar_data($webinar_id);

    if (empty($webinar_data) && $webinar_id) {
        $webinar_data = ui_webinar_room_get_webinar_data(null);
    }

    if (empty($webinar_data)) {
        return '<p class="ui-webinar-room__notice">' .
            esc_html__('Вебинар не найден или ещё не назначен.', 'ui-webinar-room') .
            '</p>';
    }

    $status = $webinar_data['status'] ?? 'scheduled';
    $state = ui_webinar_room_normalize_state($status);
    $status_label = ui_webinar_room_format_status_label($state);
    $state_error = ui_webinar_room_assert_state_access($role, $status, (int) ($webinar_data['id'] ?? 0), 'render');
    if (is_wp_error($state_error)) {
        return '<p class="ui-webinar-room__notice">' .
            esc_html__('Вебинар сейчас недоступен для вашей роли.', 'ui-webinar-room') .
            '</p>';
    }

    $video_label = $state === 'live' ? 'Идёт онлайн-вебинар' : 'Видео вебинара';
    $title = $webinar_data['title'] ?? 'Онлайн-вебинар';
    $start_datetime = $webinar_data['start_datetime'] ?? '';
    $stream_src = $webinar_data['stream_src'] ?? '';
    $webinar_id_value = (int) ($webinar_data['id'] ?? $webinar_id);
    $rest_nonce = wp_create_nonce('wp_rest');
    $nonce_handle = 'ui-webinar-room-nonce';

    wp_register_script($nonce_handle, false, array(), null, false);
    wp_enqueue_script($nonce_handle);
    wp_add_inline_script(
        $nonce_handle,
        'window.webinarRoom = window.webinarRoom || {}; window.webinarRoom.nonce = ' . wp_json_encode($rest_nonce) . ';',
        'before'
    );

    ob_start();
    ?>
    <?php wp_print_scripts($nonce_handle); ?>
    <style>
        .ui-webinar-room {
            display: block;
            margin: 24px 0;
            font-family: inherit;
        }
        .ui-webinar-room__header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }
        .ui-webinar-room__status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(0, 0, 0, 0.06);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .ui-webinar-room__layout {
            display: grid;
            gap: 20px;
            margin-top: 20px;
        }
        .ui-webinar-room__state-card {
            padding: 16px;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.03);
        }
        .ui-webinar-room__state-label {
            margin: 0 0 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: rgba(0, 0, 0, 0.6);
        }
        .ui-webinar-room__state-value {
            margin: 0 0 8px;
            font-size: 18px;
            font-weight: 600;
        }
        .ui-webinar-room__state-message {
            margin: 0;
            color: rgba(0, 0, 0, 0.7);
        }
        .ui-webinar-room__placeholder {
            margin-top: 16px;
        }
        .ui-webinar-room__placeholder iframe {
            width: 100%;
            min-height: 360px;
            border: 0;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.04);
        }
        .ui-webinar-room__video-label {
            margin-top: 10px;
            font-size: 14px;
            color: rgba(0, 0, 0, 0.7);
        }
        .ui-webinar-room__cta {
            margin-top: 20px;
            padding: 16px;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.04);
        }
        .ui-webinar-room__cta-text {
            margin: 0 0 10px;
            font-size: 14px;
            color: rgba(0, 0, 0, 0.7);
        }
        .ui-webinar-room__cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid rgba(0, 0, 0, 0.18);
            background: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
    <section
        class="ui-webinar-room"
        data-webinar-id="<?php echo esc_attr((string) $webinar_id_value); ?>"
        data-status="<?php echo esc_attr($state); ?>"
        data-rest-url="<?php echo esc_url(rest_url('webinar/v1/state')); ?>"
        data-cta-url="<?php echo esc_url(rest_url('webinar/v1/webinars/' . (int) $webinar_id_value . '/cta')); ?>"
        data-rest-nonce="<?php echo esc_attr($rest_nonce); ?>"
    >
        <header class="ui-webinar-room__header">
            <div>
                <h2><?php echo esc_html($title); ?></h2>
                <p class="ui-webinar-room__datetime">
                    <?php echo $start_datetime ? esc_html($start_datetime) : esc_html__('Дата уточняется', 'ui-webinar-room'); ?>
                </p>
                <p class="ui-webinar-room__meta">
                    <?php echo esc_html__('ID:', 'ui-webinar-room'); ?>
                    <?php echo esc_html((string) $webinar_id_value); ?>
                </p>
            </div>
            <span class="ui-webinar-room__status" data-status-label><?php echo esc_html($status_label); ?></span>
        </header>

        <div class="ui-webinar-room__layout">
            <div class="ui-webinar-room__main">
                <div class="ui-webinar-room__state-card" role="status" aria-live="polite">
                    <p class="ui-webinar-room__state-label">
                        <?php echo esc_html__('Состояние комнаты', 'ui-webinar-room'); ?>
                    </p>
                    <p class="ui-webinar-room__state-value" data-state-value><?php echo esc_html($state); ?></p>
                    <p class="ui-webinar-room__state-message" data-state-message></p>
                </div>
                <div class="ui-webinar-room__placeholder">
                    <iframe
                        title="<?php echo esc_attr($video_label); ?>"
                        src="<?php echo esc_url($stream_src ?: 'about:blank'); ?>"
                        loading="lazy"
                    ></iframe>
                    <p class="ui-webinar-room__video-label">
                        <?php echo esc_html($video_label); ?>
                    </p>
                </div>
                <div class="ui-webinar-room__cta" data-cta hidden>
                    <p class="ui-webinar-room__cta-text">
                        <?php echo esc_html__('Узнать больше / Присоединиться к WHIEDA', 'ui-webinar-room'); ?>
                    </p>
                    <button type="button" class="ui-webinar-room__cta-button" data-cta-button>
                        <?php echo esc_html__('Узнать больше / Присоединиться к WHIEDA', 'ui-webinar-room'); ?>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function() {
            var roots = document.querySelectorAll('.ui-webinar-room');
            if (!roots.length) {
                return;
            }

            roots.forEach(function(root) {
                var restUrl = root.getAttribute('data-rest-url') || '';
                var ctaUrl = root.getAttribute('data-cta-url') || '';
                var webinarRoom = window.webinarRoom || {};
                var restNonce = root.getAttribute('data-rest-nonce') || '';
                var statusLabel = root.querySelector('[data-status-label]');
                var stateValue = root.querySelector('[data-state-value]');
                var stateMessage = root.querySelector('[data-state-message]');
                var ctaContainer = root.querySelector('[data-cta]');
                var ctaButton = root.querySelector('[data-cta-button]');
                var ctaShown = false;

                if (!webinarRoom.nonce && restNonce) {
                    webinarRoom.nonce = restNonce;
                }

                function normalizeLabel(nextStatus) {
                    switch (nextStatus) {
                        case 'live':
                            return 'LIVE';
                        case 'finished':
                        case 'ended':
                            return 'Завершён';
                        case 'scheduled':
                        default:
                            return 'Запланирован';
                    }
                }

                function getStateDescriptor(nextState) {
                    switch (nextState) {
                        case 'loading':
                            return {
                                label: 'Загрузка',
                                message: 'Получаем актуальное состояние комнаты...'
                            };
                        case 'access_denied':
                            return {
                                label: 'Доступ ограничен',
                                message: 'У вас нет доступа к состоянию вебинарной комнаты.'
                            };
                        case 'finished':
                        case 'ended':
                            return {
                                label: 'Вебинар завершён',
                                message: 'Эфир уже закончился. Запись будет доступна, если она предусмотрена.'
                            };
                        case 'live':
                            return {
                                label: 'Прямой эфир',
                                message: 'Вебинар идёт. Вы можете смотреть трансляцию ниже.'
                            };
                        case 'scheduled':
                        default:
                            return {
                                label: 'Ожидание старта',
                                message: 'Вебинар ещё не начался. Мы автоматически обновим статус.'
                            };
                    }
                }

                function setStateMessage(nextState) {
                    if (!stateValue && !stateMessage) {
                        return;
                    }
                    var descriptor = getStateDescriptor(nextState);
                    if (stateValue) {
                        stateValue.textContent = descriptor.label;
                    }
                    if (stateMessage) {
                        stateMessage.textContent = descriptor.message;
                    }
                }

                function setStatus(nextStatus) {
                    if (!nextStatus) {
                        return;
                    }
                    if (nextStatus === root.getAttribute('data-status')) {
                        return;
                    }
                    root.setAttribute('data-status', nextStatus);
                    if (statusLabel) {
                        statusLabel.textContent = normalizeLabel(nextStatus);
                    }
                }

                function applyState(payload) {
                    if (!payload) {
                        return;
                    }
                    if (payload.state) {
                        setStatus(payload.state);
                        setStateMessage(payload.state);
                        updateCtaVisibility(payload.state);
                    } else if (payload.status) {
                        setStatus(payload.status);
                        setStateMessage(payload.status);
                        updateCtaVisibility(payload.status);
                    }
                }

                function shouldShowCta(nextState) {
                    return nextState === 'live' || nextState === 'finished' || nextState === 'ended';
                }

                function emitCtaEvent(eventName) {
                    if (!ctaUrl) {
                        return;
                    }
                    fetch(ctaUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-WP-Nonce': webinarRoom.nonce,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            event: eventName
                        })
                    }).then(function() {}).catch(function() {});
                }

                function updateCtaVisibility(nextState) {
                    if (!ctaContainer) {
                        return;
                    }
                    if (shouldShowCta(nextState)) {
                        ctaContainer.hidden = false;
                        if (!ctaShown) {
                            ctaShown = true;
                            emitCtaEvent('shown');
                        }
                    } else {
                        ctaContainer.hidden = true;
                    }
                }

                function fetchState() {
                    if (!restUrl) {
                        return;
                    }

                    setStateMessage('loading');
                    fetch(restUrl, {
                        credentials: 'same-origin',
                        headers: {
                            'X-WP-Nonce': webinarRoom.nonce
                        }
                    }).then(function(response) {
                        if (!response.ok) {
                            if (response.status === 401 || response.status === 403) {
                                setStateMessage('access_denied');
                            }
                            return null;
                        }
                        return response.json();
                    }).then(function(payload) {
                        applyState(payload);
                    }).catch(function() {});
                }

                setStateMessage(root.getAttribute('data-status') || 'scheduled');
                updateCtaVisibility(root.getAttribute('data-status') || 'scheduled');
                if (ctaButton) {
                    ctaButton.addEventListener('click', function() {
                        emitCtaEvent('clicked');
                    });
                }
                window.setInterval(fetchState, 9000);
            });
        })();
    </script>
    <?php

    return ob_get_clean();
}
