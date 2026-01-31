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
        add_action('init', function () {
            load_plugin_textdomain('wp-plugin-security', false, dirname(plugin_basename(WPS_PLUGIN_FILE)) . '/languages');
        });

        // Khởi tạo các controller
        new Controllers\SecurityController();
        new Controllers\AdminController();
        new Controllers\UpdateController();
    }
}

// Copyright by AcmaTvirus
