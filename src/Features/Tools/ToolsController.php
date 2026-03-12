<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Features\Tools;

use Acma\WpSecurity\Core\BaseController;

/**
 * Quản lý các công cụ Post-Hack và Tiện ích
 */
class ToolsController extends BaseController
{
    public function init()
    {
        // Hooks
    }

    /**
     * Xử lý các hành động công cụ
     */
    public function handle_actions()
    {
        if (!isset($_POST['wps_tool_action']) || !current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('wps_tool_nonce_action', 'wps_tool_nonce');
        $action = $_POST['wps_tool_action'];
        $message = '';

        if ($action === 'kill_sessions') {
            $sessions = \WP_Session_Tokens::get_instance(get_current_user_id());
            $sessions->destroy_all();
            $message = 'Tất cả phiên làm việc đã được đăng xuất.';
        } elseif ($action === 'force_pw_reset') {
            global $wpdb;
            $wpdb->query("UPDATE $wpdb->users SET user_pass = 'RE-SET-ME' WHERE 1=1;");
            $message = 'Đã yêu cầu tất cả người dùng đổi mật khẩu.';
        } elseif ($action === 'clear_logs') {
            update_option('wps_audit_logs', []);
            update_option('wps_security_logs', []);
            $message = 'Tất cả nhật ký đã được dọn dẹp.';
        }

        if ($message) {
            echo '<div class="updated"><p>' . esc_html($message) . '</p></div>';
        }
    }

    /**
     * Render tab Công cụ
     */
    public function render_tab()
    {
        $this->render('Tools/Views/ToolsTab');
    }
}
