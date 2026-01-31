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
     * 
     * @param string $ip
     * @return bool
     */
    public function is_ip_blocked($ip)
    {
        // Nếu là bot SEO thì không chặn
        if ($this->is_search_bot()) {
            return false;
        }

        // Logic kiểm tra IP từ DB hoặc file cấu hình (ở đây là ví dụ)
        $blocked_ips = get_option('wps_blocked_ips', []);
        return in_array($ip, $blocked_ips);
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
     * Chặn các request nguy hiểm (SQL Injection, XSS cơ bản)
     */
    public function is_dangerous_request()
    {
        if ($this->is_search_bot()) return false;

        $query_string = $_SERVER['QUERY_STRING'] ?? '';
        $dangerous_patterns = [
            'union select',
            'concat(',
            '<script>',
            'alert(',
            'eval(',
            'base64_',
            '/etc/passwd',
            '../',
        ];

        foreach ($dangerous_patterns as $pattern) {
            if (stripos($query_string, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
}

// Copyright by AcmaTvirus
