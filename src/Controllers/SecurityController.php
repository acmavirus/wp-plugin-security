<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Controllers;

use Acma\WpSecurity\Services\SecurityService;

/**
 * Controller quản lý các hook bảo mật
 */
class SecurityController
{
    /**
     * @var SecurityService
     */
    private $security_service;

    public function __construct()
    {
        $this->security_service = new SecurityService();
        $this->init_hooks();
    }

    /**
     * Đăng ký các WordPress hooks
     */
    private function init_hooks()
    {
        add_action('init', [$this, 'check_security']);
    }

    /**
     * Logic kiểm tra bảo mật mỗi khi trang web load
     */
    public function check_security()
    {
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

        if ($this->security_service->is_ip_blocked($user_ip)) {
            wp_die('Truy cập bị chặn bởi WP Plugin Security!');
        }
    }
}

// Copyright by AcmaTvirus
