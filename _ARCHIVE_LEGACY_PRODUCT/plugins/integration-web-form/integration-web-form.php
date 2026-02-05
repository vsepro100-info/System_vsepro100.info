<?php
/**
 * Plugin Name: Integration Web Form
 * Description: Интеграция веб-формы с Core Ingest
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_shortcode('core_web_form', 'integration_web_form_render_shortcode');
add_action('admin_post_core_web_form_submit', 'integration_web_form_handle_submit');
add_action('admin_post_nopriv_core_web_form_submit', 'integration_web_form_handle_submit');

function integration_web_form_render_shortcode() {
    $success = isset($_GET['success']) && $_GET['success'] === '1';

    ob_start();
    if ($success) {
        echo '<p>' . esc_html__('Спасибо! Заявка отправлена.', 'integration-web-form') . '</p>';
    }
    ?>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="core_web_form_submit">
        <?php wp_nonce_field('core_web_form_submit', 'core_web_form_nonce'); ?>
        <p>
            <label for="core-web-form-name"><?php echo esc_html__('Name', 'integration-web-form'); ?></label>
            <input type="text" id="core-web-form-name" name="name" required>
        </p>
        <p>
            <label for="core-web-form-email"><?php echo esc_html__('Email', 'integration-web-form'); ?></label>
            <input type="email" id="core-web-form-email" name="email" required>
        </p>
        <p>
            <button type="submit"><?php echo esc_html__('Submit', 'integration-web-form'); ?></button>
        </p>
    </form>
    <?php
    return ob_get_clean();
}

function integration_web_form_handle_submit() {
    if (
        !isset($_POST['core_web_form_nonce'])
        || !wp_verify_nonce(wp_unslash($_POST['core_web_form_nonce']), 'core_web_form_submit')
    ) {
        wp_die(esc_html__('Invalid request.', 'integration-web-form'));
    }

    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';

    $payload = array(
        'source' => 'web_form',
        'name' => $name,
        'email' => $email,
    );

    do_action('core_ingest_event', $payload);

    $redirect = wp_get_referer();
    if (!$redirect) {
        $redirect = home_url('/');
    }

    wp_safe_redirect(add_query_arg('success', '1', $redirect));
    exit;
}
