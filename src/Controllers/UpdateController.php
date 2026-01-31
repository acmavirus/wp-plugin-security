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
            'description' => 'Giải pháp bảo mật toàn diện cho WordPress. Cập nhật tự động trực tiếp từ GitHub (Branch: main).',
            'changelog' => 'Theo dõi các thay đổi mới nhất tại: ' . $remote->url . '/commits/main'
        ];

        return $res;
    }
}

// Copyright by AcmaTvirus
