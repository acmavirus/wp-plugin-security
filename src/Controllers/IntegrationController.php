<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Controllers;

/**
 * Controller cho cac tich hop: SMTP, Google, WooCommerce, Marketing va code injection.
 */
class IntegrationController
{
    public function __construct()
    {
        add_action('phpmailer_init', [$this, 'configure_smtp']);
        add_filter('wp_mail_from', [$this, 'filter_mail_from']);
        add_filter('wp_mail_from_name', [$this, 'filter_mail_from_name']);

        add_action('wp_head', [$this, 'render_head_injection'], 20);
        add_action('wp_body_open', [$this, 'render_notification_bar'], 5);
        add_action('wp_footer', [$this, 'render_footer_widgets'], 20);

        add_action('template_redirect', [$this, 'maybe_apply_redirects'], 1);
        add_filter('authenticate', [$this, 'maybe_verify_recaptcha_on_login'], 20, 3);
        add_action('login_form', [$this, 'render_login_integrations']);
        add_action('login_enqueue_scripts', [$this, 'render_login_assets']);

        add_action('init', [$this, 'maybe_start_google_login'], 5);
        add_action('admin_post_nopriv_wps_google_callback', [$this, 'handle_google_callback']);
        add_action('admin_post_wps_google_callback', [$this, 'handle_google_callback']);

        add_action('transition_post_status', [$this, 'maybe_send_google_indexing'], 10, 3);
        add_action('init', [$this, 'register_woocommerce_hooks'], 20);
    }

