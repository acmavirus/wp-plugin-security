<?php
// Copyright by AcmaTvirus
if (!defined('ABSPATH')) exit;
?>

<form method="post" action="" class="space-y-8">
    <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
        <div class="space-y-6">
            <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                <span class="dashicons dashicons-shield text-black"></span>
                <h3 class="font-bold text-lg">Tường lửa & Hardening</h3>
            </div>

            <div class="space-y-4">
                <?php
                $general_options = [
                    'disable_xmlrpc' => ['Vô hiệu hóa XML-RPC', 'Chặn tấn công brute-force qua cổng XML-RPC.'],
                    'disable_rest_api' => ['Hạn chế REST API', 'Chỉ cho phép người dùng đã đăng nhập truy cập.'],
                    'block_author_scan' => ['Chặn Author Scan', 'Ngăn bot dò tìm username quản trị viên.'],
                    'disable_directory_browsing' => ['Chặn Directory Browsing', 'Ngăn duyệt file trong các thư mục.'],
                    'disable_file_editor' => ['Tắt trình chỉnh sửa file', 'Vô hiệu hóa sửa code trực tiếp trong Admin.'],
                ];
                foreach ($general_options as $key => $info) :
                ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-transparent hover:border-gray-200 transition-all">
                        <div>
                            <h4 class="font-bold text-sm"><?php echo $info[0]; ?></h4>
                            <p class="text-xs text-gray-400"><?php echo $info[1]; ?></p>
                        </div>
                        <div class="wps-switch relative inline-block w-12 h-6">
                            <input type="checkbox" name="<?php echo $key; ?>" <?php checked($main_settings[$key] ?? false); ?> class="sr-only peer">
                            <div class="w-full h-full bg-gray-200 rounded-full peer-checked:bg-black transition-colors"></div>
                            <div class="wps-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-6"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                <span class="dashicons dashicons-visibility text-black"></span>
                <h3 class="font-bold text-lg">Quyền riêng tư & Nhật ký</h3>
            </div>

            <div class="space-y-4">
                <?php
                $privacy_options = [
                    'hide_wp_version' => ['Ẩn phiên bản WP', 'Xóa bỏ dấu hiệu nhận biết từ mã nguồn.'],
                    'enable_security_headers' => ['Security Headers', 'Kích hoạt HSTS, XSS Protection, nosniff...'],
                    'enable_audit_log' => ['Audit Trail', 'Lưu lại mọi hoạt động của người dùng.'],
                    'enable_email_alerts' => ['Thông báo qua Email', 'Gửi cảnh báo ngay khi có sự cố bảo mật.'],
                ];
                foreach ($privacy_options as $key => $info) :
                ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-transparent hover:border-gray-200 transition-all">
                        <div>
                            <h4 class="font-bold text-sm"><?php echo $info[0]; ?></h4>
                            <p class="text-xs text-gray-400"><?php echo $info[1]; ?></p>
                        </div>
                        <div class="wps-switch relative inline-block w-12 h-6">
                            <input type="checkbox" name="<?php echo $key; ?>" <?php checked($main_settings[$key] ?? false); ?> class="sr-only peer">
                            <div class="w-full h-full bg-gray-200 rounded-full peer-checked:bg-black transition-colors"></div>
                            <div class="wps-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-6"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="p-6 bg-black text-white rounded-[32px] shadow-xl relative overflow-hidden mt-6">
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="dashicons dashicons-info !text-white opacity-50"></span>
                    <span class="font-bold text-sm">Lời khuyên từ AcmaTvirus</span>
                </div>
                <p class="text-xs leading-relaxed opacity-75">Bật Security Headers giúp bảo vệ website khỏi các cuộc tấn công phổ biến như clickjacking và XSS một cách chủ động.</p>
            </div>
        </div>
    </div>

    <div class="flex justify-end pt-8 border-t border-gray-100 items-center gap-4">
        <span class="text-xs text-gray-400 font-bold italic">Lưu thay đổi trước khi chuyển Tab</span>
        <input type="hidden" name="wps_save_settings" value="1">
        <button type="submit" class="bg-black text-white px-10 py-4 rounded-2xl font-bold text-sm hover:shadow-2xl transition-all active:scale-95">Lưu thiết lập Hệ thống</button>
    </div>
</form>
