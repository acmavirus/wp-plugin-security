<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Features\Monitoring;

use Acma\WpSecurity\Core\BaseController;
use Acma\WpSecurity\Services\SecurityService;

/**
 * Quản lý tính năng Giám sát & Quét (Malware, Integrity, Sessions)
 */
class MonitoringController extends BaseController
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
     * Render tab Giám sát & Quét
     */
    public function render_tab()
    {
        $malware_files = $this->security_service->scan_for_malware();
        $integrity_changes = $this->security_service->check_file_integrity();
        $sessions = $this->security_service->get_active_sessions(get_current_user_id());

        $this->render('Monitoring/Views/MonitoringTab', [
            'malware_files' => $malware_files,
            'integrity_changes' => $integrity_changes,
            'sessions' => $sessions
        ]);
    }
}
