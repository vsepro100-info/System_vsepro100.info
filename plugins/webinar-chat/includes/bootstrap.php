<?php

defined('ABSPATH') || exit;

define('WEBINAR_CHAT_TABLE', 'wh_room_chat');

define('WEBINAR_CHAT_NONCE', 'webinar_chat_nonce');

define('WEBINAR_CHAT_LEGACY_NONCE', 'whieda_chat_nonce');

define('WEBINAR_CHAT_ACTION_FETCH', 'webinar_chat_fetch');

define('WEBINAR_CHAT_ACTION_SEND', 'webinar_chat_send');

define('WEBINAR_CHAT_ACTION_MODER', 'webinar_chat_moder');

define('WEBINAR_CHAT_ACTION_FETCH_LEGACY', 'whieda_chat_fetch');

define('WEBINAR_CHAT_ACTION_SEND_LEGACY', 'whieda_chat_send');

define('WEBINAR_CHAT_ACTION_MODER_LEGACY', 'whieda_chat_moder');

define('WEBINAR_CHAT_OPTION_ENABLED', 'wh_chat_enabled');

define('WEBINAR_CHAT_OPTION_BANNED', 'wh_chat_banned');

register_activation_hook(WEBINAR_CHAT_PLUGIN_FILE, 'webinar_chat_activate');

add_action('init', 'webinar_chat_bootstrap');

add_shortcode('webinar_room_chat', 'webinar_chat_render_shortcode');
// Deprecated shortcode alias for backward compatibility.
add_shortcode('whieda_room_chat', 'webinar_chat_render_shortcode');

add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_FETCH, 'webinar_chat_handle_fetch');
add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_SEND, 'webinar_chat_handle_send');
add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_MODER, 'webinar_chat_handle_moderation');

add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_FETCH_LEGACY, 'webinar_chat_handle_fetch');
add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_SEND_LEGACY, 'webinar_chat_handle_send');
add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_MODER_LEGACY, 'webinar_chat_handle_moderation');

/**
 * @return string
 */
function webinar_chat_table_name() {
    global $wpdb;
    return $wpdb->prefix . WEBINAR_CHAT_TABLE;
}

/**
 * @return array<int, string>
 */
function webinar_chat_get_table_columns() {
    static $columns = null;

    if ($columns !== null) {
        return $columns;
    }

    global $wpdb;
    $table = webinar_chat_table_name();

    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
        $columns = array();
        return $columns;
    }

    $columns = $wpdb->get_col("DESC {$table}");

    if (!is_array($columns)) {
        $columns = array();
    }

    return $columns;
}

/**
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function webinar_chat_filter_columns($data) {
    $columns = webinar_chat_get_table_columns();

    if (empty($columns)) {
        return array();
    }

    return array_intersect_key($data, array_flip($columns));
}

/**
 * @param array<string, mixed> $data
 * @return array<int, string>
 */
function webinar_chat_build_formats($data) {
    $formats = array();

    foreach ($data as $value) {
        if (is_int($value)) {
            $formats[] = '%d';
        } elseif (is_float($value)) {
            $formats[] = '%f';
        } else {
            $formats[] = '%s';
        }
    }

    return $formats;
}

/**
 * @return void
 */
function webinar_chat_activate() {
    webinar_chat_create_table();

    add_option(WEBINAR_CHAT_OPTION_ENABLED, 0);
    add_option(WEBINAR_CHAT_OPTION_BANNED, array());
}

/**
 * @return void
 */
function webinar_chat_bootstrap() {
    if (get_option(WEBINAR_CHAT_OPTION_ENABLED, null) === null) {
        add_option(WEBINAR_CHAT_OPTION_ENABLED, 0);
    }

    if (get_option(WEBINAR_CHAT_OPTION_BANNED, null) === null) {
        add_option(WEBINAR_CHAT_OPTION_BANNED, array());
    }

    webinar_chat_ensure_table();
}

/**
 * @return void
 */
function webinar_chat_ensure_table() {
    global $wpdb;
    $table = webinar_chat_table_name();

    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table) {
        return;
    }

    webinar_chat_create_table();
}

/**
 * @return void
 */
