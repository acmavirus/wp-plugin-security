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

        // Tailwind CSS via CDN (Dùng cho mẫu này, thực tế nên build local)
        wp_enqueue_style('wps-tailwind', 'https://cdn.tailwindcss.com', [], '3.4.1');
        
        // Font cao cấp từ Google Fonts
        wp_enqueue_style('wps-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap', [], null);

        // Custom Inline CSS để xử lý Glassmorphism và các hiệu ứng nâng cao
        $custom_css = "
            body.toplevel_page_wp_plugin_security { background-color: #f0f2f5; font-family: 'Plus Jakarta Sans', sans-serif; }
            #wpcontent { padding-left: 0; }
            .glass-card { 
                background: rgba(255, 255, 255, 0.7); 
                backdrop-filter: blur(14px); 
                border: 1px solid rgba(255, 255, 255, 0.4); 
                box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
            }
            .dark-glass-card {
                background: rgba(17, 24, 39, 0.95);
                backdrop-filter: blur(14px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: white;
            }
            .wps-sidebar-active {
                background: black;
                color: white !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
            }
            .wps-sidebar-active span { color: white !important; }
            input:focus, select:focus, textarea:focus { outline: none !important; border-color: black !important; ring: 0 !important; }
            .wps-switch input:checked ~ .wps-dot { transform: translateX(1.5rem); }
            /* Hide WP default notices in our dashboard */
            .toplevel_page_wp-plugin-security .notice, 
            .toplevel_page_wp-plugin-security .updated, 
            .toplevel_page_wp-plugin-security .error { display: none !important; }
        ";
        wp_add_inline_style('wps-tailwind', $custom_css);

        // Tailwindow config
        wp_add_inline_script('wps-tailwind', "
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                        borderRadius: { '4xl': '2rem', '5xl': '3rem' }
                    }
                }
            }
        ");
    }

    /**
     * Thêm link 'Settings' trong trang Plugins
     */
    public function add_action_links($links)
    {
        $custom_links = [
            '<a href="' . admin_url('admin.php?page=wp-plugin-security') . '">Settings</a>',
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
     * Khởi tạo các Feature Controllers (Lazy loading hoặc cached)
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
        ];
    }

    /**
     * Render trang chính
     */
    public function render_admin_page()
    {
        $current_tab = $_GET['tab'] ?? 'general';
        $features = $this->get_feature_controllers();

        // Xử lý POST (Uỷ nhiệm cho các controller tương ứng)
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

                echo '<div class="updated"><p>Cấu hình đã được lưu thành công.</p></div>';
            }
        }

        $main_settings = get_option('wps_main_settings', []);
        $security_logs = get_option('wps_security_logs', []);
