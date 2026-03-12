<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Features\Firewall;

use Acma\WpSecurity\Core\BaseController;
use Acma\WpSecurity\Services\SecurityService;

/**
 * Quản lý tính năng Tường lửa & Hardening
 */
class FirewallController extends BaseController
{
    private $security_service;

    public function __construct()
    {
        $this->security_service = new SecurityService();
    }

    public function init()
    {
        // Kiểm tra truy cập sớm nhất
        add_action('init', [$this, 'handle_security_checks'], 1);

        // Header bảo mật
        add_filter('wp_headers', [$this, 'add_security_headers']);

        // Privacy Hardening
        add_action('init', [$this, 'privacy_hardening']);

        // XML-RPC
        if ($this->security_service->get_setting('disable_xmlrpc', true)) {
            add_filter('xmlrpc_enabled', '__return_false');
            add_filter('wp_xmlrpc_server_class', '__return_false');
        }

        // Hide version
        if ($this->security_service->get_setting('hide_wp_version', true)) {
            add_filter('the_generator', '__return_empty_string');
            remove_action('wp_head', 'wp_generator');
        }

        // File Editor
        if ($this->security_service->get_setting('disable_file_editor', true)) {
            if (!defined('DISALLOW_FILE_EDIT')) {
                define('DISALLOW_FILE_EDIT', true);
            }
        }
    }

    public function handle_security_checks()
    {
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($this->security_service->is_ip_whitelisted($user_ip)) return;

        if ($this->security_service->is_ip_blocked($user_ip)) {
            $this->security_service->log_event('ip_blocked', "IP $user_ip cố gắng truy cập");
            wp_die('Truy cập bị chặn bởi WP Plugin Security!', 'Access Denied', ['response' => 403]);
        }

        if ($this->security_service->is_dangerous_request()) {
            $this->security_service->log_event('dangerous_request', "Phát hiện request nguy hiểm từ IP $user_ip");
            wp_die('Phát hiện hành vi nguy hiểm!', 'Security Warning', ['response' => 400]);
        }
    }

    public function add_security_headers($headers)
    {
        if ($this->security_service->get_setting('enable_security_headers', true)) {
             $security_headers = $this->security_service->get_security_headers();
             return array_merge($headers, $security_headers);
        }
        return $headers;
    }

    public function privacy_hardening()
    {
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'rest_output_link_wp_head');
    }

    /**
     * Xử lý lưu thiết lập Blacklist/Whitelist
     */
    public function handle_save_ips()
    {
        if (!current_user_can('manage_options')) return;

        // Handle Blacklist
        $raw_ips = explode("\n", str_replace("\r", "", $_POST['wps_blocked_ips_raw'] ?? ''));
        $clean_ips = array_unique(array_filter(array_map('trim', $raw_ips)));
        update_option('wps_blocked_ips', $clean_ips);

        // Handle Whitelist
        $raw_white = explode("\n", str_replace("\r", "", $_POST['wps_whitelist_ips_raw'] ?? ''));
        $clean_white = array_unique(array_filter(array_map('trim', $raw_white)));
        update_option('wps_whitelist_ips', $clean_white);
    }

    /**
     * Render tab Hệ thống & WAF
     */
    public function render_general_tab($main_settings)
    {
        $this->render('Firewall/Views/GeneralTab', [
            'main_settings' => $main_settings
        ]);
    }

    /**
     * Render tab IP Blacklist
     */
    public function render_blacklist_tab($security_logs)
    {
        $blocked_ips = get_option('wps_blocked_ips', []);
        $ips_text = is_array($blocked_ips) ? implode("\n", $blocked_ips) : '';

        $whitelist_ips = get_option('wps_whitelist_ips', []);
        $whitelist_text = is_array($whitelist_ips) ? implode("\n", $whitelist_ips) : '';

        $this->render('Firewall/Views/BlacklistTab', [
            'ips_text' => $ips_text,
            'whitelist_text' => $whitelist_text,
            'security_logs' => $security_logs
        ]);
    }
}