function webinar_chat_create_table() {
    global $wpdb;

    $table = webinar_chat_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        user_login VARCHAR(191) NOT NULL DEFAULT '',
        user_name VARCHAR(191) NOT NULL DEFAULT '',
        message TEXT NOT NULL,
        country VARCHAR(191) NOT NULL DEFAULT '',
        country_code VARCHAR(10) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        is_deleted TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

/**
 * @return bool
 */
function webinar_chat_is_moderator() {
    return current_user_can('manage_options') || current_user_can('speaker');
}

/**
 * @param WP_User $user
 * @return bool
 */
function webinar_chat_is_banned($user) {
    $banned = get_option(WEBINAR_CHAT_OPTION_BANNED, array());
    if (!is_array($banned)) {
        return false;
    }

    $candidates = array_filter(
        array(
            (string) $user->ID,
            (string) $user->user_login,
            (string) $user->user_email,
        )
    );

    foreach ($candidates as $candidate) {
        if (in_array($candidate, $banned, true)) {
            return true;
        }
    }

    return false;
}

/**
 * @param string $nonce
 * @return bool
 */
function webinar_chat_validate_nonce($nonce) {
    if (empty($nonce)) {
        return false;
    }

    if (wp_verify_nonce($nonce, WEBINAR_CHAT_NONCE)) {
        return true;
    }

    return (bool) wp_verify_nonce($nonce, WEBINAR_CHAT_LEGACY_NONCE);
}

/**
 * @param array<string, mixed> $atts
 * @return string
 */
function webinar_chat_render_shortcode($atts = array()) {
    if (!is_user_logged_in()) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
        $login_url = wp_login_url((string) home_url(add_query_arg(array(), $request_uri)));

        return '<p class="webinar-chat__notice">' .
            esc_html__('Доступ в чат доступен только для авторизованных пользователей.', 'webinar-chat') .
            ' <a href="' . esc_url($login_url) . '">' .
            esc_html__('Войти', 'webinar-chat') .
            '</a></p>';
    }

    $user = wp_get_current_user();
    $is_banned = webinar_chat_is_banned($user);
    $is_moderator = webinar_chat_is_moderator();
    $enabled = (int) get_option(WEBINAR_CHAT_OPTION_ENABLED, 0);

    $nonce = wp_create_nonce(WEBINAR_CHAT_NONCE);
    $legacy_nonce = wp_create_nonce(WEBINAR_CHAT_LEGACY_NONCE);

    ob_start();
    ?>
    <div
        class="webinar-chat"
        data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
        data-nonce="<?php echo esc_attr($nonce); ?>"
        data-legacy-nonce="<?php echo esc_attr($legacy_nonce); ?>"
        data-enabled="<?php echo esc_attr((string) $enabled); ?>"
        data-user-id="<?php echo esc_attr((string) $user->ID); ?>"
        data-user-name="<?php echo esc_attr($user->display_name); ?>"
        data-user-login="<?php echo esc_attr($user->user_login); ?>"
        data-user-email="<?php echo esc_attr($user->user_email); ?>"
        data-is-moderator="<?php echo esc_attr($is_moderator ? '1' : '0'); ?>"
        data-is-banned="<?php echo esc_attr($is_banned ? '1' : '0'); ?>"
    >
        <div class="webinar-chat__header">
            <h3 class="webinar-chat__title"><?php echo esc_html__('Чат вебинара', 'webinar-chat'); ?></h3>
            <?php if ($is_moderator) : ?>
                <button type="button" class="webinar-chat__clear" data-action="clear">
                    <?php echo esc_html__('Очистить', 'webinar-chat'); ?>
                </button>
            <?php endif; ?>
        </div>
        <div class="webinar-chat__messages" data-role="messages"></div>
        <?php if ($enabled === 0) : ?>
            <p class="webinar-chat__notice" data-role="disabled">
                <?php echo esc_html__('Чат временно отключён.', 'webinar-chat'); ?>
            </p>
        <?php elseif ($is_banned) : ?>
            <p class="webinar-chat__notice" data-role="banned">
                <?php echo esc_html__('Вы заблокированы модератором чата.', 'webinar-chat'); ?>
            </p>
        <?php else : ?>
            <form class="webinar-chat__form" data-role="form">
                <textarea class="webinar-chat__input" name="message" rows="2" placeholder="<?php echo esc_attr__('Введите сообщение', 'webinar-chat'); ?>"></textarea>
                <button class="webinar-chat__button" type="submit">
                    <?php echo esc_html__('Отправить', 'webinar-chat'); ?>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <style>
        .webinar-chat {
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 16px;
            background: #fff;
            display: flex;
            flex-direction: column;
            gap: 12px;
            font-family: inherit;
        }
        .webinar-chat__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .webinar-chat__title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }
        .webinar-chat__clear {
            background: #f5f5f5;
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }
        .webinar-chat__messages {
            min-height: 200px;
            max-height: 360px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .webinar-chat__message {
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }
        .webinar-chat__message-body {
            background: #f8f8f8;
            border-radius: 10px;
            padding: 8px 10px;
            flex: 1;
        }
        .webinar-chat__message-head {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
            font-size: 12px;
            color: #666;
        }
        .webinar-chat__message-name {
            font-weight: 600;
            color: #222;
        }
        .webinar-chat__message-text {
            white-space: pre-wrap;
            font-size: 14px;
            color: #222;
        }
        .webinar-chat__message-actions {
            display: flex;
            gap: 6px;
            margin-top: 6px;
        }
        .webinar-chat__action {
            background: transparent;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 11px;
            padding: 2px 6px;
            cursor: pointer;
        }
        .webinar-chat__form {
            display: flex;
            gap: 8px;
        }
        .webinar-chat__input {
            flex: 1;
            resize: vertical;
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 8px;
        }
        .webinar-chat__button {
            border-radius: 10px;
            border: none;
            background: #3a7afe;
            color: #fff;
            padding: 8px 16px;
            cursor: pointer;
        }
        .webinar-chat__notice {
            margin: 0;
            font-size: 13px;
            color: #666;
        }
    </style>

    <script>
        (function() {
            const chatNodes = document.querySelectorAll('.webinar-chat');
            if (!chatNodes.length) {
                return;
            }

            const fetchCountry = async () => {
                try {
                    const response = await fetch('https://ipapi.co/json/');
                    if (response.ok) {
                        const data = await response.json();
                        return {
                            country: data.country_name || '',
                            code: (data.country_code || '').toLowerCase(),
                        };
                    }
                } catch (error) {
                    // Ignore and fallback.
                }

                try {
                    const response = await fetch('https://ipwho.is/');
                    if (response.ok) {
                        const data = await response.json();
                        return {
                            country: data.country || '',
                            code: (data.country_code || '').toLowerCase(),
                        };
                    }
                } catch (error) {
                    // Ignore and fallback.
                }

                return { country: '', code: '' };
            };

            const formatTime = (value) => {
                if (!value) {
                    return '';
                }
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return value;
                }
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            };

            chatNodes.forEach(async (chat) => {
                const ajaxUrl = chat.dataset.ajaxUrl;
                const nonce = chat.dataset.nonce || chat.dataset.legacyNonce;
                const messagesNode = chat.querySelector('[data-role="messages"]');
                const form = chat.querySelector('[data-role="form"]');
                const isModerator = chat.dataset.isModerator === '1';
                const isBanned = chat.dataset.isBanned === '1';
                const enabled = chat.dataset.enabled !== '0';

                let lastId = 0;
                let country = { country: '', code: '' };

                if (enabled && !isBanned) {
                    country = await fetchCountry();
                }

                const renderMessage = (message) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'webinar-chat__message';
                    wrapper.dataset.messageId = message.id;
                    wrapper.dataset.userId = message.user_id;

                    const flag = message.country_code
                        ? `<img src="https://flagcdn.com/16x12/${message.country_code}.png" alt="" />`
                        : '';

                    wrapper.innerHTML = `
                        <div class="webinar-chat__message-body">
                            <div class="webinar-chat__message-head">
                                ${flag}
                                <span class="webinar-chat__message-name">${message.user_name}</span>
                                <span>${message.country || ''}</span>
                                <span>${formatTime(message.created_at)}</span>
                            </div>
                            <div class="webinar-chat__message-text"></div>
                            ${isModerator ? `
                                <div class="webinar-chat__message-actions">
                                    <button type="button" class="webinar-chat__action" data-action="delete">Удалить</button>
                                    <button type="button" class="webinar-chat__action" data-action="ban">Бан</button>
                                </div>
                            ` : ''}
                        </div>
                    `;

                    const textNode = wrapper.querySelector('.webinar-chat__message-text');
                    textNode.textContent = message.message;

                    if (isModerator) {
                        wrapper.querySelectorAll('[data-action]').forEach((button) => {
                            button.addEventListener('click', () => {
                                moderAction(button.dataset.action, wrapper.dataset.messageId, wrapper.dataset.userId);
                            });
                        });
                    }

                    messagesNode.appendChild(wrapper);
                    messagesNode.scrollTop = messagesNode.scrollHeight;
                };

                const handleFetch = async () => {
                    const body = new URLSearchParams();
                    body.append('action', 'webinar_chat_fetch');
                    body.append('nonce', nonce);
                    body.append('last_id', String(lastId));

                    const response = await fetch(ajaxUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString(),
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    if (!payload || !payload.success || !payload.data) {
                        return;
                    }

                    const messages = payload.data.messages || [];
                    messages.forEach((message) => {
                        renderMessage(message);
                        lastId = Math.max(lastId, Number(message.id));
                    });
                };

                const sendMessage = async (message) => {
                    const body = new URLSearchParams();
                    body.append('action', 'webinar_chat_send');
                    body.append('nonce', nonce);
                    body.append('message', message);
                    body.append('country', country.country || '');
                    body.append('country_code', country.code || '');

                    const response = await fetch(ajaxUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString(),
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    if (payload && payload.success && payload.data && payload.data.message) {
                        renderMessage(payload.data.message);
                        lastId = Math.max(lastId, Number(payload.data.message.id));
                    }
                };

                const moderAction = async (action, messageId, userId) => {
                    if (!isModerator) {
                        return;
                    }

                    const body = new URLSearchParams();
                    body.append('action', 'webinar_chat_moder');
                    body.append('nonce', nonce);
                    body.append('moder_action', action);
                    body.append('message_id', messageId || '');
                    body.append('user_id', userId || '');

                    await fetch(ajaxUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString(),
                    });

                    if (action === 'delete') {
                        const messageNode = messagesNode.querySelector(`[data-message-id="${messageId}"]`);
                        if (messageNode) {
                            messageNode.remove();
                        }
                    }
                };

                if (form) {
                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        const textarea = form.querySelector('textarea');
                        if (!textarea) {
                            return;
                        }
                        const message = textarea.value.trim();
                        if (!message) {
                            return;
                        }
                        textarea.value = '';
                        sendMessage(message);
                    });
                }

                handleFetch();
                setInterval(handleFetch, 5000);

                const clearButton = chat.querySelector('[data-action="clear"]');
                if (clearButton) {
                    clearButton.addEventListener('click', () => {
                        moderAction('clear', '', '');
                        messagesNode.innerHTML = '';
                        lastId = 0;
                    });
                }
            });
        })();
    </script>
    <?php

    return (string) ob_get_clean();
}

