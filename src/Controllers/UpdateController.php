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

        // Hiển thị thông tin chi tiết plugin khi người dùng nhấn "View version details"
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
    }

    /**
     * Cung cấp thông tin plugin cho modal popup của WordPress
     */
    public function plugin_info($res, $action, $args)
    {
        $plugin_slug = plugin_basename(WPS_PLUGIN_FILE);

        if ($action !== 'plugin_information' || $args->slug !== $plugin_slug) {
            return $res;
        }

        $release = $this->update_service->get_latest_release();
        if (!$release) {
            return $res;
        }

        $res = new \stdClass();
        $res->name = 'WP Plugin Security';
        $res->slug = $plugin_slug;
        $res->version = ltrim($release->tag_name, 'v');
        $res->author = '<a href="https://thuc.me">AcmaTvirus</a>';
        $res->homepage = "https://github.com/acmavirus/wp-plugin-security";
        $res->download_link = $release->assets[0]->browser_download_url ?? '';
        $res->sections = [
            'description' => 'Giải pháp bảo mật toàn diện cho WordPress dựa trên kiến trúc Clean Architecture.',
            'changelog' => $release->body ?? 'Cập nhật phiên bản mới.'
        ];

        return $res;
    }
}

// Copyright by AcmaTvirus
