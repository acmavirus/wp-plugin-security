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
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // Thêm liên kết vào trang danh sách plugin
        $plugin_base = plugin_basename(WPS_PLUGIN_FILE);
        add_filter("plugin_action_links_$plugin_base", [$this, 'add_plugin_action_links']);
    }

    /**
     * Thêm liên kết Settings và Check Update vào danh sách plugin
     */
    public function add_plugin_action_links($links)
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=wp-plugin-security') . '">Settings</a>';

        // Link này sẽ kích hoạt việc kiểm tra cập nhật của WordPress
        $update_url = wp_nonce_url(admin_url('update-core.php?force-check=1'), 'upgrade-core');
        $update_link = '<a href="' . $update_url . '" style="color: #d63638; font-weight: bold;">Check Update</a>';

        array_unshift($links, $settings_link);
        $links[] = $update_link;

        return $links;
    }

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

    public function register_settings()
    {
        register_setting('wps_settings_group', 'wps_blocked_ips');
    }

    public function render_admin_page()
    {
        $blocked_ips = get_option('wps_blocked_ips', []);
        $ips_text = is_array($blocked_ips) ? implode("\n", $blocked_ips) : '';
?>
        <div class="wrap">
            <h1>WP Plugin Security - Cấu hình</h1>
            <form method="post" action="options.php">
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

        if (isset($_POST['wps_blocked_ips_raw'])) {
            $raw_ips = explode("\n", str_replace("\r", "", $_POST['wps_blocked_ips_raw']));
            $clean_ips = array_filter(array_map('trim', $raw_ips));
            update_option('wps_blocked_ips', $clean_ips);
        }
    }
}

// Copyright by AcmaTvirus
