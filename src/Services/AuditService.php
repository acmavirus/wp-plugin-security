<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Services;

/**
 * Service ghi lại mọi hoạt động trên website (Audit Trail)
 */
class AuditService
{
    /**
     * Ghi lại một sự kiện hoạt động
     * 
     * @param string $action Hành động (login, logout, post_update, plugin_activate...)
     * @param string $message Thông báo chi tiết
     * @param int|null $user_id ID người dùng thực hiện
     */
    public function log($action, $message, $user_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user_info = get_userdata($user_id);
        $username = $user_info ? $user_info->user_login : 'Guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        $logs = get_option('wps_audit_logs', []);
        $new_log = [
            'time'    => current_time('mysql'),
            'user'    => $username,
            'action'  => $action,
            'message' => $message,
            'ip'      => $ip
        ];

        array_unshift($logs, $new_log);
        $logs = array_slice($logs, 0, 500); // Lưu 500 log hoạt động
        update_option('wps_audit_logs', $logs);
    }

    /**
     * Lấy danh sách log
     */
    public function get_logs($limit = 100)
    {
        return array_slice(get_option('wps_audit_logs', []), 0, $limit);
    }
}

// Copyright by AcmaTvirus
