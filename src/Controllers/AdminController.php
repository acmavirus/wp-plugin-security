<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Controllers;

/**
 * Controller chính cho trang quản trị
 * Nhiệm vụ chính: Điều hướng (Routing) và Ủy quyền (Delegation) cho các Feature Controllers
 */
class AdminController
{
    /**
     * Khởi tạo các hooks cho admin
     */
    public function init_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        
        $plugin_file = 'wp-plugin-security/wp-plugin-security.php';
        add_filter("plugin_action_links_$plugin_file", [$this, 'add_action_links']);
    }

    /**
     * Enqueue CSS/JS cho trang quản trị
     */
    public function enqueue_admin_assets($hook)
    {
        if ($hook !== 'toplevel_page_wp-plugin-security') {
            return;
        }

        // Chỉ giữ lại Dashicons (đã có sẵn trong WP)
        wp_enqueue_style('dashicons');

        // Thêm CSS "State of the art" cho plugin
        $custom_css = "
            :root {
                --wps-primary: #6366f1;
                --wps-primary-dark: #4f46e5;
                --wps-success: #10b981;
                --wps-warning: #f59e0b;
                --wps-danger: #ef4444;
                --wps-bg: #f8fafc;
                --wps-card-bg: #ffffff;
                --wps-text: #1e293b;
                --wps-text-muted: #64748b;
            }
            .wps-wrap { 
                margin: 20px 20px 0 0; 
                font-family: 'Inter', -apple-system, system-ui, sans-serif;
                color: var(--wps-text);
            }
            .wps-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
                padding: 20px;
                background: linear-gradient(135deg, var(--wps-primary) 0%, var(--wps-primary-dark) 100%);
                border-radius: 16px;
                color: #fff;
                box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
            }
            .wps-header h1 { color: #fff !important; margin: 0 !important; font-size: 24px !important; font-weight: 800 !important; }
            .wps-header p { margin: 5px 0 0 !important; opacity: 0.8; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }

            .wps-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; margin-top: 25px; }
            .wps-card { 
                background: var(--wps-card-bg); 
                border-radius: 20px; 
                padding: 30px; 
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                border: 1px solid rgba(0,0,0,0.05);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .wps-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
            
            .wps-score-container { position: relative; width: 180px; height: 180px; margin: 0 auto 20px; }
            .wps-score-svg { transform: rotate(-90deg); width: 100%; height: 100%; }
            .wps-score-bg { fill: none; stroke: #f1f5f9; stroke-width: 12; }
            .wps-score-fill { fill: none; stroke-width: 12; stroke-linecap: round; transition: stroke-dasharray 1s ease-in-out; }
            .wps-score-text { 
                position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                text-align: center; width: 100%;
            }
            .wps-score-val { font-size: 42px; font-weight: 900; color: var(--wps-text); display: block; line-height: 1; }
            .wps-score-label { font-size: 14px; color: var(--wps-text-muted); font-weight: 600; text-transform: uppercase; margin-top: 5px; }

            .nav-tab-wrapper { border: none !important; padding: 0 !important; margin-bottom: 0 !important; display: flex; gap: 10px; flex-wrap: wrap; }
            .nav-tab { 
                border: none !important; background: #fff !important; color: var(--wps-text-muted) !important; 
                padding: 12px 24px !important; border-radius: 12px !important; font-weight: 700 !important; 
                margin: 0 !important; transition: all 0.2s !important; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            .nav-tab:hover { color: var(--wps-primary) !important; background: #f1f5f9 !important; }
            .nav-tab-active { background: var(--wps-primary) !important; color: #fff !important; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4) !important; }

            .wps-update-bar {
                background: #fff; border-radius: 12px; padding: 15px 25px; margin-top: 25px;
                display: flex; justify-content: space-between; align-items: center;
                border: 1px solid rgba(99, 102, 241, 0.1);
                box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            }
            .wps-update-info { display: flex; align-items: center; gap: 15px; }
            .wps-update-icon { 
                width: 40px; height: 40px; border-radius: 10px; background: #f1f5f9; 
                display: flex; align-items: center; justify-content: center; color: var(--wps-primary);
            }

            .wps-btn-primary { 
                background: var(--wps-primary); color: #fff; border: none; padding: 10px 20px; 
                border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;
                text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
            }
            .wps-btn-primary:hover { background: var(--wps-primary-dark); transform: scale(1.02); }
            
            .wps-badge { font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; letter-spacing: 0.5px; }
            .wps-badge-success { background: #dcfce7; color: #10b981; }
            .wps-badge-warning { background: #fef3c7; color: #f59e0b; }
            .wps-badge-danger { background: #fee2e2; color: #ef4444; }

            .wp-list-table { background: transparent !important; border: none !important; }
            .wp-list-table td, .wp-list-table th { padding: 15px !important; vertical-align: middle !important; }
            .wp-list-table tr { background: #fff; margin-bottom: 10px; border-radius: 10px; display: table-row; }
        ";
        wp_add_inline_style('wp-admin', $custom_css);
    }

    /**
     * Thêm link 'Settings' trong trang Plugins
     */
    public function add_action_links($links)
    {
        $custom_links = [
            '<a href="' . admin_url('admin.php?page=wp-plugin-security') . '">Cài đặt</a>',
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
        register_setting('wps_settings_group', 'wps_whitelist_ips');
        register_setting('wps_settings_group', 'wps_main_settings');
    }

    /**
     * Khởi tạo các Feature Controllers
     */
    private function get_feature_controllers()
    {
        return [
            'dashboard'  => new \Acma\WpSecurity\Features\Dashboard\DashboardController(),
            'firewall'   => new \Acma\WpSecurity\Features\Firewall\FirewallController(),
            'auth'       => new \Acma\WpSecurity\Features\Auth\AuthController(),
            'audit'      => new \Acma\WpSecurity\Features\Audit\AuditController(),
            'monitoring' => new \Acma\WpSecurity\Features\Monitoring\MonitoringController(),
            'tools'      => new \Acma\WpSecurity\Features\Tools\ToolsController(),
            'updates'    => new \Acma\WpSecurity\Services\UpdateService(WPS_PLUGIN_FILE),
        ];
    }

    /**
     * Render trang chính
     */
    public function render_admin_page()
    {
        $current_tab = $_GET['tab'] ?? 'dashboard';
        $features = $this->get_feature_controllers();

        // Xử lý POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['wps_tool_action'])) {
                $features['tools']->handle_actions();
            }

            if (isset($_POST['wps_save_settings'])) {
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
                        'enable_email_alerts'     => isset($_POST['enable_email_alerts']),
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
                        'enable_2fa'              => isset($_POST['enable_2fa']),
                        'recaptcha_site_key'      => sanitize_text_field($_POST['recaptcha_site_key'] ?? ''),
                        'recaptcha_secret_key'    => sanitize_text_field($_POST['recaptcha_secret_key'] ?? ''),
                    ]);
                }

                update_option('wps_main_settings', $main_settings);

                if ($current_tab === 'blacklist') {
                    $features['firewall']->handle_save_ips();
                }

                echo '<div class="updated"><p>Thiết lập đã được lưu thành công.</p></div>';
            }
        }

        $main_settings = get_option('wps_main_settings', []);
        $security_logs = get_option('wps_security_logs', []);
?>
        <div class="wps-wrap">
            <header class="wps-header">
                <div>
                    <h1>WP Plugin Security</h1>
                    <p>Hệ thống bảo vệ toàn diện &bull; Copyright by AcmaTvirus Intelligence</p>
                </div>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div style="text-align: right;">
                        <div style="font-weight: 800; font-size: 14px;"><?php echo wp_get_current_user()->display_name; ?></div>
                        <div style="font-size: 11px; font-weight: 700; opacity: 0.7; text-transform: uppercase;">Quản trị viên</div>
                    </div>
                    <div style="width: 45px; height: 45px; border-radius: 12px; overflow: hidden; border: 2px solid rgba(255,255,255,0.2);">
                        <?php echo get_avatar(get_current_user_id(), 45); ?>
                    </div>
                </div>
            </header>

            <nav class="nav-tab-wrapper">
                <a href="?page=wp-plugin-security&tab=dashboard" class="nav-tab <?php echo $current_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-dashboard" style="margin-top: 4px;"></span> Tổng quan
                </a>
                <a href="?page=wp-plugin-security&tab=general" class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-settings" style="margin-top: 4px;"></span> Hệ thống
                </a>
                <a href="?page=wp-plugin-security&tab=login" class="nav-tab <?php echo $current_tab === 'login' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-lock" style="margin-top: 4px;"></span> Bảo mật Đăng nhập
                </a>
                <a href="?page=wp-plugin-security&tab=blacklist" class="nav-tab <?php echo $current_tab === 'blacklist' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-shield-alt" style="margin-top: 4px;"></span> Firewall IP
                </a>
                <a href="?page=wp-plugin-security&tab=audit" class="nav-tab <?php echo $current_tab === 'audit' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-visibility" style="margin-top: 4px;"></span> Nhật ký
                </a>
                <a href="?page=wp-plugin-security&tab=monitoring" class="nav-tab <?php echo $current_tab === 'monitoring' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-chart-area" style="margin-top: 4px;"></span> Theo dõi
                </a>
                <a href="?page=wp-plugin-security&tab=tools" class="nav-tab <?php echo $current_tab === 'tools' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-tools" style="margin-top: 4px;"></span> Công cụ
                </a>
                <a href="?page=wp-plugin-security&tab=updates" class="nav-tab <?php echo $current_tab === 'updates' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-update" style="margin-top: 4px;"></span> Cập nhật
                </a>
            </nav>

            <div class="wps-main-content" style="margin-top: 30px;">
                <?php 
                switch ($current_tab) {
                    case 'general':
                        $features['firewall']->render_general_tab($main_settings);
                        break;
                    case 'login':
                        $features['auth']->render_tab($main_settings);
                        break;
                    case 'blacklist':
                        $features['firewall']->render_blacklist_tab($security_logs);
                        break;
                    case 'audit':
                        $features['audit']->render_tab();
                        break;
                    case 'monitoring':
                        $features['monitoring']->render_tab();
                        break;
                    case 'tools':
                        $features['tools']->render_tab();
                        break;
                    case 'updates':
                        $remote_info = $features['updates']->get_remote_version();
                        include WPS_PLUGIN_DIR . 'src/Features/Updates/Views/UpdatesTab.php';
                        break;
                    case 'dashboard':
                    default:
                        $features['dashboard']->render_overview();
                        break;
                }
                ?>
            </div>

            <footer style="margin-top: 60px; text-align: center; color: var(--wps-text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px;">
                WP Plugin Security &bull; Phiên bản 2.1.0 &bull; Copyright &copy; <?php echo date('Y'); ?> AcmaTvirus Intelligence
            </footer>
        </div>
<?php
    }
}
