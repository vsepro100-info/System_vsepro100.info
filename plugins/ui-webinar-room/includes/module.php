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
    $attendee_roles = array('attendee', 'candidate', 'partner', 'subscriber');
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

function ui_webinar_room_format_status_label($status) {
    switch ($status) {
        case 'live':
            return 'LIVE';
        case 'ended':
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

    $user = wp_get_current_user();
    $allowed_roles = array('candidate', 'partner', 'editor', 'administrator');
    $user_roles = is_array($user->roles) ? $user->roles : array();
    $has_access = array_intersect($allowed_roles, $user_roles);

    if (empty($has_access)) {
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

    $lead_id = 0;
    if (isset($_GET['lead_id'])) {
        $lead_id = absint($_GET['lead_id']);
    } else {
        $query_lead_id = get_query_var('lead_id');
        if (!empty($query_lead_id)) {
            $lead_id = absint($query_lead_id);
        }
    }

    $status = $webinar_data['status'] ?? 'scheduled';
    $status_label = ui_webinar_room_format_status_label($status);
    $video_label = $status === 'live' ? 'Идёт онлайн-вебинар' : 'Запись вебинара';
    $title = $webinar_data['title'] ?? 'Онлайн-вебинар';
    $start_datetime = $webinar_data['start_datetime'] ?? '';
    $poster_url = $webinar_data['poster_url'] ?? '';
    $stream_src = $webinar_data['stream_src'] ?? '';
    $chat_src = $webinar_data['chat_src'] ?? '';
    $cta_text = $webinar_data['cta_text'] ?? 'Задать вопрос консультанту';
    $cta_link = $webinar_data['cta_link'] ?? '';
    $cta_visibility = $webinar_data['cta_visibility'] ?? 'hidden';
    $cta_is_shown = $cta_visibility === 'shown';
    $can_manage = current_user_can('edit_pages');
    $can_manage_cta = current_user_can('speaker');
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
    <section
        class="ui-webinar-room"
        data-webinar-id="<?php echo esc_attr((string) ($webinar_data['id'] ?? $webinar_id)); ?>"
        data-status="<?php echo esc_attr($status); ?>"
        data-lead-id="<?php echo esc_attr((string) $lead_id); ?>"
        data-cta-visibility="<?php echo esc_attr($cta_visibility); ?>"
        data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
        data-rest-url="<?php echo esc_url(rest_url('webinar/v1/state')); ?>"
        data-rest-nonce="<?php echo esc_attr($rest_nonce); ?>"
    >
        <header class="ui-webinar-room__header">
            <div>
                <h2><?php echo esc_html($title); ?></h2>
                <p class="ui-webinar-room__datetime">
                    <?php echo $start_datetime ? esc_html($start_datetime) : esc_html__('Дата уточняется', 'ui-webinar-room'); ?>
                </p>
            </div>
            <span class="ui-webinar-room__status"><?php echo esc_html($status_label); ?></span>
        </header>

        <?php if ($can_manage) : ?>
            <div class="ui-webinar-room__adminbar">
                <button type="button" class="ui-webinar-room__admin-button" data-action="start">
                    <?php echo esc_html__('Start', 'ui-webinar-room'); ?>
                </button>
                <button type="button" class="ui-webinar-room__admin-button" data-action="stop">
                    <?php echo esc_html__('Stop', 'ui-webinar-room'); ?>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($can_manage_cta) : ?>
            <div class="ui-webinar-room__cta-controls">
                <button type="button" class="ui-webinar-room__admin-button" data-action="show-cta">
                    <?php echo esc_html__('Show CTA', 'ui-webinar-room'); ?>
                </button>
                <button type="button" class="ui-webinar-room__admin-button" data-action="hide-cta">
                    <?php echo esc_html__('Hide CTA', 'ui-webinar-room'); ?>
                </button>
            </div>
        <?php endif; ?>

        <div class="ui-webinar-room__layout">
            <div class="ui-webinar-room__main">
                <div class="ui-webinar-room__lobby" data-screen="lobby">
                    <?php if ($poster_url) : ?>
                        <img class="ui-webinar-room__poster" src="<?php echo esc_url($poster_url); ?>" alt="" />
                    <?php endif; ?>
                    <p class="ui-webinar-room__lobby-text">
                        <?php echo esc_html__('Ожидаем начала вебинара.', 'ui-webinar-room'); ?>
                    </p>
                    <button class="ui-webinar-room__button" type="button" data-action="enter">
                        <?php echo esc_html__('Войти на вебинар', 'ui-webinar-room'); ?>
                    </button>
                </div>

                <div class="ui-webinar-room__player" data-screen="viewing" hidden>
                    <iframe
                        title="<?php echo esc_attr($video_label); ?>"
                        src="<?php echo esc_url($stream_src ?: 'about:blank'); ?>"
                        loading="lazy"
                    ></iframe>
                    <p class="ui-webinar-room__video-label">
                        <?php echo esc_html($video_label); ?>
                    </p>
                    <button class="ui-webinar-room__button" type="button" data-action="finish">
                        <?php echo esc_html__('Завершить просмотр', 'ui-webinar-room'); ?>
                    </button>
                </div>

                <div class="ui-webinar-room__complete" data-screen="complete" hidden>
                    <p class="ui-webinar-room__complete-text">
                        <?php echo esc_html__('Вебинар завершён', 'ui-webinar-room'); ?>
                    </p>
                    <?php if ($cta_is_shown) : ?>
                        <?php if (!empty($cta_link)) : ?>
                            <a class="ui-webinar-room__button" data-cta-button="1" href="<?php echo esc_url($cta_link); ?>">
                                <?php echo esc_html($cta_text); ?>
                            </a>
                        <?php else : ?>
                            <button class="ui-webinar-room__button" type="button" data-action="consult" data-cta-button="1">
                                <?php echo esc_html($cta_text); ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <template class="ui-webinar-room__cta-template">
                        <?php if (!empty($cta_link)) : ?>
                            <a class="ui-webinar-room__button" data-cta-button="1" href="<?php echo esc_url($cta_link); ?>">
                                <?php echo esc_html($cta_text); ?>
                            </a>
                        <?php else : ?>
                            <button class="ui-webinar-room__button" type="button" data-action="consult" data-cta-button="1">
                                <?php echo esc_html($cta_text); ?>
                            </button>
                        <?php endif; ?>
                    </template>
                </div>
            </div>

            <aside class="ui-webinar-room__sidebar">
                <div class="ui-webinar-room__users">
                    <?php echo do_shortcode('[whieda_live_presence]'); ?>
                </div>
                <div class="ui-webinar-room__chat">
                    <?php if ($chat_src) : ?>
                        <iframe title="<?php echo esc_attr__('Чат вебинара', 'ui-webinar-room'); ?>" src="<?php echo esc_url($chat_src); ?>" loading="lazy"></iframe>
                    <?php endif; ?>
                    <?php echo do_shortcode('[webinar_room_chat]'); ?>
                </div>
            </aside>
        </div>
    </section>

    <script>
        (function() {
            var roots = document.querySelectorAll('.ui-webinar-room');
            if (!roots.length) {
                return;
            }

            roots.forEach(function(root) {
                var webinarId = root.getAttribute('data-webinar-id') || '';
                var leadId = root.getAttribute('data-lead-id') || '';
                var ajaxUrl = root.getAttribute('data-ajax-url') || '';
                var restUrl = root.getAttribute('data-rest-url') || '';

                var entryButton = root.querySelector('[data-action="enter"]');
                var finishButton = root.querySelector('[data-action="finish"]');
                var startButton = root.querySelector('[data-action="start"]');
                var stopButton = root.querySelector('[data-action="stop"]');
                var showCtaButton = root.querySelector('[data-action="show-cta"]');
                var hideCtaButton = root.querySelector('[data-action="hide-cta"]');
                var ctaTemplate = root.querySelector('.ui-webinar-room__cta-template');
                var ctaContainer = root.querySelector('.ui-webinar-room__complete');
                var webinarRoom = window.webinarRoom || {};
                var restNonce = root.getAttribute('data-rest-nonce') || '';

                if (!webinarRoom.nonce && restNonce) {
                    webinarRoom.nonce = restNonce;
                }

                function getCtaVisibility() {
                    return root.getAttribute('data-cta-visibility') || 'hidden';
                }

                function setCtaVisibility(visibility) {
                    root.setAttribute('data-cta-visibility', visibility);
                    updateCtaVisibility(visibility);
                }

                function updateCtaVisibility(visibility) {
                    if (!ctaContainer) {
                        return;
                    }

                    var existing = ctaContainer.querySelector('[data-cta-button="1"]');
                    if (visibility === 'shown') {
                        if (!existing && ctaTemplate && 'content' in ctaTemplate) {
                            ctaContainer.appendChild(ctaTemplate.content.cloneNode(true));
                        }
                    } else if (existing) {
                        existing.remove();
                    }
                }

                function showScreen(screenName) {
                    var screens = root.querySelectorAll('[data-screen]');
                    screens.forEach(function(screen) {
                        if (screen.getAttribute('data-screen') === screenName) {
                            screen.removeAttribute('hidden');
                        } else {
                            screen.setAttribute('hidden', 'hidden');
                        }
                    });

                    if (screenName === 'complete') {
                        emitCompleted();
                    }
                }

                function emitEntered() {
                    if (root.dataset.entered === '1') {
                        return;
                    }
                    root.dataset.entered = '1';

                    try {
                        var url = new URL(window.location.href);
                        url.searchParams.set('webinar_id', webinarId);
                        if (leadId) {
                            url.searchParams.set('lead_id', leadId);
                        }
                        fetch(url.toString(), {
                            credentials: 'same-origin',
                            headers: {
                                'X-WP-Nonce': webinarRoom.nonce
                            }
                        }).catch(function() {});
                    } catch (error) {
                    }
                }

                function emitCompleted() {
                    if (root.dataset.completed === '1' || !ajaxUrl) {
                        return;
                    }
                    root.dataset.completed = '1';

                    var body = new URLSearchParams();
                    body.set('action', 'client_webinar_completed');
                    body.set('webinar_id', webinarId);
                    if (leadId) {
                        body.set('lead_id', leadId);
                    }

                    fetch(ajaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-WP-Nonce': webinarRoom.nonce
                        },
                        body: body.toString()
                    }).catch(function() {});
                }

                function setStatus(nextStatus) {
                    if (!nextStatus) {
                        return;
                    }
                    if (nextStatus === root.getAttribute('data-status')) {
                        return;
                    }
                    root.setAttribute('data-status', nextStatus);
                    if (nextStatus === 'live') {
                        showScreen('viewing');
                    } else if (nextStatus === 'ended') {
                        showScreen('complete');
                    } else {
                        showScreen('lobby');
                    }
                }

                function applyState(payload) {
                    if (!payload) {
                        return;
                    }
                    if (payload.status) {
                        setStatus(payload.status);
                    }
                    if (payload.cta_visibility) {
                        if (payload.cta_visibility !== getCtaVisibility()) {
                            setCtaVisibility(payload.cta_visibility);
                        }
                    } else if (typeof payload.cta_visible === 'boolean') {
                        setCtaVisibility(payload.cta_visible ? 'shown' : 'hidden');
                    }
                }

                function fetchState() {
                    if (!restUrl) {
                        return;
                    }

                    fetch(restUrl, {
                        credentials: 'same-origin',
                        headers: {
                            'X-WP-Nonce': webinarRoom.nonce
                        }
                    }).then(function(response) {
                        if (!response.ok) {
                            return null;
                        }
                        return response.json();
                    }).then(function(payload) {
                        applyState(payload);
                    }).catch(function() {});
                }

                function sendStateUpdate(payload) {
                    if (!restUrl || !webinarRoom.nonce) {
                        return;
                    }

                    fetch(restUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': webinarRoom.nonce
                        },
                        body: JSON.stringify(payload || {})
                    }).then(function(response) {
                        if (!response.ok) {
                            return null;
                        }
                        return response.json();
                    }).then(function(responsePayload) {
                        applyState(responsePayload);
                    }).catch(function() {});
                }

                var status = root.getAttribute('data-status') || 'scheduled';
                if (status === 'live') {
                    showScreen('viewing');
                } else if (status === 'ended') {
                    showScreen('complete');
                } else {
                    showScreen('lobby');
                }

                updateCtaVisibility(getCtaVisibility());

                if (entryButton) {
                    entryButton.addEventListener('click', function() {
                        showScreen('viewing');
                        emitEntered();
                    });
                }

                if (finishButton) {
                    finishButton.addEventListener('click', function() {
                        showScreen('complete');
                    });
                }

                if (startButton) {
                    startButton.addEventListener('click', function() {
                        sendStateUpdate({ status: 'live' });
                    });
                }

                if (stopButton) {
                    stopButton.addEventListener('click', function() {
                        sendStateUpdate({ status: 'ended' });
                    });
                }

                if (showCtaButton) {
                    showCtaButton.addEventListener('click', function() {
                        sendStateUpdate({ cta_visibility: 'shown' });
                    });
                }

                if (hideCtaButton) {
                    hideCtaButton.addEventListener('click', function() {
                        sendStateUpdate({ cta_visibility: 'hidden' });
                    });
                }

                window.setInterval(fetchState, 9000);
            });
        })();
    </script>
    <?php

    return ob_get_clean();
}
