<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Features\Audit;

use Acma\WpSecurity\Core\BaseController;
use Acma\WpSecurity\Services\AuditService;

/**
 * Quản lý tính năng Audit Trail
 */
class AuditController extends BaseController
{
    private $audit_service;

    public function __construct()
    {
        $this->audit_service = new AuditService();
    }

    public function init()
    {
        $main_settings = get_option('wps_main_settings', []);
        if (!($main_settings['enable_audit_log'] ?? true)) return;

        add_action('wp_login', function ($user_login, $user) {
            $this->audit_service->log('login', "Người dùng $user_login đã đăng nhập", $user->ID);
        }, 10, 2);

        add_action('wp_logout', function () {
            $user_id = get_current_user_id();
            $this->audit_service->log('logout', "Người dùng đã đăng xuất", $user_id);
        });

        add_action('switch_theme', function ($new_name) {
            $this->audit_service->log('theme_change', "Đổi giao diện sang: $new_name");
        });

        add_action('activated_plugin', function ($plugin) {
            $this->audit_service->log('plugin_activate', "Kích hoạt plugin: $plugin");
        });

        add_action('deactivated_plugin', function ($plugin) {
            $this->audit_service->log('plugin_deactivate', "Hủy kích hoạt plugin: $plugin");
        });

        add_action('save_post', function ($post_id, $post, $update) {
            if ($update) {
                if (get_post_type($post_id) !== 'revision') {
                    $this->audit_service->log('post_update', "Cập nhật bài viết: " . get_the_title($post_id));
                }
            }
        }, 10, 3);
    }

    /**
     * Render trang Audit Trail
     */
    public function render_tab()
    {
        $audit_logs = get_option('wps_audit_logs', []);
        $this->render('Audit/Views/AuditTab', [
            'audit_logs' => $audit_logs
        ]);
    }
}
