<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Services;

/**
 * Service xử lý việc kiểm tra cập nhật từ GitHub Releases Assets
 * Đảm bảo gói tải về chứa đầy đủ thư mục vendor (đã qua build)
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
     * Lấy thông tin bản release mới nhất từ GitHub API (kèm Assets)
     */
    public function get_remote_version()
    {
        if (!empty($this->github_response)) {
            return $this->github_response;
        }

        $url = "https://api.github.com/repos/{$this->username}/{$this->repository}/releases/latest";

        $args = [
            'headers' => [
                'User-Agent' => 'WordPress-Plugin-Updater'
            ],
            'timeout' => 10
        ];

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response));
        if (!$data || empty($data->tag_name)) {
            return false;
        }

        // Ưu tiên lấy file zip được đính kèm trong Assets (vì nó có chứa vendor)
        $download_url = '';
        if (!empty($data->assets)) {
            foreach ($data->assets as $asset) {
                if (strpos($asset->name, '.zip') !== false) {
                    $download_url = $asset->browser_download_url;
                    break;
                }
            }
        }

        // Nếu không có assets, dùng link zipball mặc định của GitHub (Lưu ý: link này không có vendor)
        if (empty($download_url)) {
            $download_url = $data->zipball_url;
        }

        $this->github_response = (object) [
            'version' => ltrim($data->tag_name, 'v'),
            'zip_url' => $download_url,
            'url'     => $data->html_url,
            'body'    => $data->body,
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
     * Sửa tên thư mục plugin sau khi giải nén
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
