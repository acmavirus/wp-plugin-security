<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Controllers;

/**
 * Controller xu ly cac thiet lap trong trang quan tri
 */
class AdminController
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        $plugin_base = plugin_basename(WPS_PLUGIN_FILE);
        add_filter("plugin_action_links_{$plugin_base}", [$this, 'add_plugin_action_links']);
    }

    /**
     * Them lien ket nhanh trong danh sach plugin.
     */
    public function add_plugin_action_links($links)
    {
        $settings_url = admin_url('admin.php?page=wp-plugin-security');
        $update_url = wp_nonce_url(admin_url('update-core.php?force-check=1'), 'upgrade-core');

        $custom_links = [
            '<a href="' . esc_url($settings_url) . '">' . __('Settings', 'wp-plugin-security') . '</a>',
            '<a href="' . esc_url($update_url) . '" style="color: #d63638; font-weight: bold;">' . __('Check Update', 'wp-plugin-security') . '</a>',
        ];

        return array_merge($custom_links, (array) $links);
    }

    /**
     * Tao menu trong admin.
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
     * Dang ky settings.
     */
    public function register_settings()
    {
        register_setting('wps_settings_group', 'wps_blocked_ips');
        register_setting('wps_settings_group', 'wps_main_settings');
    }

    /**
     * Render trang cau hinh.
     */
    public function render_admin_page()
    {
        $current_tab = sanitize_key($_GET['tab'] ?? 'general');
        $notices = [];

        if (isset($_POST['wps_tool_action']) && current_user_can('manage_options')) {
            check_admin_referer('wps_tool_nonce_action', 'wps_tool_nonce');
            $action = sanitize_key($_POST['wps_tool_action']);

            if ($action === 'kill_sessions') {
                $destroyed_sessions = $this->destroy_all_sessions();
                $notices[] = sprintf(
                    'Da dang xuat %d phien dang nhap tren toan website.',
                    $destroyed_sessions
                );
            } elseif ($action === 'force_pw_reset') {
                $reset_users = $this->force_password_reset_for_all_users();
                $notices[] = sprintf(
                    'Da vo hieu hoa mat khau va session hien tai cua %d tai khoan.',
                    $reset_users
                );
            }
        }

        if (isset($_POST['wps_save_settings']) && current_user_can('manage_options')) {
            check_admin_referer('wps_settings_action', 'wps_settings_nonce');

            $main_settings = get_option('wps_main_settings', []);

            if ($current_tab === 'general') {
                $main_settings = array_merge($main_settings, [
                    'disable_xmlrpc' => isset($_POST['disable_xmlrpc']),
                    'disable_rest_api' => isset($_POST['disable_rest_api']),
                    'block_author_scan' => isset($_POST['block_author_scan']),
                    'disable_file_editor' => isset($_POST['disable_file_editor']),
                    'disable_directory_browsing' => isset($_POST['disable_directory_browsing']),
                    'hide_wp_version' => isset($_POST['hide_wp_version']),
                    'enable_security_headers' => isset($_POST['enable_security_headers']),
                    'enable_audit_log' => isset($_POST['enable_audit_log']),
                ]);
            } elseif ($current_tab === 'login') {
                $main_settings = array_merge($main_settings, [
                    'limit_login_attempts' => isset($_POST['limit_login_attempts']),
                    'max_login_attempts' => (int) ($_POST['max_login_attempts'] ?? 5),
                    'lockout_duration' => (int) ($_POST['lockout_duration'] ?? 60),
                    'rename_login_slug' => sanitize_title($_POST['rename_login_slug'] ?? ''),
                    'idle_logout_time' => (int) ($_POST['idle_logout_time'] ?? 0),
                    'enforce_strong_password' => isset($_POST['enforce_strong_password']),
                    'mask_login_errors' => isset($_POST['mask_login_errors']),
                ]);
            }

            update_option('wps_main_settings', $main_settings);

            if ($current_tab === 'blacklist') {
                $raw_ips = explode("\n", str_replace("\r", '', $_POST['wps_blocked_ips_raw'] ?? ''));
                $clean_ips = array_unique(array_filter(array_map('trim', $raw_ips)));
                update_option('wps_blocked_ips', $clean_ips);
            }

            $notices[] = 'Cau hinh da duoc luu thanh cong.';
        }

        $main_settings = get_option('wps_main_settings', [
            'limit_login_attempts' => true,
            'max_login_attempts' => 5,
            'lockout_duration' => 60,
            'disable_xmlrpc' => true,
            'disable_rest_api' => true,
            'block_author_scan' => true,
            'mask_login_errors' => true,
            'hide_wp_version' => true,
            'disable_file_editor' => true,
            'enable_security_headers' => true,
            'enable_audit_log' => true,
            'enforce_strong_password' => true,
        ]);

        $blocked_ips = get_option('wps_blocked_ips', []);
        $ips_text = is_array($blocked_ips) ? implode("\n", $blocked_ips) : '';
        $audit_logs = get_option('wps_audit_logs', []);
        $security_logs = get_option('wps_security_logs', []);
        ?>
        <div class="wrap">
            <h1>
                <?php _e('WP Security Settings', 'wp-plugin-security'); ?>
                <span class="title-count" style="font-size: 0.5em; background: #eee; padding: 2px 8px; border-radius: 4px; vertical-align: middle;">
                    v<?php echo esc_html($this->get_plugin_version()); ?>
                </span>
            </h1>

            <?php foreach ($notices as $notice) : ?>
                <div class="updated"><p><?php echo esc_html($notice); ?></p></div>
            <?php endforeach; ?>

            <nav class="nav-tab-wrapper wp-clearfix" style="margin-bottom: 20px;">
                <a href="?page=wp-plugin-security&tab=general" class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-settings" style="margin-top: 4px;"></span> <?php _e('He thong & WAF', 'wp-plugin-security'); ?>
                </a>
                <a href="?page=wp-plugin-security&tab=login" class="nav-tab <?php echo $current_tab === 'login' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-lock" style="margin-top: 4px;"></span> <?php _e('Bao mat Dang nhap', 'wp-plugin-security'); ?>
                </a>
                <a href="?page=wp-plugin-security&tab=blacklist" class="nav-tab <?php echo $current_tab === 'blacklist' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-no-alt" style="margin-top: 4px;"></span> <?php _e('IP Blacklist', 'wp-plugin-security'); ?>
                </a>
                <a href="?page=wp-plugin-security&tab=audit" class="nav-tab <?php echo $current_tab === 'audit' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-list-view" style="margin-top: 4px;"></span> <?php _e('Audit Trail', 'wp-plugin-security'); ?>
                </a>
                <a href="?page=wp-plugin-security&tab=tools" class="nav-tab <?php echo $current_tab === 'tools' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-hammer" style="margin-top: 4px;"></span> <?php _e('Cong cu', 'wp-plugin-security'); ?>
                </a>
            </nav>

            <div class="wps-content-area">
                <?php if ($current_tab === 'general') : ?>
                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
                        <h2><?php _e('Tuong lua & Hardening', 'wp-plugin-security'); ?></h2>
                        <table class="form-table" role="presentation">
                            <?php $this->render_checkbox_row('disable_xmlrpc', 'Vo hieu hoa XML-RPC', $main_settings, 'Ngan chan brute-force qua XML-RPC.'); ?>
                            <?php $this->render_checkbox_row('disable_rest_api', 'Han che REST API', $main_settings, 'Chi cho phep nguoi dung da dang nhap truy cap API.'); ?>
                            <?php $this->render_checkbox_row('block_author_scan', 'Chan Author Scan', $main_settings, 'Ngan bot do tim username quan tri vien.'); ?>
                            <?php $this->render_checkbox_row('disable_directory_browsing', 'Chan Directory Browsing', $main_settings, 'Ngan truy cap liet ke file trong thu muc.'); ?>
                            <?php $this->render_checkbox_row('disable_file_editor', 'Tat trinh chinh sua file', $main_settings, 'Vo hieu hoa chinh sua code trong admin.'); ?>
                        </table>

                        <hr>

                        <h2><?php _e('Quyen rieng tu & Nhat ky', 'wp-plugin-security'); ?></h2>
                        <table class="form-table" role="presentation">
                            <?php $this->render_checkbox_row('hide_wp_version', 'An phien ban WP', $main_settings, 'Xoa dau hieu nhan biet phien ban tu ma nguon.'); ?>
                            <?php $this->render_checkbox_row('enable_security_headers', 'Security Headers', $main_settings, 'Kich hoat HSTS, XSS Protection, nosniff...'); ?>
                            <?php $this->render_checkbox_row('enable_audit_log', 'Audit Trail', $main_settings, 'Luu lai moi hoat dong cua nguoi dung.'); ?>
                        </table>

                        <input type="hidden" name="wps_save_settings" value="1">
                        <?php submit_button(__('Luu thiet lap He thong', 'wp-plugin-security')); ?>
                    </form>

                <?php elseif ($current_tab === 'login') : ?>
                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
                        <h2><?php _e('Rename Login', 'wp-plugin-security'); ?></h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="rename_login_slug"><?php _e('Duong dan dang nhap moi', 'wp-plugin-security'); ?></label></th>
                                <td>
                                    <code><?php echo esc_html(home_url('/')); ?></code>
                                    <input type="text" id="rename_login_slug" name="rename_login_slug" value="<?php echo esc_attr($main_settings['rename_login_slug'] ?? ''); ?>" placeholder="vi-du: secret-login" class="regular-text">
                                    <p class="description"><?php _e('Neu de trong, plugin se dung wp-login.php mac dinh.', 'wp-plugin-security'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <hr>

                        <h2><?php _e('Brute Force Protection', 'wp-plugin-security'); ?></h2>
                        <table class="form-table" role="presentation">
                            <?php $this->render_checkbox_row('limit_login_attempts', 'Gioi han dang nhap', $main_settings, 'Khoa IP neu dang nhap sai nhieu lan.'); ?>
                            <?php $this->render_checkbox_row('mask_login_errors', 'An loi dang nhap', $main_settings, 'Khong cho biet username hay password sai.'); ?>
                            <?php $this->render_number_row('max_login_attempts', 'Thu toi da', $main_settings, 5); ?>
                            <?php $this->render_number_row('lockout_duration', 'Thoi gian khoa (phut)', $main_settings, 60); ?>
                        </table>

                        <hr>

                        <h2><?php _e('Chinh sach nguoi dung', 'wp-plugin-security'); ?></h2>
                        <table class="form-table" role="presentation">
                            <?php $this->render_checkbox_row('enforce_strong_password', 'Mat khau manh', $main_settings, 'Bat buoc dung mat khau manh voi it nhat 12 ky tu.'); ?>
                            <?php $this->render_number_row('idle_logout_time', 'Tu dong dang xuat (phut)', $main_settings, 0, '0 de tat chuc nang nay.'); ?>
                        </table>

                        <input type="hidden" name="wps_save_settings" value="1">
                        <?php submit_button(__('Luu thiet lap Dang nhap', 'wp-plugin-security')); ?>
                    </form>

                <?php elseif ($current_tab === 'blacklist') : ?>
                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
                        <div class="card" style="max-width: 100%; margin-top: 0;">
                            <h2><?php _e('Quan ly IP bi chan', 'wp-plugin-security'); ?></h2>
                            <p class="description"><?php _e('Nhap moi dia chi IP tren mot dong.', 'wp-plugin-security'); ?></p>
                            <textarea name="wps_blocked_ips_raw" rows="10" class="large-text code" style="width: 100%;"><?php echo esc_textarea($ips_text); ?></textarea>
                        </div>

                        <div class="card" style="max-width: 100%; margin-top: 20px;">
                            <h2><?php _e('Nhat ky chan tu dong (Gan day)', 'wp-plugin-security'); ?></h2>
                            <table class="widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th width="150"><?php _e('Thoi gian', 'wp-plugin-security'); ?></th>
                                        <th width="150"><?php _e('IP', 'wp-plugin-security'); ?></th>
                                        <th><?php _e('Ly do', 'wp-plugin-security'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $auto_blocked = array_filter($security_logs, function ($log) {
                                        return in_array($log['type'], ['ip_blocked', 'dangerous_request'], true);
                                    });
                                    if (empty($auto_blocked)) :
                                        ?>
                                        <tr>
                                            <td colspan="3"><?php _e('Chua co IP bi chan tu dong.', 'wp-plugin-security'); ?></td>
                                        </tr>
                                    <?php else : ?>
                                        <?php foreach (array_slice(array_reverse($auto_blocked), 0, 10) as $log) : ?>
                                            <tr>
                                                <td><?php echo esc_html(date('H:i d/m/Y', strtotime($log['time']))); ?></td>
                                                <td><code><?php echo esc_html($log['ip']); ?></code></td>
                                                <td><?php echo esc_html($log['message']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="wps_save_settings" value="1">
                        <?php submit_button(__('Cap nhat Blacklist', 'wp-plugin-security')); ?>
                    </form>

                <?php elseif ($current_tab === 'audit') : ?>
                    <h2><?php _e('Lich su hoat dong (Audit Trail)', 'wp-plugin-security'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th width="150"><?php _e('Thoi gian', 'wp-plugin-security'); ?></th>
                                <th width="150"><?php _e('Nguoi dung', 'wp-plugin-security'); ?></th>
                                <th width="120"><?php _e('Hanh dong', 'wp-plugin-security'); ?></th>
                                <th><?php _e('Chi tiet', 'wp-plugin-security'); ?></th>
                                <th width="150"><?php _e('Dia chi IP', 'wp-plugin-security'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($audit_logs)) : ?>
                                <tr>
                                    <td colspan="5"><?php _e('Chua co hoat dong nao duoc ghi lai.', 'wp-plugin-security'); ?></td>
                                </tr>
                            <?php else : ?>
                                <?php foreach (array_reverse($audit_logs) as $log) : ?>
                                    <?php
                                    $action = strtolower($log['action'] ?? 'info');
                                    $color = '#64748b';
                                    if (strpos($action, 'login') !== false) {
                                        $color = '#10b981';
                                    }
                                    if (strpos($action, 'failed') !== false || strpos($action, 'blocked') !== false) {
                                        $color = '#ef4444';
                                    }
                                    ?>
                                    <tr>
                                        <td><small><?php echo esc_html(date('d/m/Y H:i:s', strtotime($log['time'] ?? 'now'))); ?></small></td>
                                        <td><strong><?php echo esc_html($log['user'] ?? 'Guest'); ?></strong></td>
                                        <td>
                                            <span style="background: <?php echo esc_attr($color); ?>; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase;">
                                                <?php echo esc_html($log['action'] ?? 'INFO'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html($log['message'] ?? ''); ?></td>
                                        <td><code><?php echo esc_html($log['ip'] ?? '0.0.0.0'); ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php elseif ($current_tab === 'tools') : ?>
                    <h2><?php _e('Cong cu bao mat khan cap', 'wp-plugin-security'); ?></h2>
                    <div class="card" style="border-left: 4px solid #d63638;">
                        <h3><?php _e('Ngat toan bo phien lam viec', 'wp-plugin-security'); ?></h3>
                        <p><?php _e('Dang xuat tat ca session tren website, bao gom ca tai khoan hien tai.', 'wp-plugin-security'); ?></p>
                        <form method="post" action="" onsubmit="return confirm('Tat ca session dang nhap se bi huy. Tiep tuc?');">
                            <?php wp_nonce_field('wps_tool_nonce_action', 'wps_tool_nonce'); ?>
                            <input type="hidden" name="wps_tool_action" value="kill_sessions">
                            <button type="submit" class="button button-link-delete" style="color: #d63638;"><?php _e('Kich hoat Logout All', 'wp-plugin-security'); ?></button>
                        </form>
                    </div>

                    <div class="card" style="border-left: 4px solid #d63638; margin-top: 20px;">
                        <h3><?php _e('Reset mat khau toan website', 'wp-plugin-security'); ?></h3>
                        <p><?php _e('Dat password ngau nhien moi cho tat ca tai khoan va huy toan bo session hien tai.', 'wp-plugin-security'); ?></p>
                        <form method="post" action="" onsubmit="return confirm('CANH BAO: Tat ca mat khau hien tai se bi vo hieu hoa. Tiep tuc?');">
                            <?php wp_nonce_field('wps_tool_nonce_action', 'wps_tool_nonce'); ?>
                            <input type="hidden" name="wps_tool_action" value="force_pw_reset">
                            <button type="submit" class="button button-link-delete" style="color: #d63638;"><?php _e('Kich hoat Reset Password Toan Dien', 'wp-plugin-security'); ?></button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render mot dong checkbox settings.
     */
    private function render_checkbox_row($key, $label, array $settings, $description)
    {
        ?>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html__($label, 'wp-plugin-security'); ?></label></th>
            <td>
                <input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="1" <?php checked($settings[$key] ?? false); ?>>
                <p class="description"><?php echo esc_html__($description, 'wp-plugin-security'); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Render mot dong input number settings.
     */
    private function render_number_row($key, $label, array $settings, $default = 0, $description = '')
    {
        ?>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html__($label, 'wp-plugin-security'); ?></label></th>
            <td>
                <input type="number" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($settings[$key] ?? $default); ?>" class="small-text">
                <?php if ($description !== '') : ?>
                    <p class="description"><?php echo esc_html__($description, 'wp-plugin-security'); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Dang xuat toan bo session tren website.
     */
    private function destroy_all_sessions()
    {
        if (method_exists('\WP_Session_Tokens', 'destroy_all_for_all_users')) {
            $count = count_users();
            \WP_Session_Tokens::destroy_all_for_all_users();

            return (int) ($count['total_users'] ?? 0);
        }

        $user_ids = get_users([
            'fields' => 'ID',
            'number' => -1,
        ]);

        $destroyed = 0;
        foreach ($user_ids as $user_id) {
            $sessions = \WP_Session_Tokens::get_instance($user_id);
            $sessions->destroy_all();
            $destroyed++;
        }

        return $destroyed;
    }

    /**
     * Dat random password moi cho tat ca tai khoan.
     */
    private function force_password_reset_for_all_users()
    {
        $user_ids = get_users([
            'fields' => 'ID',
            'number' => -1,
        ]);

        foreach ($user_ids as $user_id) {
            wp_set_password(wp_generate_password(32, true, true), $user_id);
            delete_user_meta($user_id, 'wps_last_action');
        }

        $this->destroy_all_sessions();

        return count($user_ids);
    }

    /**
     * Lay plugin version tu header file.
     */
    private function get_plugin_version()
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_data = get_plugin_data(WPS_PLUGIN_FILE, false, false);

        return $plugin_data['Version'] ?? '0.0.0';
    }
}

// Copyright by AcmaTvirus
