<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Controllers;

/**
 * Controller xử lý các thiết lập trong trang quản trị
 */
class AdminController
{

    public function __construct()
    {
        // Đăng ký menu
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Đăng ký settings
        add_action('admin_init', [$this, 'register_settings']);

        // Đăng ký action links trực tiếp trong constructor (vì plugin đã load)
        $plugin_base = plugin_basename(WPS_PLUGIN_FILE);
        add_filter("plugin_action_links_{$plugin_base}", [$this, 'add_plugin_action_links']);
    }

    /**
     * Thêm liên kết Settings và Check Update vào danh sách plugin
     */
    public function add_plugin_action_links($links)
    {
        $settings_url = admin_url('admin.php?page=wp-plugin-security');
        $update_url = wp_nonce_url(admin_url('update-core.php?force-check=1'), 'upgrade-core');

        $custom_links = [
            '<a href="' . esc_url($settings_url) . '">' . __('Settings', 'wp-plugin-security') . '</a>',
            '<a href="' . esc_url($update_url) . '" style="color: #d63638; font-weight: bold;">' . __('Check Update', 'wp-plugin-security') . '</a>'
        ];

        return array_merge($custom_links, (array)$links);
    }

    /**
     * Tạo menu trong admin
     */
    public function add_admin_menu()
    {
        add_menu_page(
            'WP Security',
            'WP Security',
            'manage_options',
            'wp-plugin-security',
            [$this, 'render_admin_page'],
            'dashicons-shield-alt',
            80
        );
    }

    /**
     * Đăng ký settings
     */
    public function register_settings()
    {
        register_setting('wps_settings_group', 'wps_blocked_ips');
        register_setting('wps_settings_group', 'wps_main_settings');
    }

