<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Features\Auth;

use Acma\WpSecurity\Core\BaseController;
use Acma\WpSecurity\Services\SecurityService;

/**
 * Quản lý tính năng Bảo mật Đăng nhập
 */
class AuthController extends BaseController
{
    private $security_service;

    public function __construct()
    {
        $this->security_service = new SecurityService();
    }

    public function init()
    {
        // Rename Login Page
        add_action('init', [$this, 'handle_rename_login']);
        add_filter('site_url', [$this, 'fix_login_urls'], 10, 4);
        add_filter('network_site_url', [$this, 'fix_login_urls'], 10, 4);

        // Idle Logout
        add_action('init', [$this, 'handle_idle_logout']);

        // REST API
        if ($this->security_service->get_setting('disable_rest_api', true)) {
            add_filter('rest_authentication_errors', function ($result) {
                if (!empty($result)) return $result;
                if (!is_user_logged_in()) {
                    return new \WP_Error('rest_forbidden', 'REST API is restricted.', ['status' => 401]);
                }
                return $result;
            });
        }

        // Login Attempts
        add_action('wp_login_failed', [$this, 'handle_failed_login']);
        add_filter('authenticate', [$this, 'check_login_lockout'], 30, 3);

        // 2FA
        if ($this->security_service->get_setting('enable_2fa', false)) {
            add_action('wp_login', [$this, 'init_2fa_verification'], 10, 2);
            add_action('init', [$this, 'handle_2fa_submission']);
        }

        // reCAPTCHA
        $site_key = $this->security_service->get_setting('recaptcha_site_key', '');
        if (!empty($site_key)) {
            add_action('login_enqueue_scripts', [$this, 'enqueue_recaptcha']);
            add_action('login_form', [$this, 'add_recaptcha_field']);
            add_filter('wp_authenticate_user', [$this, 'verify_recaptcha'], 10, 2);
        }

        // Strong Password
        if ($this->security_service->get_setting('enforce_strong_password', true)) {
            add_action('user_profile_update_errors', [$this, 'check_strong_password'], 10, 3);
            add_action('validate_password_reset', [$this, 'check_strong_password'], 10, 3);
        }

        // Mask Login Errors
        if ($this->security_service->get_setting('mask_login_errors', true)) {
            add_filter('login_errors', function () {
                return 'Sai thông tin đăng nhập. Vui lòng thử lại.';
            });
        }
    }

    public function handle_rename_login()
    {
        $slug = $this->security_service->get_setting('rename_login_slug', '');
        if (empty($slug)) return;

        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_login = (strpos($request_uri, 'wp-login.php') !== false || strpos($request_uri, 'wp-admin') !== false);
        $is_custom = (strpos($request_uri, $slug) !== false);

        if ($is_login && !$is_custom && !is_user_logged_in() && !defined('DOING_AJAX')) {
            wp_safe_redirect(home_url());
            exit;
        }

        if ($is_custom && !is_user_logged_in()) {
            include ABSPATH . 'wp-login.php';
            exit;
        }
    }

    public function fix_login_urls($url, $path, $scheme, $blog_id)
    {
        $slug = $this->security_service->get_setting('rename_login_slug', '');
        if (empty($slug)) return $url;
        if (strpos($url, 'wp-login.php') !== false) {
            return str_replace('wp-login.php', $slug, $url);
        }
        return $url;
    }

    public function handle_idle_logout()
    {
        $this->security_service->check_idle_timeout();
    }

    public function handle_failed_login($username)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($this->security_service->is_ip_whitelisted($ip)) return;
        $this->security_service->increment_login_attempts($ip);
    }

    public function check_login_lockout($user, $username, $password)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($this->security_service->is_ip_whitelisted($ip)) return $user;
        if (!$this->security_service->check_login_attempts($ip)) {
            return new \WP_Error('locked_out', 'IP bị khóa do thử sai quá nhiều lần.');
        }
        return $user;
    }

    public function init_2fa_verification($user_login, $user)
    {
        set_transient('wps_pending_2fa_' . $user->ID, true, 15 * MINUTE_IN_SECONDS);
        $this->security_service->handle_2fa_email($user->ID);
        wp_logout();
        wp_redirect(home_url('?wps_2fa_verify=1&uid=' . $user->ID));
        exit;
    }

    public function handle_2fa_submission()
    {
        if (isset($_GET['wps_2fa_verify']) && isset($_POST['wps_2fa_code'])) {
            $user_id = (int)$_GET['uid'];
            $code = sanitize_text_field($_POST['wps_2fa_code']);
            if ($this->security_service->verify_2fa($user_id, $code)) {
                delete_transient('wps_pending_2fa_' . $user_id);
                wp_set_auth_cookie($user_id);
                wp_redirect(admin_url());
                exit;
            }
        }
    }

    public function enqueue_recaptcha()
    {
        $site_key = $this->security_service->get_setting('recaptcha_site_key', '');
        wp_enqueue_script('google-recaptcha', "https://www.google.com/recaptcha/api.js?render={$site_key}", [], null, true);
    }

    public function add_recaptcha_field()
    {
        $site_key = $this->security_service->get_setting('recaptcha_site_key', '');
        ?>
        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
        <script>
            grecaptcha.ready(function() {
                grecaptcha.execute('<?php echo $site_key; ?>', {action: 'login'}).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                });
            });
        </script>
        <?php
    }

    public function verify_recaptcha($user, $password)
    {
        if (isset($_POST['g-recaptcha-response'])) {
            $token = $_POST['g-recaptcha-response'];
            $secret = $this->security_service->get_setting('recaptcha_secret_key', '');
            $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
                'body' => ['secret' => $secret, 'response' => $token, 'remoteip' => $_SERVER['REMOTE_ADDR']]
            ]);
            $result = json_decode(wp_remote_retrieve_body($response), true);
            if (empty($result['success']) || $result['score'] < 0.5) {
                return new \WP_Error('recaptcha_failed', 'Phát hiện hành vi nghi ngờ (Bot).');
            }
        }
        return $user;
    }

    public function check_strong_password($errors, $update, $user)
    {
        $password = $_POST['pass1'] ?? '';
        if (empty($password)) return;
        if (strlen($password) < 12 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors->add('weak_password', 'Mật khẩu phải dài ít nhất 12 ký tự, bao gồm chữ hoa, số và ký tự đặc biệt.');
        }
    }

    /**
     * Render tab Bảo mật Đăng nhập
     */
    public function render_tab($main_settings)
    {
        $this->render('Auth/Views/LoginTab', [
            'main_settings' => $main_settings
        ]);
    }
}
