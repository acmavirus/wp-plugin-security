<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Core;

/**
 * Controller cơ sở cho các Feature
 */
abstract class BaseController
{
    /**
     * Khởi tạo Feature (Đăng ký hooks)
     */
    abstract public function init();

    /**
     * Render view tương ứng
     * 
     * @param string $template
     * @param array $data
     */
    protected function render($template, $data = [])
    {
        View::render($template, $data);
    }
}