    /**
     * Render trang cấu hình
     */
    public function render_admin_page()
    {
        $current_tab = $_GET['tab'] ?? 'general';

        // Xử lý các hành động Tools (Post-Hack)
        if (isset($_POST['wps_tool_action']) && current_user_can('manage_options')) {
            check_admin_referer('wps_tool_nonce_action', 'wps_tool_nonce');
            $action = $_POST['wps_tool_action'];

            if ($action === 'kill_sessions') {
                $sessions = \WP_Session_Tokens::get_instance(get_current_user_id());
                $sessions->destroy_all();
                echo '<div class="updated"><p>Tất cả phiên làm việc đã được đăng xuất (bao gồm cả bạn).</p></div>';
            } elseif ($action === 'force_pw_reset') {
                global $wpdb;
                $wpdb->query("UPDATE $wpdb->users SET user_pass = 'RE-SET-ME' WHERE 1=1;");
                echo '<div class="updated"><p>Đã yêu cầu tất cả người dùng đổi mật khẩu (Mật khẩu cũ sẽ bị vô hiệu hóa).</p></div>';
            }
        }

        // Xử lý lưu thiết lập
        if (isset($_POST['wps_save_settings']) && current_user_can('manage_options')) {
            check_admin_referer('wps_settings_action', 'wps_settings_nonce');

            $main_settings = get_option('wps_main_settings', []);

            if ($current_tab === 'general') {
                $main_settings = array_merge($main_settings, [
                    'disable_xmlrpc'          => isset($_POST['disable_xmlrpc']),
                    'disable_rest_api'        => isset($_POST['disable_rest_api']),
                    'block_author_scan'       => isset($_POST['block_author_scan']),
                    'disable_file_editor'     => isset($_POST['disable_file_editor']),
                    'disable_directory_browsing' => isset($_POST['disable_directory_browsing']),
                    'hide_wp_version'         => isset($_POST['hide_wp_version']),
                    'enable_security_headers' => isset($_POST['enable_security_headers']),
                    'enable_audit_log'        => isset($_POST['enable_audit_log']),
                ]);
            } elseif ($current_tab === 'login') {
                $main_settings = array_merge($main_settings, [
                    'limit_login_attempts'    => isset($_POST['limit_login_attempts']),
                    'max_login_attempts'      => (int)($_POST['max_login_attempts'] ?? 5),
                    'lockout_duration'        => (int)($_POST['lockout_duration'] ?? 60),
                    'rename_login_slug'       => sanitize_title($_POST['rename_login_slug'] ?? ''),
                    'idle_logout_time'        => (int)($_POST['idle_logout_time'] ?? 0),
                    'enforce_strong_password' => isset($_POST['enforce_strong_password']),
                    'mask_login_errors'       => isset($_POST['mask_login_errors']),
                ]);
            }

            update_option('wps_main_settings', $main_settings);

            if ($current_tab === 'blacklist') {
                $raw_ips = explode("\n", str_replace("\r", "", $_POST['wps_blocked_ips_raw'] ?? ''));
                $clean_ips = array_unique(array_filter(array_map('trim', $raw_ips)));
                update_option('wps_blocked_ips', $clean_ips);
            }

            echo '<div class="updated"><p>Cấu hình đã được lưu thành công.</p></div>';
        }

        $main_settings = get_option('wps_main_settings', [
            'limit_login_attempts'    => true,
            'max_login_attempts'      => 5,
            'lockout_duration'        => 60,
            'disable_xmlrpc'          => true,
            'disable_rest_api'        => true,
            'block_author_scan'       => true,
            'mask_login_errors'       => true,
            'hide_wp_version'         => true,
            'disable_file_editor'     => true,
            'enable_security_headers' => true,
            'enable_audit_log'        => true,
            'enforce_strong_password' => true,
        ]);

        $blocked_ips = get_option('wps_blocked_ips', []);
        $ips_text = is_array($blocked_ips) ? implode("\n", $blocked_ips) : '';
        $audit_logs = get_option('wps_audit_logs', []);
        $security_logs = get_option('wps_security_logs', []);
?>
        <div class="wrap wps-admin-wrap">
            <div class="wps-header">
                <div class="wps-brand">
                    <span class="dashicons dashicons-shield-alt"></span>
                    <div>
                        <h1>WP Plugin Security <span class="v-badge">v1.1.2</span></h1>
                        <p class="description">Giải pháp bảo vệ website tối thượng của bạn.</p>
                    </div>
                </div>
                <div class="wps-stats">
                    <div class="stat-item">
                        <span class="label">Sự kiện bảo mật</span>
                        <span class="value"><?php echo count($security_logs); ?></span>
                    </div>
                </div>
            </div>

            <h2 class="nav-tab-wrapper wps-tabs">
                <a href="?page=wp-plugin-security&tab=general" class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-settings"></span> Hệ thống & WAF
                </a>
                <a href="?page=wp-plugin-security&tab=login" class="nav-tab <?php echo $current_tab === 'login' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-lock"></span> Bảo mật Đăng nhập
                </a>
                <a href="?page=wp-plugin-security&tab=blacklist" class="nav-tab <?php echo $current_tab === 'blacklist' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-no-alt"></span> IP Blacklist
                </a>
                <a href="?page=wp-plugin-security&tab=audit" class="nav-tab <?php echo $current_tab === 'audit' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-list-view"></span> Audit Trail
                </a>
                <a href="?page=wp-plugin-security&tab=tools" class="nav-tab <?php echo $current_tab === 'tools' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-hammer"></span> Công cụ
                </a>
            </h2>

            <div class="wps-content-box">
                <?php if ($current_tab === 'general') : ?>
                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
                        <div class="wps-settings-grid">
                            <div class="settings-section">
                                <h3><span class="dashicons dashicons-shield"></span> Tường lửa & Hardening</h3>

                                <div class="setting-row">
                                    <div class="setting-info">
                                        <label>Vô hiệu hóa XML-RPC</label>
                                        <span class="help">Ngăn chặn tấn công brute-force qua cổng XML-RPC.</span>
                                    </div>
                                    <label class="wps-switch">
                                        <input type="checkbox" name="disable_xmlrpc" <?php checked($main_settings['disable_xmlrpc'] ?? false); ?>>
                                        <span class="wps-slider"></span>
                                    </label>
                                </div>

                                <div class="setting-row">
                                    <div class="setting-info">
                                        <label>Hạn chế REST API</label>
                                        <span class="help">Chỉ cho phép người dùng đã đăng nhập truy cập API.</span>
                                    </div>
                                    <label class="wps-switch">
                                        <input type="checkbox" name="disable_rest_api" <?php checked($main_settings['disable_rest_api'] ?? false); ?>>
                                        <span class="wps-slider"></span>
                                    </label>
                                </div>

                                <div class="setting-row">
                                    <div class="setting-info">
                                        <label>Chặn Author Scan</label>
                                        <span class="help">Ngăn bot dò tìm username quản trị viên.</span>
                                    </div>
                                    <label class="wps-switch">
                                        <input type="checkbox" name="block_author_scan" <?php checked($main_settings['block_author_scan'] ?? false); ?>>
                                        <span class="wps-slider"></span>
                                    </label>
                                </div>

                                <div class="setting-row">
                                    <div class="setting-info">
                                        <label>Chặn Directory Browsing</label>
                                        <span class="help">Ngăn người lạ duyệt file trong thư mục.</span>
                                    </div>
                                    <label class="wps-switch">
                                        <input type="checkbox" name="disable_directory_browsing" <?php checked($main_settings['disable_directory_browsing'] ?? false); ?>>
                                        <span class="wps-slider"></span>
                                    </label>
                                </div>

                                <div class="setting-row">
                                    <div class="setting-info">
                                        <label>Tắt trình chỉnh sửa file</label>
                                        <span class="help">Vô hiệu hóa chỉnh sửa Code trong Admin.</span>
                                    </div>
                                    <label class="wps-switch">
                                        <input type="checkbox" name="disable_file_editor" <?php checked($main_settings['disable_file_editor'] ?? false); ?>>
                                        <span class="wps-slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="settings-section">
                                <h3><span class="dashicons dashicons-visibility"></span> Quyền riêng tư & Nhật ký</h3>

                                <div class="setting-row">
                                    <div class="setting-info">
                                        <label>Ẩn phiên bản WP</label>
                                        <span class="help">Xóa bỏ dấu hiệu nhận biết phiên bản từ mã nguồn.</span>
                                    </div>
                                    <label class="wps-switch">
                                        <input type="checkbox" name="hide_wp_version" <?php checked($main_settings['hide_wp_version'] ?? false); ?>>
                                        <span class="wps-slider"></span>
                                    </label>
                                </div>

                                <div class="setting-row">
                                    <div class="setting-info">
                                        <label>Security Headers</label>
                                        <span class="help">Kích hoạt HSTS, XSS Protection, nosniff...</span>
                                    </div>
                                    <label class="wps-switch">
                                        <input type="checkbox" name="enable_security_headers" <?php checked($main_settings['enable_security_headers'] ?? false); ?>>
                                        <span class="wps-slider"></span>
                                    </label>
                                </div>

                                <div class="setting-row">
                                    <div class="setting-info">
                                        <label>Audit Trail</label>
                                        <span class="help">Lưu lại mọi hoạt động của người dùng.</span>
                                    </div>
                                    <label class="wps-switch">
                                        <input type="checkbox" name="enable_audit_log" <?php checked($main_settings['enable_audit_log'] ?? false); ?>>
                                        <span class="wps-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="wps_save_settings" value="1">
                        <div class="submit-wrap">
                            <?php submit_button('Lưu thiết lập Hệ thống', 'primary button-hero'); ?>
                        </div>
                    </form>

                <?php elseif ($current_tab === 'login') : ?>
                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
                        <div class="wps-settings-grid">
                            <div class="settings-section">
                                <h3><span class="dashicons dashicons-admin-links"></span> Rename Login</h3>
                                <div class="input-row">
                                    <label>Đường dẫn đăng nhập mới</label>
                                    <div class="slug-input-group">
                                        <span class="prefix"><?php echo home_url('/'); ?></span>
                                        <input type="text" name="rename_login_slug" value="<?php echo esc_attr($main_settings['rename_login_slug'] ?? ''); ?>" placeholder="vi-du: secret-login" class="regular-text">
                                    </div>
                                    <p class="description">Nếu để trống, plugin sẽ dùng `wp-login.php` mặc định.</p>
                                </div>
                            </div>

                            <div class="settings-section">
                                <h3><span class="dashicons dashicons-shield-alt"></span> Brute Force Protection</h3>
                                <div class="setting-row">
                                    <div class="setting-info">
                                        <label>Giới hạn đăng nhập</label>
                                        <span class="help">Khóa IP nếu đăng nhập sai nhiều lần.</span>
                                    </div>
                                    <label class="wps-switch">
                                        <input type="checkbox" name="limit_login_attempts" <?php checked($main_settings['limit_login_attempts'] ?? false); ?>>
                                        <span class="wps-slider"></span>
                                    </label>
                                </div>
                                <div class="setting-row">
                                    <div class="setting-info">
                                        <label>Ẩn lỗi đăng nhập</label>
                                        <span class="help">Không cho biết username hay password sai.</span>
                                    </div>
                                    <label class="wps-switch">
                                        <input type="checkbox" name="mask_login_errors" <?php checked($main_settings['mask_login_errors'] ?? false); ?>>
                                        <span class="wps-slider"></span>
                                    </label>
                                </div>
                                <div class="input-grid">
                                    <div class="input-item">
                                        <label>Thử tối đa</label>
                                        <input type="number" name="max_login_attempts" value="<?php echo esc_attr($main_settings['max_login_attempts'] ?? 5); ?>">
                                    </div>
                                    <div class="input-item">
                                        <label>Thời gian khóa (phút)</label>
                                        <input type="number" name="lockout_duration" value="<?php echo esc_attr($main_settings['lockout_duration'] ?? 60); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="settings-section">
                                <h3><span class="dashicons dashicons-admin-users"></span> Chính sách người dùng</h3>
                                <div class="setting-row">
                                    <div class="setting-info">
                                        <label>Mật khẩu mạnh</label>
                                        <span class="help">Bắt buộc sử dụng 12 ký tự + ký tự đặc biệt.</span>
                                    </div>
                                    <label class="wps-switch">
                                        <input type="checkbox" name="enforce_strong_password" <?php checked($main_settings['enforce_strong_password'] ?? false); ?>>
                                        <span class="wps-slider"></span>
                                    </label>
                                </div>
                                <div class="input-row">
                                    <label>Tự động đăng xuất khi nhàn rỗi (phút)</label>
                                    <input type="number" name="idle_logout_time" value="<?php echo esc_attr($main_settings['idle_logout_time'] ?? 0); ?>" class="small-text">
                                    <span class="help">0 để tắt chức năng này.</span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="wps_save_settings" value="1">
                        <div class="submit-wrap">
                            <?php submit_button('Lưu thiết lập Đăng nhập', 'primary button-hero'); ?>
                        </div>
                    </form>

                <?php elseif ($current_tab === 'blacklist') : ?>
                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
                        <div class="blacklist-container">
                            <div class="text-area-wrap">
                                <h3>Quản lý IP bị chặn</h3>
                                <p class="description">Mỗi địa chỉ IP nằm trên một dòng riêng biệt.</p>
                                <textarea name="wps_blocked_ips_raw" rows="12" class="large-text code"><?php echo esc_textarea($ips_text); ?></textarea>
                            </div>
                            <div class="blocked-history">
                                <h3>Nhật ký chặn tự động</h3>
                                <div class="history-list">
                                    <?php
                                    $auto_blocked = array_filter($security_logs, fn($l) => in_array($l['type'], ['ip_blocked', 'dangerous_request']));
                                    if (empty($auto_blocked)) : echo '<p>Chưa có IP bị chặn tự động.</p>';
                                    else : foreach (array_slice($auto_blocked, 0, 10) as $log) : ?>
                                            <div class="history-item">
                                                <span class="h-time"><?php echo date('H:i d/m', strtotime($log['time'])); ?></span>
                                                <span class="h-ip"><code><?php echo $log['ip']; ?></code></span>
                                                <span class="h-msg"><?php echo $log['message']; ?></span>
                                            </div>
                                    <?php endforeach;
                                    endif; ?>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="wps_save_settings" value="1">
                        <?php submit_button('Cập nhật danh sách đen'); ?>
                    </form>

                <?php elseif ($current_tab === 'audit') : ?>
                    <div class="audit-wrapper">
                        <table class="wp-list-table widefat fixed striped wps-table">
                            <thead>
                                <tr>
                                    <th width="120">Thời gian</th>
                                    <th width="150">Người dùng</th>
                                    <th width="120">Hành động</th>
                                    <th>Thông tin chi tiết</th>
                                    <th width="120">Địa chỉ IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($audit_logs)) : ?>
                                    <tr>
                                        <td colspan="5">Chưa có hoạt động nào được ghi lại.</td>
                                    </tr>
                                    <?php else : foreach ($audit_logs as $log) : ?>
                                        <tr>
                                            <td><small><?php echo date('H:i:s d/m', strtotime($log['time'] ?? 'now')); ?></small></td>
                                            <td><strong><?php echo esc_html($log['user'] ?? 'Guest'); ?></strong></td>
                                            <td><span class="act-badge b-<?php echo strtolower($log['action'] ?? 'info'); ?>"><?php echo esc_html($log['action'] ?? 'INFO'); ?></span></td>
                                            <td><?php echo esc_html($log['message'] ?? ''); ?></td>
                                            <td><code><?php echo esc_html($log['ip'] ?? '0.0.0.0'); ?></code></td>
                                        </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($current_tab === 'tools') : ?>
                    <div class="wps-tools-grid">
                        <div class="tool-card danger">
                            <span class="dashicons dashicons-warning"></span>
                            <h3>Ngắt toàn bộ phiên làm việc</h3>
                            <p>Đăng xuất tất cả người dùng ngay lập tức (bao gồm cả bạn). Sử dụng nếu nghi ngờ có người lạ xâm nhập.</p>
                            <form method="post" action="" onsubmit="return confirm('Bạn sẽ bị đăng xuất ngay lập tức. Tiếp tục?');">
                                <?php wp_nonce_field('wps_tool_nonce_action', 'wps_tool_nonce'); ?>
                                <input type="hidden" name="wps_tool_action" value="kill_sessions">
                                <button type="submit" class="button button-link-delete">Kích hoạt Logout All</button>
                            </form>
                        </div>