?>
        <div id="wps-admin-root" class="flex min-h-screen bg-[#f0f2f5] p-0 font-sans">
            <!-- Sidebar -->
            <aside class="w-64 glass-card m-4 mr-0 rounded-[40px] flex flex-col overflow-hidden z-10 shrink-0">
                <div class="p-8 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-black flex items-center justify-center shadow-2xl">
                        <span class="dashicons dashicons-shield text-white !text-xl !w-auto !h-auto"></span>
                    </div>
                    <span class="text-xl font-extrabold tracking-tighter">WPSECURE</span>
                </div>

                <nav class="flex-grow px-4 space-y-2 mt-4">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-4 mb-4">Protection</div>
                    
                    <a href="?page=wp-plugin-security&tab=general" class="flex items-center gap-4 px-6 py-4 rounded-3xl transition-all group <?php echo $current_tab === 'general' ? 'wps-sidebar-active' : 'text-gray-500 hover:bg-gray-100/50'; ?>">
                        <span class="dashicons dashicons-admin-settings !text-lg transition-transform group-hover:scale-110"></span>
                        <span class="font-bold text-xs uppercase tracking-wider">Hệ thống</span>
                    </a>
                    
                    <a href="?page=wp-plugin-security&tab=login" class="flex items-center gap-4 px-6 py-4 rounded-3xl transition-all group <?php echo $current_tab === 'login' ? 'wps-sidebar-active' : 'text-gray-500 hover:bg-gray-100/50'; ?>">
                        <span class="dashicons dashicons-lock !text-lg transition-transform group-hover:scale-110"></span>
                        <span class="font-bold text-xs uppercase tracking-wider">Đăng nhập</span>
                    </a>

                    <a href="?page=wp-plugin-security&tab=blacklist" class="flex items-center gap-4 px-6 py-4 rounded-3xl transition-all group <?php echo $current_tab === 'blacklist' ? 'wps-sidebar-active' : 'text-gray-500 hover:bg-gray-100/50'; ?>">
                        <span class="dashicons dashicons-no-alt !text-lg transition-transform group-hover:scale-110"></span>
                        <span class="font-bold text-xs uppercase tracking-wider">Firewall IP</span>
                    </a>

                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-4 mt-8 mb-4">Analysis</div>

                    <a href="?page=wp-plugin-security&tab=audit" class="flex items-center gap-4 px-6 py-4 rounded-3xl transition-all group <?php echo $current_tab === 'audit' ? 'wps-sidebar-active' : 'text-gray-500 hover:bg-gray-100/50'; ?>">
                        <span class="dashicons dashicons-list-view !text-lg transition-transform group-hover:scale-110"></span>
                        <span class="font-bold text-xs uppercase tracking-wider">Audit Log</span>
                    </a>

                    <a href="?page=wp-plugin-security&tab=monitoring" class="flex items-center gap-4 px-6 py-4 rounded-3xl transition-all group <?php echo $current_tab === 'monitoring' ? 'wps-sidebar-active' : 'text-gray-500 hover:bg-gray-100/50'; ?>">
                        <span class="dashicons dashicons-visibility !text-lg transition-transform group-hover:scale-110"></span>
                        <span class="font-bold text-xs uppercase tracking-wider">Theo dõi</span>
                    </a>

                    <a href="?page=wp-plugin-security&tab=tools" class="flex items-center gap-4 px-6 py-4 rounded-3xl transition-all group <?php echo $current_tab === 'tools' ? 'wps-sidebar-active' : 'text-gray-500 hover:bg-gray-100/50'; ?>">
                        <span class="dashicons dashicons-hammer !text-lg transition-transform group-hover:scale-110"></span>
                        <span class="font-bold text-xs uppercase tracking-wider">Công cụ</span>
                    </a>
                </nav>

                <!-- PRO Promo -->
                <div class="p-6">
                    <div class="dark-glass-card p-6 rounded-[32px] relative overflow-hidden group hover:scale-[1.02] transition-all">
                        <div class="absolute -right-10 -top-10 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                        <div class="text-[9px] text-gray-400 font-black mb-1 uppercase tracking-[0.2em]">Upgrade Now</div>
                        <div class="font-bold text-xs mb-4">Go Premium for Advanced WAF</div>
                        <a href="#" class="block text-center bg-white text-black py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-200 transition-colors">Explorer PRO</a>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-grow p-8 overflow-y-auto max-h-screen">
                <!-- Top Header -->
                <header class="flex justify-between items-center mb-12">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-sm">
                             <span class="dashicons dashicons-menu-alt3 text-black"></span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Security Center</h2>
                            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Powered by AcmaTvirus Intelligence</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl shadow-sm border border-gray-100/50 group cursor-pointer hover:border-black transition-all">
                             <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                             <span class="text-[10px] font-bold uppercase tracking-widest group-hover:text-black">All Systems Functional</span>
                        </div>
                        
                        <div class="w-12 h-12 rounded-2xl bg-white border border-gray-100 flex items-center justify-center shadow-sm cursor-pointer hover:bg-gray-50">
                             <span class="dashicons dashicons-bell !text-lg text-gray-400"></span>
                        </div>

                        <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
                             <div class="text-right">
                                 <div class="text-xs font-bold"><?php echo wp_get_current_user()->display_name; ?></div>
                                 <div class="text-[9px] font-bold text-gray-400 uppercase">Administrator</div>
                             </div>
                             <div class="w-12 h-12 rounded-2xl border-2 border-white shadow-xl overflow-hidden cursor-pointer hover:scale-105 transition-all">
                                 <?php echo get_avatar(get_current_user_id(), 48); ?>
                             </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content Components -->
                <section class="space-y-10 pb-12">
                    <?php 
                    // Luôn hiển thị Overview ở trang General hoặc Dashboard
                    if ($current_tab === 'general' || $current_tab === 'dashboard') {
                        $features['dashboard']->render_overview();
                    }
                    ?>

                    <div class="page-container relative">
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
                            default:
                                $features['dashboard']->render_overview();
                                break;
                        }
                        ?>
                    </div>
                </section>

                <footer class="mt-8 border-t border-gray-100 pt-8 flex justify-between items-center text-[9px] font-bold uppercase tracking-[0.3em] text-gray-300">
                    <div>WP Plugin Security &bull; Version 2.0.0</div>
                    <div>&copy; <?php echo date('Y'); ?> AcmaTvirus Intelligence</div>
                </footer>
            </main>
        </div>
<?php
    }
}
