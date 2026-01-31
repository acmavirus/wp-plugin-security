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
        $this->init_controllers();
    }

    /**
     * Khởi tạo các controller
     */
    private function init_controllers()
    {
        new Controllers\SecurityController();
    }
}

// Copyright by AcmaTvirus
