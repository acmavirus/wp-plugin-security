<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Services;

/**
 * Service xử lý các logic bảo mật
 */
class SecurityService
{
    /**
     * Kiểm tra xem IP có nằm trong danh sách đen không
     * 
     * @param string $ip
     * @return bool
     */
    public function is_ip_blocked($ip)
    {
        // Logic kiểm tra IP (giả lập)
        $blocked_ips = ['127.0.0.1'];
        return in_array($ip, $blocked_ips);
    }
}

// Copyright by AcmaTvirus