/**
 * @return void
 */
function webinar_chat_handle_fetch() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'forbidden'), 403);
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

    if (!webinar_chat_validate_nonce($nonce)) {
        wp_send_json_error(array('message' => 'invalid_nonce'), 400);
    }

    $last_id = isset($_POST['last_id']) ? absint($_POST['last_id']) : 0;

    global $wpdb;
    $table = webinar_chat_table_name();
    $columns = webinar_chat_get_table_columns();

    $where = '1=1';
    $params = array();

    if ($last_id > 0 && in_array('id', $columns, true)) {
        $where .= ' AND id > %d';
        $params[] = $last_id;
    }

    $deleted_column = null;
    foreach (array('is_deleted', 'deleted') as $candidate) {
        if (in_array($candidate, $columns, true)) {
            $deleted_column = $candidate;
            break;
        }
    }

    if ($deleted_column) {
        $where .= " AND {$deleted_column} = 0";
    }

    $sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY id ASC LIMIT 50";

    if (!empty($params)) {
        $sql = $wpdb->prepare($sql, $params);
    }

    $rows = $wpdb->get_results($sql, ARRAY_A);

    if (!is_array($rows)) {
        $rows = array();
    }

    $messages = array();
    foreach ($rows as $row) {
        $messages[] = array(
            'id' => (int) ($row['id'] ?? 0),
            'user_id' => (int) ($row['user_id'] ?? 0),
            'user_name' => (string) ($row['user_name'] ?? ''),
            'message' => (string) ($row['message'] ?? ''),
            'country' => (string) ($row['country'] ?? ''),
            'country_code' => strtolower((string) ($row['country_code'] ?? '')),
            'created_at' => (string) ($row['created_at'] ?? ''),
        );
    }

    wp_send_json_success(array('messages' => $messages));
}

