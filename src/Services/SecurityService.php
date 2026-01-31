<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Services;

/**
 * Service xử lý các logic bảo mật nang cao
 */
class SecurityService
{
    /**
     * Danh sách các bot tìm kiếm phổ biến để cho phép truy cập
     */
    private $allowed_bots = [
        'Googlebot',
        'Bingbot',
        'Slurp',
        'DuckDuckBot',
        'Baiduspider',
        'YandexBot',
        'facebookexternalhit',
        'twitterbot',
        'rogerbot',
        'linkedinbot',
        'embedly',
        'quora link preview',
        'showyoubot',
        'outbrain',
        'pinterest',
        'slackbot',
        'vkShare',
        'W3C_Validator',
    ];

    /**
     * Kiểm tra xem User Agent có phải là bot tìm kiếm không
     * 
     * @return bool
     */
    public function is_search_bot()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (empty($user_agent)) return false;

        foreach ($this->allowed_bots as $bot) {
            if (stripos($user_agent, $bot) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Kiểm tra xem IP có nằm trong danh sách đen không
     */
    public function is_ip_blocked($ip)
    {
        if ($this->is_search_bot()) {
            return false;
        }

        $blocked_ips = get_option('wps_blocked_ips', []);
        return in_array($ip, $blocked_ips);
    }

    /**
     * Lấy cấu hình bảo mật
     */
    public function get_setting($key, $default = false)
    {
        $settings = get_option('wps_main_settings', []);
        return $settings[$key] ?? $default;
    }

    /**
     * Bảo mật Header HTTP
     */
    public function get_security_headers()
    {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        ];
    }

    /**
     * Kiểm tra xem request có phải là trang login ẩn không
     */
    public function is_hidden_login_page()
    {
        $slug = $this->get_setting('rename_login_slug', '');
        if (empty($slug)) return false;

        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        return (strpos($request_uri, $slug) !== false);
    }

    /**
     * Kiểm tra thời gian nhàn rỗi (Idle Timeout)
     */
    public function check_idle_timeout()
    {
        if (!is_user_logged_in()) return;

        $timeout = (int)$this->get_setting('idle_logout_time', 0);
        if ($timeout <= 0) return;

        $last_action = get_user_meta(get_current_user_id(), 'wps_last_action', true);
        $current_time = time();

        if ($last_action && ($current_time - $last_action) > ($timeout * 60)) {
            wp_logout();
            wp_redirect(home_url('?wps_event=idle_logout'));
            exit;
        }

        update_user_meta(get_current_user_id(), 'wps_last_action', $current_time);
    }

    /**
     * Chặn các request nguy hiểm (WAF Nâng cao)
     */
    public function is_dangerous_request()
    {
        if ($this->is_search_bot()) return false;

        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $query_string = $_SERVER['QUERY_STRING'] ?? '';
        $post_data = file_get_contents('php://input');
        $data_to_check = $request_uri . ' ' . $query_string . ' ' . $post_data;

        $dangerous_patterns = [
            'union select',
            'concat(',
            '<script>',
            'alert(',
            'eval(',
            'base64_',
            '/etc/passwd',
            '../',
            'proc/self/environ',
            'wp-config.php',
            '.htaccess',
            'global $wpdb',
            'order by',
            'group_concat',
            'sysadmin',
            'xp_cmdshell',
            'javascript:',
            'onerror=',
            'onload=',
            'prompt(',
            'confirm('
        ];

        foreach ($dangerous_patterns as $pattern) {
            if (stripos($data_to_check, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Ghi lại nhật ký bảo mật
     */
    public function log_event($type, $message, $ip = null)
    {
        if (!$ip) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        }

        $logs = get_option('wps_security_logs', []);
        $new_log = [
            'time' => current_time('mysql'),
            'type' => $type,
            'ip' => $ip,
            'message' => $message,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        array_unshift($logs, $new_log);
        $logs = array_slice($logs, 0, 100); // Giữ lại 100 log gần nhất
        update_option('wps_security_logs', $logs);
    }

    /**
     * Kiểm tra và giới hạn đăng nhập
     */
    public function check_login_attempts($ip)
    {
        if (!$this->get_setting('limit_login_attempts', true)) {
            return true;
        }

        $attempts = get_transient('wps_login_attempts_' . md5($ip));
        $max_attempts = (int)$this->get_setting('max_login_attempts', 5);

        if ($attempts && $attempts >= $max_attempts) {
            return false;
        }

        return true;
    }

    /**
     * Tăng số lần thử đăng nhập thất bại
     */
    public function increment_login_attempts($ip)
    {
        $key = 'wps_login_attempts_' . md5($ip);
        $attempts = (int)get_transient($key);
        $attempts++;

        $lockout_time = (int)$this->get_setting('lockout_duration', 60) * MINUTE_IN_SECONDS;
        set_transient($key, $attempts, $lockout_time);

        $this->log_event('login_failed', "Đăng nhập thất bại lần $attempts từ IP $ip", $ip);
    }
}

// Copyright by AcmaTvirus
