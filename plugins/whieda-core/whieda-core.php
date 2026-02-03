<?php
/**
 * Plugin Name: WHIEDA Core
 * Description: Канонический перенос legacy-логики WHIEDA в плагины (реферальный контур, формы, роли, контакты).
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

define('WHIEDA_REF_COOKIE', 'wh_ref');

define('WHIEDA_REF_COOKIE_TTL', 30 * DAY_IN_SECONDS);

define('WHIEDA_REF_CLICKS_META', 'ref_total_clicks');

define('WHIEDA_CONTACT_FORM_ACTION', 'whieda_public_contact_submit');

define('WHIEDA_REGISTER_ACTION', 'whieda_register_submit');

/**
 * Ensures core roles are registered.
 */
function whieda_core_register_roles() {
    $roles = array(
        'candidate' => array(
            'name' => 'Candidate',
            'caps' => array('read' => true),
        ),
        'pending_partner' => array(
            'name' => 'Pending Partner',
            'caps' => array('read' => true),
        ),
        'partner' => array(
            'name' => 'Partner',
            'caps' => array('read' => true),
        ),
        'moderator' => array(
            'name' => 'Moderator',
            'caps' => array('read' => true),
        ),
    );

    foreach ($roles as $role => $data) {
        if (get_role($role)) {
            continue;
        }

        add_role($role, $data['name'], $data['caps']);
    }
}

register_activation_hook(__FILE__, 'whieda_core_register_roles');
add_action('init', 'whieda_core_register_roles');

/**
 * Captures referral context from ?ref= and writes to cookie.
 */