                        <div class="tool-card critical">
                            <span class="dashicons dashicons-update"></span>
                            <h3>Reset mật khẩu toàn website</h3>
                            <p>Vô hiệu hóa toàn bộ mật khẩu hiện tại. Người dùng sẽ phải thực hiện "Quên mật khẩu" để truy cập lại.</p>
                            <form method="post" action="" onsubmit="return confirm('HƯ HẠI NẶNG: Toàn bộ mật khẩu sẽ bị xóa. Tiếp tục?');">
                                <?php wp_nonce_field('wps_tool_nonce_action', 'wps_tool_nonce'); ?>
                                <input type="hidden" name="wps_tool_action" value="force_pw_reset">
                                <button type="submit" class="button button-link-delete">Kích hoạt Reset Pass Toàn diện</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <style>
            :root {
                --wps-primary: #6366f1;
                --wps-success: #10b981;
                --wps-danger: #ef4444;
                --wps-border: #e2e8f0;
                --wps-bg: #ffffff;
            }

            .wps-admin-wrap {
                margin: 20px 20px 0 0;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }

            /* Header Style */
            .wps-header {
                background: #fff;
                padding: 30px;
                border-radius: 12px;
                margin-bottom: 25px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            .wps-brand {
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .wps-brand .dashicons {
                font-size: 40px;
                width: 40px;
                height: 40px;
                color: var(--wps-primary);
            }

            .wps-brand h1 {
                margin: 0;
                font-size: 24px;
                font-weight: 700;
                color: #1e293b;
            }

            .v-badge {
                font-size: 12px;
                background: #f1f5f9;
                padding: 2px 8px;
                border-radius: 4px;
                color: #64748b;
            }

            /* Tabs Custom */
            .wps-tabs {
                border: none !important;
                margin-bottom: 0 !important;
                gap: 5px;
                background: transparent !important;
            }

            .wps-tabs .nav-tab {
                border: none !important;
                background: #e2e8f0 !important;
                color: #64748b !important;
                border-radius: 8px 8px 0 0 !important;
                padding: 12px 20px !important;
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                transition: all 0.2s;
                margin: 0 !important;
            }

            .wps-tabs .nav-tab-active {
                background: #fff !important;
                color: var(--wps-primary) !important;
                font-weight: 600 !important;
            }

            .wps-tabs .nav-tab:hover {
                background: #cbd5e1 !important;
            }

            /* Content Box */
            .wps-content-box {
                background: #fff;
                min-height: 400px;
                padding: 40px;
                border-radius: 0 12px 12px 12px;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            }

            /* Switch Style */
            .wps-switch {
                position: relative;
                display: inline-block;
                width: 44px;
                height: 24px;
            }

            .wps-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .wps-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #cbd5e1;
                transition: .3s;
                border-radius: 34px;
            }

            .wps-slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .3s;
                border-radius: 50%;
            }

