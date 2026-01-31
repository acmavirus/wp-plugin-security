<?php

/**
 * Plugin Name: WP Plugin Security
 * Plugin URI:  https://github.com/acmavirus/wp-plugin-security
 * Description: Giải pháp bảo mật toàn diện cho WordPress dựa trên kiến trúc Clean Architecture.
 * Version:     1.0.2
 * Author:      AcmaTvirus
 * Author URI:  https://thuc.me
 * License:     GPL2
 * Text Domain: wp-plugin-security
 * Domain Path: /languages
 *
 * Copyright by AcmaTvirus
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Định nghĩa hằng số
define('WPS_PLUGIN_FILE', __FILE__);

// Nạp Autoloader từ Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Khởi tạo Plugin
 */
function wp_plugin_security_init()
{
    if (class_exists('Acma\\WpSecurity\\Plugin')) {
        \Acma\WpSecurity\Plugin::instance()->run();
    }
}
add_action('plugins_loaded', 'wp_plugin_security_init');

// Copyright by AcmaTvirus