    public function configure_smtp($phpmailer)
    {
        if (!$this->get_setting('smtp_enabled', false)) {
            return;
        }

        $host = trim((string) $this->get_setting('smtp_host', ''));
        if ($host === '') {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host = $host;
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = (int) $this->get_setting('smtp_port', 587);
        $phpmailer->Username = (string) $this->get_setting('smtp_username', '');
        $phpmailer->Password = (string) $this->get_setting('smtp_password', '');

        $encryption = (string) $this->get_setting('smtp_encryption', 'tls');
        if (in_array($encryption, ['ssl', 'tls'], true)) {
            $phpmailer->SMTPSecure = $encryption;
        }

        $phpmailer->From = (string) $this->get_setting('smtp_from_email', get_option('admin_email'));
        $phpmailer->FromName = (string) $this->get_setting('smtp_from_name', get_bloginfo('name'));
    }

    public function filter_mail_from($from)
    {
        if (!$this->get_setting('smtp_enabled', false)) {
            return $from;
        }

        return (string) $this->get_setting('smtp_from_email', $from);
    }

    public function filter_mail_from_name($name)
    {
        if (!$this->get_setting('smtp_enabled', false)) {
            return $name;
        }

        return (string) $this->get_setting('smtp_from_name', $name);
    }

    public function render_head_injection()
    {
        $code = (string) $this->get_setting('code_inject_head', '');
        if ($code !== '') {
            echo "\n" . $code . "\n";
        }
    }

    public function render_footer_widgets()
    {
        $footer_code = (string) $this->get_setting('code_inject_footer', '');
        if ($footer_code !== '') {
            echo "\n" . $footer_code . "\n";
        }

        $this->render_chat_bubbles();
    }

    public function render_notification_bar()
    {
        if (!$this->get_setting('notification_bar_enabled', false)) {
            return;
        }

        $text = (string) $this->get_setting('notification_bar_text', '');
        if ($text === '') {
            return;
        }

        $position = (string) $this->get_setting('notification_bar_position', 'top');
        $link = esc_url($this->get_setting('notification_bar_link', ''));
        $button = (string) $this->get_setting('notification_bar_button', __('Xem thêm', 'acma-security-shield'));

        $style = 'position:sticky;top:0;z-index:9999;background:linear-gradient(135deg,#1167ad,#003b6b);color:#fff;padding:12px 16px;text-align:center;font-size:14px;';
        if ($position === 'bottom') {
            $style = 'position:sticky;bottom:0;z-index:9999;background:linear-gradient(135deg,#1167ad,#003b6b);color:#fff;padding:12px 16px;text-align:center;font-size:14px;';
        }

        echo '<div class="wps-notification-bar" style="' . esc_attr($style) . '">';
        echo '<span>' . esc_html($text) . '</span>';
        if ($link !== '') {
            echo ' <a href="' . esc_url($link) . '" style="margin-left:12px;color:#fff;font-weight:700;text-decoration:underline;">' . esc_html($button) . '</a>';
        }
        echo '</div>';
    }

    public function render_chat_bubbles()
    {
        if (!$this->get_setting('chat_enabled', false)) {
            return;
        }

        $channels = [
            'chat_phone' => ['label' => __('Điện thoại', 'acma-security-shield'), 'icon' => 'tel:+'],
            'chat_sms' => ['label' => __('Tin nhắn', 'acma-security-shield'), 'icon' => 'sms:'],
            'chat_zalo' => ['label' => 'Zalo', 'icon' => 'https://zalo.me/'],
            'chat_messenger' => ['label' => 'Messenger', 'icon' => 'https://m.me/'],
            'chat_telegram' => ['label' => 'Telegram', 'icon' => 'https://t.me/'],
            'chat_whatsapp' => ['label' => 'WhatsApp', 'icon' => 'https://wa.me/'],
        ];

        $items = [];
        foreach ($channels as $key => $meta) {
            $value = trim((string) $this->get_setting($key, ''));
            if ($value === '') {
                continue;
            }

            $url = $value;
            if ($key === 'chat_phone' && strpos($value, 'tel:') !== 0) {
                $url = 'tel:' . preg_replace('/\s+/', '', $value);
            } elseif ($key === 'chat_sms' && strpos($value, 'sms:') !== 0) {
                $url = 'sms:' . preg_replace('/\s+/', '', $value);
            } elseif (in_array($key, ['chat_zalo', 'chat_messenger', 'chat_telegram', 'chat_whatsapp'], true)) {
                $url = $value;
            }

            $items[] = '<a class="wps-chat-item" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;margin:6px;border-radius:999px;background:#fff;color:#003b6b;text-decoration:none;box-shadow:0 10px 24px rgba(1,34,61,.12);font-weight:600;">' . esc_html($meta['label']) . '</a>';
        }

        if (empty($items)) {
            return;
        }

        echo '<div class="wps-chat-stack" style="position:fixed;right:18px;bottom:18px;z-index:9998;display:flex;flex-direction:column;align-items:flex-end;">' . implode('', $items) . '</div>';
    }

    public function maybe_apply_redirects()
    {
        $rules = (string) $this->get_setting('redirect_rules', '');
        if (trim($rules) === '') {
            return;
        }

        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $site_url = home_url('/');
        foreach (preg_split("/\r\n|\n|\r/", $rules) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '|') === false) {
                continue;
            }

            [$from, $to, $code] = array_pad(array_map('trim', explode('|', $line)), 3, '301');
            if ($from === '' || $to === '') {
                continue;
            }

            if (strpos($request_uri, $from) !== false) {
                wp_safe_redirect($this->normalize_redirect_target($to, $site_url), (int) $code ?: 301);
                exit;
            }
        }
    }

    public function maybe_verify_recaptcha_on_login($user, $username, $password)
    {
        if (!$this->get_setting('recaptcha_enabled', false)) {
            return $user;
        }

        if (!empty($user) && !is_wp_error($user)) {
            return $user;
        }

        $token = sanitize_text_field($_POST['wps_recaptcha_response'] ?? '');
        if ($token === '' || !$this->verify_recaptcha($token)) {
            return new \WP_Error('wps_recaptcha_failed', __('Xác thực reCAPTCHA thất bại.', 'acma-security-shield'));
        }

        return $user;
    }

    public function render_login_assets()
    {
        if (!$this->get_setting('recaptcha_enabled', false)) {
            return;
        }

        $site_key = (string) $this->get_setting('recaptcha_site_key', '');
        if ($site_key === '') {
            return;
        }

        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
        wp_add_inline_script('google-recaptcha', 'window.wpsRecaptchaSiteKey=' . wp_json_encode($site_key) . ';');
    }

    public function render_login_integrations()
    {
        if ($this->get_setting('google_login_enabled', false)) {
            $url = $this->get_google_login_url();
            if ($url) {
                echo '<p style="margin:16px 0;"><a class="button button-primary" href="' . esc_url($url) . '" style="width:100%;text-align:center;">' . esc_html__('Đăng nhập bằng Google', 'acma-security-shield') . '</a></p>';
            }
        }

        if ($this->get_setting('recaptcha_enabled', false)) {
            $site_key = (string) $this->get_setting('recaptcha_site_key', '');
            if ($site_key !== '') {
                echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '" style="margin:12px 0;"></div>';
                echo '<input type="hidden" name="wps_recaptcha_response" id="wps_recaptcha_response" value="">';
                echo '<script>document.addEventListener("DOMContentLoaded",function(){if(typeof grecaptcha!=="undefined"&&window.wpsRecaptchaSiteKey){var form=document.getElementById("loginform");if(form){form.addEventListener("submit",function(){var token=grecaptcha.getResponse();document.getElementById("wps_recaptcha_response").value=token;});}}});</script>';
            }
        }
    }

    public function maybe_start_google_login()
    {
        if (!isset($_GET['wps_google_login'])) {
            return;
        }

        if (!$this->get_setting('google_login_enabled', false)) {
            wp_die(__('Đăng nhập bằng Google đã bị tắt.', 'acma-security-shield'));
        }

        $auth_url = $this->build_google_auth_url();
        if (!$auth_url) {
            wp_die(__('Chưa cấu hình đăng nhập bằng Google.', 'acma-security-shield'));
        }

        wp_safe_redirect($auth_url);
        exit;
    }

    public function handle_google_callback()
    {
        if (!$this->get_setting('google_login_enabled', false)) {
            wp_die(__('Đăng nhập bằng Google đã bị tắt.', 'acma-security-shield'));
        }

        $code = sanitize_text_field($_GET['code'] ?? '');
        $state = sanitize_text_field($_GET['state'] ?? '');
        if ($state === '' || !wp_verify_nonce($state, 'wps_google_login_state')) {
            wp_die(__('Trạng thái đăng nhập Google không hợp lệ.', 'acma-security-shield'));
        }

        if ($code === '') {
            wp_safe_redirect(wp_login_url());
            exit;
        }

        $token = $this->exchange_google_code_for_token($code);
        if (!$token) {
            wp_safe_redirect(wp_login_url() . '?login=failed');
            exit;
        }

        $user_info = $this->fetch_google_userinfo($token);
        if (!$user_info || empty($user_info['email'])) {
            wp_safe_redirect(wp_login_url() . '?login=failed');
            exit;
        }

        $user = get_user_by('email', sanitize_email($user_info['email']));
        if (!$user) {
            $username = sanitize_user(current(explode('@', $user_info['email'])), true);
            $username = $this->ensure_unique_username($username);
            $user_id = wp_create_user($username, wp_generate_password(32, true, true), $user_info['email']);
            if (is_wp_error($user_id)) {
                wp_safe_redirect(wp_login_url() . '?login=failed');
                exit;
            }
            $user = get_user_by('id', $user_id);
        }

        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        do_action('wp_login', $user->user_login, $user);
        wp_safe_redirect(admin_url());
        exit;
    }

    public function maybe_send_google_indexing($new_status, $old_status, $post)
    {
        if (!$this->get_setting('google_indexing_enabled', false)) {
            return;
        }

        if ($new_status !== 'publish' || $old_status === 'publish') {
            return;
        }

        if (!empty($post->post_password) || $post->post_status !== 'publish') {
            return;
        }

        $types = (array) $this->get_setting('google_indexing_post_types', ['post']);
        if (!in_array($post->post_type, $types, true)) {
            return;
        }

        $this->send_url_to_google_indexing(get_permalink($post));
    }

    public function filter_add_to_cart_text($text, $product = null)
    {
        return (string) $this->get_setting('woo_add_to_cart_text', $text);
    }

    public function filter_add_to_cart_text_single($text, $product = null)
    {
        return (string) $this->get_setting('woo_add_to_cart_text', $text);
    }

    public function filter_price_html($price, $product)
    {
        if (!$product) {
            return $price;
        }

        $numeric = (float) $product->get_price();
        if ($numeric <= 0) {
            return esc_html((string) $this->get_setting('woo_price_zero_text', __('Liên hệ', 'acma-security-shield')));
        }

        return $price;
    }

    public function filter_empty_price_html($price, $product)
    {
            return esc_html((string) $this->get_setting('woo_price_zero_text', __('Liên hệ', 'acma-security-shield')));
    }

    public function register_woocommerce_hooks()
    {
        if (!class_exists('\WooCommerce') && !function_exists('WC')) {
            return;
        }

        add_filter('woocommerce_product_add_to_cart_text', [$this, 'filter_add_to_cart_text'], 20, 2);
        add_filter('woocommerce_product_single_add_to_cart_text', [$this, 'filter_add_to_cart_text_single'], 20, 2);
        add_filter('woocommerce_get_price_html', [$this, 'filter_price_html'], 20, 2);
        add_filter('woocommerce_empty_price_html', [$this, 'filter_empty_price_html'], 20, 2);
        add_action('woocommerce_thankyou', [$this, 'send_telegram_order_alert'], 20, 1);
    }

    public function send_telegram_order_alert($order_id)
    {
        if (!$this->get_setting('woo_telegram_enabled', false)) {
            return;
        }

        $bot_token = trim((string) $this->get_setting('woo_telegram_bot_token', ''));
        $chat_id = trim((string) $this->get_setting('woo_telegram_chat_id', ''));
        if ($bot_token === '' || $chat_id === '') {
            return;
        }

        $order = function_exists('wc_get_order') ? wc_get_order($order_id) : null;
        if (!$order) {
            return;
        }

        $message = sprintf(
            __("Đơn hàng mới #%s\nKhách hàng: %s\nTổng tiền: %s\nTrạng thái: %s", 'acma-security-shield'),
            $order->get_order_number(),
            $order->get_formatted_billing_full_name(),
            html_entity_decode(wp_strip_all_tags($order->get_formatted_order_total())),
            $order->get_status()
        );

        wp_remote_post('https://api.telegram.org/bot' . rawurlencode($bot_token) . '/sendMessage', [
            'timeout' => 10,
            'body' => [
                'chat_id' => $chat_id,
                'text' => $message,
            ],
        ]);
    }

    public function send_url_to_google_indexing($url)
    {
        $service_account_json = trim((string) $this->get_setting('google_service_account_json', ''));
        $project_id = trim((string) $this->get_setting('google_indexing_project_id', ''));
        if ($service_account_json === '' || $project_id === '') {
            return false;
        }

        $credentials = json_decode($service_account_json, true);
        if (empty($credentials['client_email']) || empty($credentials['private_key'])) {
            return false;
        }

        $access_token = $this->get_google_access_token($credentials);
        if (!$access_token) {
            return false;
        }

        $response = wp_remote_post('https://indexing.googleapis.com/v3/urlNotifications:publish', [
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'url' => $url,
                'type' => 'URL_UPDATED',
            ]),
        ]);

        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) >= 200 && wp_remote_retrieve_response_code($response) < 300;
    }

    public function get_google_login_url()
    {
        $client_id = trim((string) $this->get_setting('google_client_id', ''));
        if ($client_id === '') {
            return false;
        }

        $redirect_uri = (string) $this->get_setting('google_redirect_uri', admin_url('admin-post.php?action=wps_google_callback'));
        $state = wp_create_nonce('wps_google_login_state');

        return add_query_arg([
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ], 'https://accounts.google.com/o/oauth2/v2/auth');
    }

    private function build_google_auth_url()
    {
        $client_id = trim((string) $this->get_setting('google_client_id', ''));
        $redirect_uri = (string) $this->get_setting('google_redirect_uri', admin_url('admin-post.php?action=wps_google_callback'));
        if ($client_id === '' || $redirect_uri === '') {
            return false;
        }

        $state = wp_create_nonce('wps_google_login_state');

        return add_query_arg([
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ], 'https://accounts.google.com/o/oauth2/v2/auth');
    }

    private function exchange_google_code_for_token($code)
    {
        $client_id = trim((string) $this->get_setting('google_client_id', ''));
        $client_secret = trim((string) $this->get_setting('google_client_secret', ''));
        $redirect_uri = (string) $this->get_setting('google_redirect_uri', admin_url('admin-post.php?action=wps_google_callback'));

        if ($client_id === '' || $client_secret === '' || $redirect_uri === '') {
            return false;
        }

        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'timeout' => 15,
            'body' => [
                'code' => $code,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $redirect_uri,
                'grant_type' => 'authorization_code',
            ],
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return $data['access_token'] ?? false;
    }

    private function fetch_google_userinfo($access_token)
    {
        $response = wp_remote_get('https://openidconnect.googleapis.com/v1/userinfo', [
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
            ],
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    private function get_google_access_token(array $credentials)
    {
        $now = time();
        $header = $this->base64url_encode(wp_json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/indexing',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ];
        $payload = $this->base64url_encode(wp_json_encode($claims));
        $signature_input = $header . '.' . $payload;

        $signature = '';
        $private_key = openssl_pkey_get_private($credentials['private_key']);
        if (!$private_key) {
            return false;
        }

        openssl_sign($signature_input, $signature, $private_key, OPENSSL_ALGO_SHA256);
        openssl_free_key($private_key);

        $jwt = $signature_input . '.' . $this->base64url_encode($signature);

        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'timeout' => 15,
            'body' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return $data['access_token'] ?? false;
    }

    private function normalize_redirect_target($to, $site_url)
    {
        if (preg_match('#^https?://#i', $to)) {
            return esc_url_raw($to);
        }

        return esc_url_raw(rtrim($site_url, '/') . '/' . ltrim($to, '/'));
    }

    private function verify_recaptcha($token)
    {
        $secret = trim((string) $this->get_setting('recaptcha_secret_key', ''));
        if ($secret === '') {
            return false;
        }

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'timeout' => 10,
            'body' => [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ],
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return !empty($data['success']);
    }

    private function ensure_unique_username($username)
    {
        $base = sanitize_user($username, true);
        $candidate = $base;
        $index = 1;
        while (username_exists($candidate)) {
            $candidate = $base . $index;
            $index++;
        }

        return $candidate;
    }

    private function base64url_encode($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function get_setting($key, $default = false)
    {
        $settings = get_option('wps_main_settings', []);

        return $settings[$key] ?? $default;
    }
}

// Copyright by AcmaTvirus