/**
 * @return void
 */
function webinar_chat_handle_send() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'forbidden'), 403);
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

    if (!webinar_chat_validate_nonce($nonce)) {
        wp_send_json_error(array('message' => 'invalid_nonce'), 400);
    }

    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $message = trim($message);

    if ($message === '') {
        wp_send_json_error(array('message' => 'empty_message'), 422);
    }

    $user = wp_get_current_user();
    if (webinar_chat_is_banned($user)) {
        wp_send_json_error(array('message' => 'banned'), 403);
    }

    $country = isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : '';
    $country_code = isset($_POST['country_code']) ? sanitize_text_field(wp_unslash($_POST['country_code'])) : '';

    $data = array(
        'user_id' => (int) $user->ID,
        'user_login' => (string) $user->user_login,
        'user_name' => (string) $user->display_name,
        'message' => $message,
        'country' => $country,
        'country_code' => strtolower($country_code),
        'created_at' => current_time('mysql'),
        'is_deleted' => 0,
    );

    global $wpdb;
    $table = webinar_chat_table_name();
    $data = webinar_chat_filter_columns($data);

    if (empty($data)) {
        wp_send_json_error(array('message' => 'table_missing'), 500);
    }

    $inserted = $wpdb->insert($table, $data, webinar_chat_build_formats($data));

    if (!$inserted) {
        wp_send_json_error(array('message' => 'db_error'), 500);
    }

    $id = (int) $wpdb->insert_id;

    $response = array(
        'id' => $id,
        'user_id' => (int) $user->ID,
        'user_name' => (string) $user->display_name,
        'message' => $message,
        'country' => $country,
        'country_code' => strtolower($country_code),
        'created_at' => current_time('mysql'),
    );

    wp_send_json_success(array('message' => $response));
}

