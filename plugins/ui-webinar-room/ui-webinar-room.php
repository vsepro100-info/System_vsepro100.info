<?php
/**
 * Plugin Name: Webinar Room UI
 * Description: UI-only shortcode for webinar room with MVP logic.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_shortcode('webinar_room', 'ui_webinar_room_render_shortcode');
add_shortcode('whieda_live_room', 'ui_webinar_room_render_shortcode');

function ui_webinar_room_get_webinar_data($webinar_id = null) {
    if (empty($webinar_id)) {
        $webinar_id = apply_filters('core_webinar_get_current', null);
    }

    if (empty($webinar_id)) {
        return array();
    }

    return apply_filters('core_webinar_get', (int) $webinar_id, array());
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
    $can_manage = current_user_can('edit_pages');
    $nonce = wp_create_nonce('core_webinar_control');

    ob_start();
    ?>
    <section
        class="ui-webinar-room"
        data-webinar-id="<?php echo esc_attr((string) ($webinar_data['id'] ?? $webinar_id)); ?>"
        data-status="<?php echo esc_attr($status); ?>"
        data-lead-id="<?php echo esc_attr((string) $lead_id); ?>"
        data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
        data-nonce="<?php echo esc_attr($nonce); ?>"
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
                    <?php if (!empty($cta_link)) : ?>
                        <a class="ui-webinar-room__button" href="<?php echo esc_url($cta_link); ?>">
                            <?php echo esc_html($cta_text); ?>
                        </a>
                    <?php else : ?>
                        <button class="ui-webinar-room__button" type="button" data-action="consult">
                            <?php echo esc_html($cta_text); ?>
                        </button>
                    <?php endif; ?>
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
                    <?php echo do_shortcode('[whieda_room_chat]'); ?>
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
                var nonce = root.getAttribute('data-nonce') || '';

                var entryButton = root.querySelector('[data-action="enter"]');
                var finishButton = root.querySelector('[data-action="finish"]');
                var startButton = root.querySelector('[data-action="start"]');
                var stopButton = root.querySelector('[data-action="stop"]');

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
                            credentials: 'same-origin'
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
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: body.toString()
                    }).catch(function() {});
                }

                function sendControl(actionName) {
                    if (!ajaxUrl || !nonce) {
                        return;
                    }
                    var body = new URLSearchParams();
                    body.set('action', actionName);
                    body.set('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: body.toString()
                    }).then(function(response) {
                        return response.json();
                    }).then(function(payload) {
                        if (!payload || !payload.success) {
                            return;
                        }
                        if (payload.data && payload.data.status) {
                            root.setAttribute('data-status', payload.data.status);
                        }
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
                        sendControl('core_webinar_start');
                    });
                }

                if (stopButton) {
                    stopButton.addEventListener('click', function() {
                        sendControl('core_webinar_stop');
                    });
                }
            });
        })();
    </script>
    <?php

    return ob_get_clean();
}
