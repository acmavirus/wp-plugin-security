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
        // Kiểm tra truy cập sớm nhất có thể
        add_action('init', [$this, 'handle_security_checks'], 1);

        // Thêm các header bảo mật
        add_filter('wp_headers', [$this, 'add_security_headers']);

        // Vô hiệu hóa XML-RPC (thường dùng để brute force)
        add_filter('xmlrpc_enabled', '__return_false');

        // Ẩn phiên bản WordPress để tránh lộ thông tin lỗ hổng
        add_filter('the_generator', '__return_empty_string');

        // Chặn truy cập trực tiếp vào các file hệ thống quan trọng qua .htaccess logic (WP hook)
        add_action('admin_init', [$this, 'protect_admin_area']);
    }

    /**
     * Thực hiện các kiểm tra bảo mật
     */
    public function handle_security_checks()
    {
        // 1. Kiểm tra IP
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($this->security_service->is_ip_blocked($user_ip)) {
            wp_die('Truy cập bị chặn bởi WP Plugin Security!', 'Access Denied', ['response' => 403]);
        }

        // 2. Lọc các request nguy hiểm (SQLi, XSS)
        if ($this->security_service->is_dangerous_request()) {
            wp_die('Phát hiện hành vi nguy hiểm!', 'Security Warning', ['response' => 400]);
        }
    }

    /**
     * Thêm các headers bảo mật vào response
     */
    public function add_security_headers($headers)
    {
        $security_headers = $this->security_service->get_security_headers();
        return array_merge($headers, $security_headers);
    }

    /**
     * Bảo vệ khu vực admin
     */
    public function protect_admin_area()
    {
        if (!current_user_can('manage_options') && !defined('DOING_AJAX')) {
            // Có thể thêm logic redirect hoặc thông báo ở đây
        }
    }
}

// Copyright by AcmaTvirus
