<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Core;

/**
 * Lớp hỗ trợ Render View (Template)
 */
class View
{
    /**
     * Render một template file
     * 
     * @param string $template_path Đường dẫn file template (relative to src/Features)
     * @param array $data Dữ liệu truyền vào template
     * @return void
     */
    public static function render($template_path, $data = [])
    {
        // Trích xuất data thành biến
        extract($data);

        // Đường dẫn tuyệt đối
        $full_path = dirname(__DIR__) . '/Features/' . $template_path . '.php';

        if (file_exists($full_path)) {
            include $full_path;
        } else {
            echo "Template not found: " . esc_html($template_path);
        }
    }
}
