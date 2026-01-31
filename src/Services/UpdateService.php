<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Services;

/**
 * Service xử lý việc kiểm tra cập nhật từ GitHub mã nguồn (Tracking Branch)
 * Dựa trên cấu trúc updater thành công của theme Ketsatphugiaan
 */
class UpdateService
{
    private $username = 'acmavirus';
    private $repository = 'wp-plugin-security';
    private $plugin_file;
    private $github_response;

    public function __construct($plugin_file)
    {
        $this->plugin_file = $plugin_file;
    }

    /**
     * Lấy thông tin phiên bản từ file chính trên GitHub (Raw content)
     */
    public function get_remote_version()
    {
        if (!empty($this->github_response)) {
            return $this->github_response;
        }

        // Lấy nội dung file chính từ GitHub để đọc version
        $url = "https://api.github.com/repos/{$this->username}/{$this->repository}/contents/wp-plugin-security.php?ref=main";

        $args = [
            'headers' => [
                'Accept' => 'application/vnd.github.v3.raw',
                'User-Agent' => 'WordPress-Plugin-Updater'
            ],
            'timeout' => 10
        ];

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            return false;
        }

        $content = wp_remote_retrieve_body($response);
        if (empty($content)) {
            return false;
        }

        // Parse version từ header
        preg_match('/Version:\s*(.*)$/mi', $content, $matches);
        $remote_version = isset($matches[1]) ? trim($matches[1]) : false;

        if (!$remote_version) {
            return false;
        }

        $this->github_response = (object) [
            'version' => $remote_version,
            'zip_url' => "https://github.com/{$this->username}/{$this->repository}/archive/refs/heads/main.zip",
            'url'     => "https://github.com/{$this->username}/{$this->repository}",
        ];

        return $this->github_response;
    }

    /**
     * So sánh phiên bản hiện tại với bản trên GitHub
     */
    public function check_for_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = $this->get_remote_version();
        if (!$remote) {
            return $transient;
        }

        $plugin_file = plugin_basename($this->plugin_file);
        $current_version = $transient->checked[$plugin_file] ?? '0.0.0';

        if (version_compare($current_version, $remote->version, '<')) {
            $obj = new \stdClass();
            $obj->slug = 'wp-plugin-security';
            $obj->plugin = $plugin_file;
            $obj->new_version = $remote->version;
            $obj->url = $remote->url;
            $obj->package = $remote->zip_url;

            $transient->response[$plugin_file] = $obj;
        }

        return $transient;
    }

    /**
     * Sửa tên thư mục plugin sau khi giải nén từ GitHub (thường bị thêm đuôi -main)
     */
    public function fix_source_selection($source, $remote_source, $upgrader, $hook_extra)
    {
        if (isset($hook_extra['plugin']) && $hook_extra['plugin'] === plugin_basename($this->plugin_file)) {
            $source_files = array_diff(scandir($source), array('..', '.'));
            if (count($source_files) === 1 && is_dir($source . '/' . current($source_files))) {
                $source = $source . '/' . current($source_files) . '/';
            }
        }
        return $source;
    }
}

// Copyright by AcmaTvirus
