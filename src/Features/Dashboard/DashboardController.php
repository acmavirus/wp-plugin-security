<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Features\Dashboard;

use Acma\WpSecurity\Core\BaseController;
use Acma\WpSecurity\Services\SecurityService;

/**
 * Quản lý Dashboard và Thống kê tổng quan
 */
class DashboardController extends BaseController
{
    private $security_service;

    public function __construct()
    {
        $this->security_service = new SecurityService();
    }

    public function init()
    {
        // Hooks
    }

    /**
     * Render trang tổng quan Dashboard
     */
    public function render_overview()
    {
        $security_score = $this->security_service->calculate_security_score();
        $blocked_ips = get_option('wps_blocked_ips', []);
        
        $this->render('Dashboard/Views/Overview', [
            'security_score' => $security_score,
            'blocked_count' => count($blocked_ips)
        ]);
    }
}
