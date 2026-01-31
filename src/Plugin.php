<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity;

/**
 * Class chính của Plugin
 */
class Plugin
{
    /**
     * @var Plugin
     */
    private static $instance;

    /**
     * @var object[] Danh sách các controller
     */
    private $controllers = [];

    /**
     * Trả về instance duy nhất của plugin
     * 
     * @return Plugin
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Khởi chạy plugin
     */
    public function run()
    {
        // Nạp text domain
        add_action('init', [$this, 'load_textdomain']);

        $this->init_controllers();
    }

    /**
     * Nạp ngôn ngữ
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('wp-plugin-security', false, dirname(plugin_basename(WPS_PLUGIN_FILE)) . '/languages');
    }

    /**
     * Khởi tạo các controller và lưu trữ instance để tránh bị giải phóng
     */
    private function init_controllers()
    {
        $this->controllers['security'] = new Controllers\SecurityController();
        $this->controllers['admin']    = new Controllers\AdminController();
        $this->controllers['update']   = new Controllers\UpdateController();
    }
}

// Copyright by AcmaTvirus
