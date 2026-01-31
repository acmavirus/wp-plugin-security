<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Services;

/**
 * Service xử lý việc kiểm tra cập nhật từ GitHub
 */
class UpdateService
{
    private $username = 'acmavirus';
    private $repository = 'wp-plugin-security';
    private $plugin_file;

    public function __construct($plugin_file)
    {
        $this->plugin_file = $plugin_file;
    }

    /**
     * Lấy thông tin bản release mới nhất từ GitHub API
     */
    public function get_latest_release()
    {
        $url = "https://api.github.com/repos/{$this->username}/{$this->repository}/releases/latest";

        $args = [
            'timeout' => 10,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
        ];

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response));
    }

    /**
     * So sánh phiên bản hiện tại với bản mới nhất
     */
    public function check_for_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $release = $this->get_latest_release();
        if (!$release) {
            return $transient;
        }

        $current_version = $transient->checked[plugin_basename($this->plugin_file)] ?? '0.0.0';
        $remote_version = ltrim($release->tag_name, 'v');

        if (version_compare($current_version, $remote_version, '<')) {
            $plugin_slug = plugin_basename($this->plugin_file);

            $obj = new \stdClass();
            $obj->slug = $plugin_slug;
            $obj->new_version = $remote_version;
            $obj->url = "https://github.com/{$this->username}/{$this->repository}";
            $obj->package = $release->assets[0]->browser_download_url ?? ''; // Lấy link file zip đầu tiên

            $transient->response[$plugin_slug] = $obj;
        }

        return $transient;
    }
}

// Copyright by AcmaTvirus