function whieda_core_capture_ref_cookie() {
    if (!isset($_GET['ref'])) {
        return;
    }

    $ref = sanitize_user(wp_unslash($_GET['ref']));
    if ($ref === '') {
        return;
    }

    $user = get_user_by('login', $ref);
    if (!$user) {
        return;
    }

    $clicks = (int) get_user_meta($user->ID, WHIEDA_REF_CLICKS_META, true);
    update_user_meta($user->ID, WHIEDA_REF_CLICKS_META, $clicks + 1);

    $expires = time() + WHIEDA_REF_COOKIE_TTL;
    setcookie(WHIEDA_REF_COOKIE, $ref, $expires, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
    $_COOKIE[WHIEDA_REF_COOKIE] = $ref;
}

add_action('init', 'whieda_core_capture_ref_cookie', 1);

/**
 * Resolves a valid referral login.
 *
 * @return string
 */
function whieda_core_resolve_ref_login() {
    $ref = '';
    if (isset($_GET['ref'])) {
        $ref = sanitize_user(wp_unslash($_GET['ref']));
    }

    if ($ref === '' && isset($_COOKIE[WHIEDA_REF_COOKIE])) {
        $ref = sanitize_user(wp_unslash($_COOKIE[WHIEDA_REF_COOKIE]));
    }

    if ($ref === '' && is_user_logged_in()) {
        $invited_by = get_user_meta(get_current_user_id(), 'invited_by', true);
        if (is_string($invited_by) && $invited_by !== '') {
            $ref = sanitize_user($invited_by);
        }
    }

    if ($ref === '') {
        return '';
    }

    $user = get_user_by('login', $ref);
    if (!$user) {
        return '';
    }

    return $ref;
}

/**
 * Returns the referral user object if available.
 *
 * @return WP_User|null
 */
function whieda_core_get_ref_user() {
    $ref = whieda_core_resolve_ref_login();
    if ($ref === '') {
        return null;
    }

    $user = get_user_by('login', $ref);
    if (!$user) {
        return null;
    }

    return $user;
}

/**
 * Returns the account dashboard URL.
 *
 * @return string
 */
function whieda_core_get_account_url() {
    $url = home_url('/account/');
    return apply_filters('whieda_account_url', $url);
}

/**
 * Returns the contact page URL.
 *
 * @return string
 */
function whieda_core_get_contact_url() {
    $url = home_url('/contact/');
    return apply_filters('whieda_contact_url', $url);
}

/**
 * Returns a safe redirect URL with query args.
 *
 * @param array $args
 * @return string
 */
function whieda_core_get_redirect_url(array $args) {
    $redirect = wp_get_referer();
    if (!$redirect) {
        $redirect = home_url('/');
    }

    return add_query_arg($args, $redirect);
}

/**
 * Registration form shortcode.
 */
function whieda_core_render_register_shortcode() {
    $error = isset($_GET['whieda_error']) ? sanitize_text_field(wp_unslash($_GET['whieda_error'])) : '';
    $notice = isset($_GET['whieda_notice']) ? sanitize_text_field(wp_unslash($_GET['whieda_notice'])) : '';

    ob_start();
    if ($error) {
        echo '<p class="whieda-form-error">' . esc_html($error) . '</p>';
    }
    if ($notice) {
        echo '<p class="whieda-form-notice">' . esc_html($notice) . '</p>';
    }
    ?>
    <form class="whieda-register-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="<?php echo esc_attr(WHIEDA_REGISTER_ACTION); ?>">
        <?php wp_nonce_field('whieda_register_submit', 'whieda_register_nonce'); ?>
        <p>
            <label for="whieda-register-login"><?php echo esc_html__('Логин', 'whieda-core'); ?></label>
            <input type="text" id="whieda-register-login" name="user_login" required>
        </p>
        <p>
            <label for="whieda-register-email"><?php echo esc_html__('Email', 'whieda-core'); ?></label>
            <input type="email" id="whieda-register-email" name="user_email" required>
        </p>
        <p>
            <label for="whieda-register-password"><?php echo esc_html__('Пароль', 'whieda-core'); ?></label>
            <input type="password" id="whieda-register-password" name="user_password" required>
        </p>
        <p>
            <button type="submit"><?php echo esc_html__('Зарегистрироваться', 'whieda-core'); ?></button>
        </p>
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('whieda_register', 'whieda_core_render_register_shortcode');

/**
 * Handles registration form submission.
 */
function whieda_core_handle_register_submit() {
    if (
        !isset($_POST['whieda_register_nonce'])
        || !wp_verify_nonce(wp_unslash($_POST['whieda_register_nonce']), 'whieda_register_submit')
    ) {
        wp_die(esc_html__('Invalid request.', 'whieda-core'));
    }

    $login = isset($_POST['user_login']) ? sanitize_user(wp_unslash($_POST['user_login'])) : '';
    $email = isset($_POST['user_email']) ? sanitize_email(wp_unslash($_POST['user_email'])) : '';
    $password = isset($_POST['user_password']) ? wp_unslash($_POST['user_password']) : '';

    if ($login === '' || $email === '' || $password === '') {
        wp_safe_redirect(whieda_core_get_redirect_url(array(
            'whieda_error' => __('Заполните все обязательные поля.', 'whieda-core'),
        )));
        exit;
    }

    if (username_exists($login)) {
        wp_safe_redirect(whieda_core_get_redirect_url(array(
            'whieda_error' => __('Этот логин уже используется.', 'whieda-core'),
        )));
        exit;
    }

    if (email_exists($email)) {
        wp_safe_redirect(whieda_core_get_redirect_url(array(
            'whieda_error' => __('Этот email уже используется.', 'whieda-core'),
        )));
        exit;
    }

    $user_id = wp_insert_user(array(
        'user_login' => $login,
        'user_pass' => $password,
        'user_email' => $email,
        'role' => 'candidate',
    ));

    if (is_wp_error($user_id) || !$user_id) {
        wp_safe_redirect(whieda_core_get_redirect_url(array(
            'whieda_error' => __('Не удалось создать пользователя.', 'whieda-core'),
        )));
        exit;
    }

    $ref_login = whieda_core_resolve_ref_login();
    if ($ref_login !== '') {
        update_user_meta($user_id, 'invited_by', $ref_login);
    }

    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    wp_safe_redirect(whieda_core_get_account_url());
    exit;
}

add_action('admin_post_' . WHIEDA_REGISTER_ACTION, 'whieda_core_handle_register_submit');
add_action('admin_post_nopriv_' . WHIEDA_REGISTER_ACTION, 'whieda_core_handle_register_submit');

/**
 * Renders a contact card for a partner by login.
 *
 * @param WP_User $user
 * @return string
 */
function whieda_core_render_contact_card(WP_User $user) {
    $first_name = get_user_meta($user->ID, 'first_name', true);
    $last_name = get_user_meta($user->ID, 'last_name', true);
    $about = get_user_meta($user->ID, 'about', true);
    $photo = get_user_meta($user->ID, 'photo', true);
    $telegram = get_user_meta($user->ID, 'telegram', true);
    $whatsapp = get_user_meta($user->ID, 'whatsapp', true);
    $email = get_user_meta($user->ID, 'email', true);
    if ($email === '') {
        $email = $user->user_email;
    }

    $name = trim($first_name . ' ' . $last_name);
    if ($name === '') {
        $name = $user->display_name;
    }

    ob_start();
    ?>
    <div class="whieda-contact-card">
        <?php if ($photo) : ?>
            <div class="whieda-contact-card__photo">
                <img src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr($name); ?>">
            </div>
        <?php endif; ?>
        <div class="whieda-contact-card__body">
            <h2 class="whieda-contact-card__name"><?php echo esc_html($name); ?></h2>
            <?php if ($about) : ?>
                <div class="whieda-contact-card__about"><?php echo wp_kses_post(wpautop($about)); ?></div>
            <?php endif; ?>
            <ul class="whieda-contact-card__contacts">
                <?php if ($email) : ?>
                    <li><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></li>
                <?php endif; ?>
                <?php if ($telegram) : ?>
                    <li><a href="https://t.me/<?php echo esc_attr(ltrim($telegram, '@')); ?>" target="_blank" rel="noopener noreferrer">@<?php echo esc_html(ltrim($telegram, '@')); ?></a></li>
                <?php endif; ?>
                <?php if ($whatsapp) : ?>
                    <li><a href="https://wa.me/<?php echo esc_attr(preg_replace('/\D/', '', $whatsapp)); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($whatsapp); ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Shortcode: [whieda_contact_card]
 */
function whieda_core_render_contact_card_shortcode($atts = array()) {
    $atts = shortcode_atts(array('ref' => ''), $atts, 'whieda_contact_card');

    $ref = sanitize_user($atts['ref']);
    if ($ref === '') {
        $ref = whieda_core_resolve_ref_login();
    }

    if ($ref === '') {
        return '<p>' . esc_html__('Консультант не определён.', 'whieda-core') . '</p>';
    }

    $user = get_user_by('login', $ref);
    if (!$user) {
        return '<p>' . esc_html__('Консультант не найден.', 'whieda-core') . '</p>';
    }

    return whieda_core_render_contact_card($user);
}

add_shortcode('whieda_contact_card', 'whieda_core_render_contact_card_shortcode');
add_shortcode('partner_contact_info', 'whieda_core_render_contact_card_shortcode');

/**
 * Shortcode: [public_contact_info]
 */
function whieda_core_render_public_contact_shortcode() {
    $user = whieda_core_get_ref_user();
    if ($user) {
        return whieda_core_render_contact_card($user);
    }

    $notice = isset($_GET['whieda_notice']) ? sanitize_text_field(wp_unslash($_GET['whieda_notice'])) : '';

    ob_start();
    if ($notice) {
        echo '<p class="whieda-form-notice">' . esc_html($notice) . '</p>';
    }
    ?>
    <form class="whieda-contact-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="<?php echo esc_attr(WHIEDA_CONTACT_FORM_ACTION); ?>">
        <?php wp_nonce_field('whieda_public_contact_submit', 'whieda_public_contact_nonce'); ?>
        <p>
            <label for="whieda-contact-name"><?php echo esc_html__('Имя', 'whieda-core'); ?></label>
            <input type="text" id="whieda-contact-name" name="name" required>
        </p>
        <p>
            <label for="whieda-contact-contact"><?php echo esc_html__('Контакт (телефон или email)', 'whieda-core'); ?></label>
            <input type="text" id="whieda-contact-contact" name="contact" required>
        </p>
        <p>
            <label for="whieda-contact-message"><?php echo esc_html__('Комментарий', 'whieda-core'); ?></label>
            <textarea id="whieda-contact-message" name="message" rows="4"></textarea>
        </p>
        <p>
            <button type="submit"><?php echo esc_html__('Отправить', 'whieda-core'); ?></button>
        </p>
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('public_contact_info', 'whieda_core_render_public_contact_shortcode');

/**
 * Handles public contact form submission.
 */
function whieda_core_handle_public_contact_submit() {
    if (
        !isset($_POST['whieda_public_contact_nonce'])
        || !wp_verify_nonce(wp_unslash($_POST['whieda_public_contact_nonce']), 'whieda_public_contact_submit')
    ) {
        wp_die(esc_html__('Invalid request.', 'whieda-core'));
    }

    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $contact = isset($_POST['contact']) ? sanitize_text_field(wp_unslash($_POST['contact'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

    $payload = array(
        'source' => 'public_contact',
        'name' => $name,
        'contact' => $contact,
        'message' => $message,
        'page_url' => wp_get_referer(),
    );

    $ref = whieda_core_resolve_ref_login();
    if ($ref !== '') {
        $payload['ref'] = $ref;
    } else {
        $payload['needs_assignment'] = '1';
    }

    do_action('core_ingest_event', $payload);

    wp_safe_redirect(whieda_core_get_redirect_url(array(
        'whieda_notice' => __('Спасибо! Мы свяжемся с вами.', 'whieda-core'),
    )));
    exit;
}

add_action('admin_post_' . WHIEDA_CONTACT_FORM_ACTION, 'whieda_core_handle_public_contact_submit');
add_action('admin_post_nopriv_' . WHIEDA_CONTACT_FORM_ACTION, 'whieda_core_handle_public_contact_submit');

/**
 * Shortcode: [whieda_protected_content access="role1,role2"]
 */
function whieda_core_render_protected_content_shortcode($atts, $content = '') {
    $atts = shortcode_atts(array('access' => ''), $atts, 'whieda_protected_content');
    $roles = array_filter(array_map('trim', explode(',', $atts['access'])));

    if (empty($roles)) {
        return do_shortcode($content);
    }

    if (!is_user_logged_in()) {
        $message = __('Доступ запрещён.', 'whieda-core');
        return '<p class="whieda-protected-content__denied">' . esc_html(apply_filters('whieda_protected_content_denied_message', $message)) . '</p>';
    }

    $user = wp_get_current_user();
    if (!array_intersect($roles, (array) $user->roles)) {
        $message = __('Доступ запрещён.', 'whieda-core');
        return '<p class="whieda-protected-content__denied">' . esc_html(apply_filters('whieda_protected_content_denied_message', $message)) . '</p>';
    }

    return do_shortcode($content);
}

add_shortcode('whieda_protected_content', 'whieda_core_render_protected_content_shortcode');

/**
 * Shortcode: [whieda_main_cta]
 */
function whieda_core_render_main_cta_shortcode($atts = array()) {
    $atts = shortcode_atts(array(
        'text' => __('Мой консультант', 'whieda-core'),
        'url' => whieda_core_get_contact_url(),
    ), $atts, 'whieda_main_cta');

    $url = $atts['url'];
    $ref = whieda_core_resolve_ref_login();
    if ($ref !== '') {
        $url = add_query_arg('ref', $ref, $url);
    }

    return '<a class="whieda-main-cta" href="' . esc_url($url) . '">' . esc_html($atts['text']) . '</a>';
}

add_shortcode('whieda_main_cta', 'whieda_core_render_main_cta_shortcode');
