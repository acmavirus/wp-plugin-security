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
        // Đăng ký menu và settings
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // Đăng ký action links cho plugin (Settings | Check Update)
        // Lưu ý: WPS_PLUGIN_FILE được định nghĩa tại file chính wp-plugin-security.php
        $plugin_base = plugin_basename(WPS_PLUGIN_FILE);
        add_filter("plugin_action_links_{$plugin_base}", [$this, 'add_plugin_action_links']);
    }

    /**
     * Thêm liên kết Settings và Check Update vào danh sách plugin
     */
    public function add_plugin_action_links($links)
    {
        // 1. Tạo liên kết Settings (trỏ về menu slug wp-plugin-security)
        $settings_url = admin_url('admin.php?page=wp-plugin-security');
        $settings_link = '<a href="' . $settings_url . '">' . __('Settings', 'wp-plugin-security') . '</a>';

        // 2. Tạo liên kết Check Update (Force check WordPress core updates)
        $update_url = wp_nonce_url(admin_url('update-core.php?force-check=1'), 'upgrade-core');
        $update_link = '<a href="' . $update_url . '" style="color: #d63638; font-weight: bold;">' . __('Check Update', 'wp-plugin-security') . '</a>';

        // Thêm các liên kết mới vào đầu danh sách (trước chữ Deactivate)
        array_unshift($links, $settings_link, $update_link);

        return $links;
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
            'dashicons-shield-alt'
        );
    }

    /**
     * Đăng ký settings
     */
    public function register_settings()
    {
        register_setting('wps_settings_group', 'wps_blocked_ips');
    }

    /**
     * Render trang cấu hình
     */
    public function render_admin_page()
    {
        // Xử lý lưu mảng IP
        if (isset($_POST['wps_blocked_ips_raw'])) {
            $raw_ips = explode("\n", str_replace("\r", "", $_POST['wps_blocked_ips_raw']));
            $clean_ips = array_filter(array_map('trim', $raw_ips));
            update_option('wps_blocked_ips', $clean_ips);
        }

        $blocked_ips = get_option('wps_blocked_ips', []);
        $ips_text = is_array($blocked_ips) ? implode("\n", $blocked_ips) : '';
?>
        <div class="wrap">
            <h1>WP Plugin Security - Cấu hình</h1>
            <form method="post" action="">
                <?php settings_fields('wps_settings_group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Danh sách IP bị chặn (mỗi IP một dòng)</th>
                        <td>
                            <textarea name="wps_blocked_ips_raw" rows="10" cols="50" class="large-text"><?php echo esc_textarea($ips_text); ?></textarea>
                            <p class="description">Các bot tìm kiếm (Google, Bing...) sẽ luôn được phép truy cập bất kể danh sách này.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
<?php
    }
}

// Copyright by AcmaTvirus
