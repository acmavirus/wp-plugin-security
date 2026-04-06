<?php

/**
 * Plugin Name: WP Plugin Security
 * Plugin URI:  https://github.com/acmavirus/wp-plugin-security
 * Description: Plugin bảo mật WordPress đa ngôn ngữ được xây dựng theo kiến trúc sạch.
 * Version:     3.0.14
 * Author:      AcmaTvirus
 * Author URI:  https://thuc.me
 * License:     GPL2
 * Text Domain: wp-plugin-security
 * Domain Path: /languages
 *
 * Copyright by AcmaTvirus
 */

// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants.
if (!defined('WPS_PLUGIN_FILE')) {
    define('WPS_PLUGIN_FILE', __FILE__);
}

// Load Composer autoloader.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Boot the plugin.
 */
if (class_exists('Acma\\WpSecurity\\Plugin')) {
    \Acma\WpSecurity\Plugin::instance()->run();
} else {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>' . esc_html__('WP Plugin Security', 'wp-plugin-security') . ':</strong> ' . esc_html__('Autoload thất bại hoặc không tìm thấy class chính. Vui lòng kiểm tra thư mục <code>vendor</code>.', 'wp-plugin-security') . '</p></div>';
    });
}

// Copyright by AcmaTvirus
