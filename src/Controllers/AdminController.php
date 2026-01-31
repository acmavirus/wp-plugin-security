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

        // Xử lý lưu mảng IP
        if (isset($_POST['wps_blocked_ips_raw'])) {
            $raw_ips = explode("\n", str_replace("\r", "", $_POST['wps_blocked_ips_raw']));
            $clean_ips = array_filter(array_map('trim', $raw_ips));
            update_option('wps_blocked_ips', $clean_ips);
        }
    }
}

// Copyright by AcmaTvirus
