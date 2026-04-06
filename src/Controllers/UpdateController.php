<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Controllers;

use Acma\WpSecurity\Services\UpdateService;

/**
 * Controller xử lý các hooks liên quan đến cập nhật plugin
 */
class UpdateController
{
    private $update_service;

    public function __construct()
    {
        $this->update_service = new UpdateService(WPS_PLUGIN_FILE);
        $this->init_hooks();
    }

    private function init_hooks()
    {
        // Hook vào quá trình kiểm tra cập nhật của WordPress
        add_filter('pre_set_site_transient_update_plugins', [$this->update_service, 'check_for_update']);

        // Hiển thị thông tin chi tiết plugin
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);

        // Sửa lỗi cấu trúc thư mục GitHub (thư mục con sau khi giải nén)
        add_filter('upgrader_source_selection', [$this->update_service, 'fix_source_selection'], 10, 4);

        // Ajax handler for Check Update button
        add_action('wp_ajax_wps_check_update', [$this, 'ajax_check_update']);
    }

    /**
     * Handle AJAX Check Update
     */
    public function ajax_check_update()
    {
        check_ajax_referer('wps_check_update_nonce', 'nonce');

        // Force clear update transient
        delete_site_transient('update_plugins');

        $remote = $this->update_service->get_remote_version();

        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_data = get_plugin_data(WPS_PLUGIN_FILE, false, false);
        $current_version = $plugin_data['Version'] ?? '0.0.0';

        if ($remote && version_compare($current_version, $remote->version, '<')) {
            wp_send_json_success([
                'message' => 'Có phiên bản mới: v' . $remote->version . '. Đang tải lại trang...',
                'has_update' => true
            ]);
        } else {
            wp_send_json_success([
                'message' => 'Bạn đang sử dụng phiên bản mới nhất (' . $current_version . ')!',
                'has_update' => false
            ]);
        }
    }

    /**
     * Cung cấp thông tin plugin cho modal popup của WordPress
     */
    public function plugin_info($res, $action, $args)
    {
        $plugin_slug = 'wp-plugin-security';

        if ($action !== 'plugin_information' || $args->slug !== $plugin_slug) {
            return $res;
        }

        $remote = $this->update_service->get_remote_version();
        if (!$remote) {
            return $res;
        }

        $res = new \stdClass();
        $res->name = 'WP Plugin Security';
        $res->slug = $plugin_slug;
        $res->version = $remote->version;
        $res->author = '<a href="https://thuc.me">AcmaTvirus</a>';
        $res->homepage = $remote->url;
        $res->download_link = $remote->zip_url;
        $res->sections = [
            'description' => 'Giải pháp bảo mật toàn diện cho WordPress. Cập nhật tự động trực tiếp từ GitHub Releases.',
            'changelog' => $remote->body ?? 'Theo dõi các thay đổi mới nhất tại: ' . $remote->url
        ];

        return $res;
    }
}

// Copyright by AcmaTvirus