            input:checked+.wps-slider {
                background-color: var(--wps-success);
            }

            input:checked+.wps-slider:before {
                transform: translateX(20px);
            }

            /* Grid & Rows */
            .wps-settings-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 40px;
            }

            .settings-section h3 {
                border-bottom: 1px solid #f1f5f9;
                padding-bottom: 15px;
                margin-bottom: 25px;
                color: #1e293b;
                font-size: 18px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .setting-row {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 20px;
                padding: 10px 0;
            }

            .setting-info label {
                display: block;
                font-weight: 600;
                color: #334155;
                margin-bottom: 4px;
            }

            .setting-info .help {
                font-size: 12px;
                color: #94a3b8;
            }

            /* Inputs */
            .input-grid {
                display: flex;
                gap: 20px;
                margin-top: 20px;
                background: #f8fafc;
                padding: 15px;
                border-radius: 8px;
            }

            .input-item label {
                display: block;
                font-size: 11px;
                color: #64748b;
                text-transform: uppercase;
                margin-bottom: 5px;
            }

            .input-item input {
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                padding: 5px 10px;
                width: 80px;
            }

            .slug-input-group {
                display: flex;
                align-items: center;
                border: 1px solid #cbd5e1;
                border-radius: 6px;
                overflow: hidden;
            }

            .slug-input-group .prefix {
                background: #f1f5f9;
                padding: 10px;
                border-right: 1px solid #cbd5e1;
                color: #64748b;
                font-size: 13px;
            }

            .slug-input-group input {
                border: none !important;
                box-shadow: none !important;
                padding: 10px;
                flex: 1;
            }

            /* Blacklist */
            .blacklist-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
            }

            .history-list {
                background: #f8fafc;
                border-radius: 8px;
                padding: 15px;
                max-height: 300px;
                overflow-y: auto;
            }

            .history-item {
                display: flex;
                gap: 10px;
                padding: 8px 0;
                border-bottom: 1px solid #e2e8f0;
                font-size: 12px;
            }

            .h-time {
                color: #94a3b8;
                width: 80px;
            }

            .h-ip {
                color: var(--wps-primary);
                font-weight: 600;
            }

            /* Table */
            .wps-table {
                border: none !important;
            }

            .wps-table thead th {
                background: #f8fafc !important;
                color: #64748b !important;
                font-weight: 600 !important;
                text-transform: uppercase;
                font-size: 11px;
                padding: 15px !important;
            }

            .act-badge {
                background: #f1f5f9;
                padding: 4px 8px;
                border-radius: 4px;
                font-weight: 700;
                font-size: 10px;
                color: #64748b;
            }

            .b-login {
                background: #dcfce7;
                color: #166534;
            }

            .b-post_update {
                background: #dbeafe;
                color: #1e40af;
            }

            .b-theme_change {
                background: #fef9c3;
                color: #854d0e;
            }

            /* Tools */
            .wps-tools-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }

            .tool-card {
                border: 1px solid #fee2e2;
                padding: 30px;
                border-radius: 12px;
                text-align: center;
                transition: 0.3s;
            }

            .tool-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            }

            .tool-card.danger {
                border-color: var(--wps-danger);
            }

            .tool-card.danger h3 {
                color: var(--wps-danger);
            }

            .tool-card span {
                font-size: 48px;
                color: var(--wps-danger);
                height: auto;
                width: auto;
            }

            .tool-card.critical {
                border-color: #ffedd5;
            }

            .tool-card.critical span {
                color: #f59e0b;
            }

            .tool-card h3 {
                margin: 20px 0 10px;
            }
        </style>
<?php
    }
}

// Copyright by AcmaTvirus