/**
 * @return void
 */
function webinar_chat_handle_moderation() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'forbidden'), 403);
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

    if (!webinar_chat_validate_nonce($nonce)) {
        wp_send_json_error(array('message' => 'invalid_nonce'), 400);
    }

    if (!webinar_chat_is_moderator()) {
        wp_send_json_error(array('message' => 'forbidden'), 403);
    }

    $action = isset($_POST['moder_action']) ? sanitize_text_field(wp_unslash($_POST['moder_action'])) : '';
    $message_id = isset($_POST['message_id']) ? absint($_POST['message_id']) : 0;
    $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;

    global $wpdb;
    $table = webinar_chat_table_name();
    $columns = webinar_chat_get_table_columns();

    switch ($action) {
        case 'delete':
            if ($message_id > 0) {
                if (in_array('is_deleted', $columns, true)) {
                    $wpdb->update($table, array('is_deleted' => 1), array('id' => $message_id), array('%d'), array('%d'));
                } elseif (in_array('deleted', $columns, true)) {
                    $wpdb->update($table, array('deleted' => 1), array('id' => $message_id), array('%d'), array('%d'));
                } else {
                    $wpdb->delete($table, array('id' => $message_id), array('%d'));
                }
            }
            break;
        case 'ban':
            if ($user_id > 0) {
                $banned = get_option(WEBINAR_CHAT_OPTION_BANNED, array());
                if (!is_array($banned)) {
                    $banned = array();
                }
                $user_id_str = (string) $user_id;
                if (!in_array($user_id_str, $banned, true)) {
                    $banned[] = $user_id_str;
                    update_option(WEBINAR_CHAT_OPTION_BANNED, $banned);
                }
            }
            break;
        case 'unban':
            if ($user_id > 0) {
                $banned = get_option(WEBINAR_CHAT_OPTION_BANNED, array());
                if (is_array($banned)) {
                    $banned = array_values(array_diff($banned, array((string) $user_id)));
                    update_option(WEBINAR_CHAT_OPTION_BANNED, $banned);
                }
            }
            break;
        case 'clear':
            $wpdb->query("DELETE FROM {$table}");
            break;
        default:
            wp_send_json_error(array('message' => 'unknown_action'), 422);
    }

    wp_send_json_success(array('status' => 'ok'));
}
