<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Controllers;

use Acma\WpSecurity\Services\UpdateService;

/**
 * Controller xử lý các hooks liên quan đến cập nhật plugin.
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
        add_filter('pre_set_site_transient_update_plugins', [$this->update_service, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_filter('upgrader_source_selection', [$this->update_service, 'fix_source_selection'], 10, 4);
        add_action('wp_ajax_wps_check_update', [$this, 'ajax_check_update']);
    }

    /**
     * Xử lý AJAX Check Update.
     */
    public function ajax_check_update()
    {
        check_ajax_referer('wps_check_update_nonce', 'nonce');

        delete_site_transient('update_plugins');

        $remote = $this->update_service->get_remote_version();

        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_data = get_plugin_data(WPS_PLUGIN_FILE, false, false);
        $current_version = $plugin_data['Version'] ?? '0.0.0';

        if ($remote && version_compare($current_version, $remote->version, '<')) {
            wp_send_json_success([
                'message' => sprintf(__('Có phiên bản mới: v%s. Đang tải lại trang...', 'wp-plugin-security'), $remote->version),
                'has_update' => true,
            ]);
        } else {
            wp_send_json_success([
                'message' => sprintf(__('Bạn đang sử dụng phiên bản mới nhất (%s)!', 'wp-plugin-security'), $current_version),
                'has_update' => false,
            ]);
        }
    }

    /**
     * Cung cấp thông tin plugin cho modal popup của WordPress.
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
            'description' => __('Giải pháp bảo mật toàn diện cho WordPress. Cập nhật trực tiếp từ GitHub Releases.', 'wp-plugin-security'),
            'changelog' => $remote->body ?? sprintf(__('Theo dõi các thay đổi mới nhất tại: %s', 'wp-plugin-security'), $remote->url),
        ];

        return $res;
    }
}

// Copyright by AcmaTvirus
