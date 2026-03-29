<?php
if (!defined('ABSPATH')) exit;

class VGT_Core {
    public static function init() {
        $mode = get_option('vgt_shield_mode', 'global');

        if ($mode === 'global') {
            add_action('wp_enqueue_scripts', [__CLASS__, 'inject_engine']);
        }

        add_shortcode('vgt_shield', [__CLASS__, 'render_shortcode']);

        add_action('rest_api_init', function () {
            register_rest_route('vgt-shield/v1', '/challenge', [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'api_get_challenge'],
                'permission_callback' => '__return_true'
            ]);
        });

        // VGT Supreme System Validatoren (Jetzt mit Header-Support)
        add_filter('preprocess_comment', [__CLASS__, 'validate_comment_submission']);
        add_action('wpcf7_before_send_mail', [__CLASS__, 'validate_cf7_submission'], 10, 3);
        add_filter('woocommerce_process_registration_errors', [__CLASS__, 'validate_woo_auth'], 10, 2);
        add_action('woocommerce_process_login_errors', [__CLASS__, 'validate_woo_auth'], 10, 3);
        add_action('woocommerce_after_checkout_validation', [__CLASS__, 'validate_woo_checkout'], 10, 2);
        add_filter('wpforms_process_initial_errors', [__CLASS__, 'validate_wpforms'], 10, 2);
        add_filter('gform_validation', [__CLASS__, 'validate_gform']);
        
        self::attach_dynamic_hooks();
    }

    private static function attach_dynamic_hooks() {
        $custom_hooks = get_option('vgt_shield_custom_hooks', []);
        if (!is_array($custom_hooks)) return;
        foreach ($custom_hooks as $hook) {
            add_action($hook, [__CLASS__, 'validate_dynamic_hook'], 1, 0); 
        }
    }

    public static function validate_dynamic_hook() {
        $payload = VGT_Security::get_pow_payload();
        if (empty($payload) && empty($_POST)) return; 
        if (!VGT_Security::validate_pow($payload)) {
            wp_die('VGT-SHIELD: Dynamic Security Matrix Triggered. Access Denied.');
        }
    }

    public static function validate_woo_auth($validation_error, $username = '', $password = '') {
        if (!VGT_Security::validate_pow(VGT_Security::get_pow_payload())) {
            $validation_error->add('vgt_error', '<strong>VGT Shield</strong>: Security Validation Failed.');
        }
        return $validation_error;
    }

    public static function validate_woo_checkout($data, $errors) {
        if (!VGT_Security::validate_pow(VGT_Security::get_pow_payload())) {
            $errors->add('vgt_error', '<strong>VGT Shield</strong>: Checkout Security Validation Failed.');
        }
    }

    public static function validate_wpforms($errors, $form_data) {
        if (!VGT_Security::validate_pow(VGT_Security::get_pow_payload())) {
            $errors[$form_data['id']]['header'] = 'VGT-SHIELD: Security Matrix Validation Failed.';
        }
        return $errors;
    }

    public static function validate_gform($validation_result) {
        if (!VGT_Security::validate_pow(VGT_Security::get_pow_payload())) {
            $validation_result['is_valid'] = false;
            $validation_result['form']['vgt_error'] = 'VGT-SHIELD: Validation Failed.';
        }
        return $validation_result;
    }

    public static function render_shortcode() {
        self::inject_engine();
        return '<div class="vgt-shield-anchor" style="display:none;" data-vgt-status="active"></div>';
    }

    public static function api_get_challenge() {
        return rest_ensure_response(VGT_Security::generate_challenge());
    }

    public static function inject_engine() {
        if (wp_script_is('vgt-shield-engine', 'enqueued')) return;

        wp_enqueue_script(
            'vgt-shield-engine', 
            VGT_SHIELD_URL . 'assets/js/vgt-shield-engine.js', 
            [], 
            '5.0.0', 
            true
        );

        wp_localize_script('vgt-shield-engine', 'vgtShieldConfig', [
            'apiUrl' => esc_url_raw(rest_url('vgt-shield/v1/challenge')),
            'workerUrl' => esc_url_raw(VGT_SHIELD_URL . 'assets/js/vgt-worker.js')
        ]);
    }

    public static function validate_comment_submission($commentdata) {
        if (!VGT_Security::validate_pow(VGT_Security::get_pow_payload())) {
            wp_die('VGT-SHIELD: Access Denied. Kognitive Anomalie erkannt (Proof-of-Work failed).');
        }
        return $commentdata;
    }

    public static function validate_cf7_submission($contact_form, &$abort, $submission) {
        if (!VGT_Security::validate_pow(VGT_Security::get_pow_payload())) {
            $abort = true;
            $submission->set_status('validation_failed');
            $submission->set_response('VGT-SHIELD: Access Denied. Security Matrix Validation Failed.');
        }
    }
}