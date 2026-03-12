<?php
// Copyright by AcmaTvirus
if (!defined('ABSPATH')) exit;
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <!-- Kill Sessions -->
    <div class="glass-card rounded-[40px] p-8 flex flex-col justify-between hover:scale-[1.02] transition-transform">
        <div>
            <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center mb-6">
                <span class="dashicons dashicons-exit text-red-500"></span>
            </div>
            <h3 class="font-bold text-lg mb-2">Đăng xuất toàn bộ</h3>
            <p class="text-xs text-gray-400 leading-relaxed mb-8">Buộc tất cả người dùng (bao gồm cả bạn) phải đăng nhập lại. Hữu ích khi nghi ngờ có thiết bị lạ xâm nhập.</p>
        </div>
        <form method="post" action="">
            <?php wp_nonce_field('wps_tool_nonce_action', 'wps_tool_nonce'); ?>
            <input type="hidden" name="wps_tool_action" value="kill_sessions">
            <button type="submit" class="w-full py-4 bg-red-50 text-red-600 font-bold text-xs rounded-2xl hover:bg-red-600 hover:text-white transition-all uppercase tracking-widest">Thực hiện ngay</button>
        </form>
    </div>

    <!-- Force PW Reset -->
    <div class="glass-card rounded-[40px] p-8 flex flex-col justify-between hover:scale-[1.02] transition-transform">
        <div>
            <div class="w-12 h-12 rounded-2xl bg-orange-50 flex items-center justify-center mb-6">
                <span class="dashicons dashicons-lock text-orange-500"></span>
            </div>
            <h3 class="font-bold text-lg mb-2">Đặt lại mật khẩu</h3>
            <p class="text-xs text-gray-400 leading-relaxed mb-8">Vô hiệu hóa toàn bộ mật khẩu hiện tại. Tất cả người dùng sẽ nhận được yêu cầu đổi mật khẩu mới qua email.</p>
        </div>
        <form method="post" action="">
            <?php wp_nonce_field('wps_tool_nonce_action', 'wps_tool_nonce'); ?>
            <input type="hidden" name="wps_tool_action" value="force_pw_reset">
            <button type="submit" class="w-full py-4 bg-orange-50 text-orange-600 font-bold text-xs rounded-2xl hover:bg-orange-600 hover:text-white transition-all uppercase tracking-widest" onclick="return confirm('Hành động này không thể hoàn tác. Bạn có chắc chắn?')">Kích hoạt Force Reset</button>
        </form>
    </div>

    <!-- Clear Logs -->
    <div class="glass-card rounded-[40px] p-8 flex flex-col justify-between hover:scale-[1.02] transition-transform">
        <div>
            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mb-6">
                <span class="dashicons dashicons-trash text-gray-600"></span>
            </div>
            <h3 class="font-bold text-lg mb-2">Dọn dẹp Nhật ký</h3>
            <p class="text-xs text-gray-400 leading-relaxed mb-8">Xóa sạch toàn bộ Audit Trail và Security Logs để giảm dung lượng cơ sở dữ liệu.</p>
        </div>
        <form method="post" action="">
            <?php wp_nonce_field('wps_tool_nonce_action', 'wps_tool_nonce'); ?>
            <input type="hidden" name="wps_tool_action" value="clear_logs">
            <button type="submit" class="w-full py-4 bg-gray-100 text-gray-600 font-bold text-xs rounded-2xl hover:bg-black hover:text-white transition-all uppercase tracking-widest">Xóa Logs</button>
        </form>
    </div>
</div>

<div class="mt-12 p-8 dark-glass-card rounded-[40px] flex items-center justify-between">
    <div class="flex items-center gap-6">
        <div class="w-16 h-16 rounded-3xl bg-white/10 flex items-center justify-center">
            <span class="dashicons dashicons-shield-alt text-white !text-2xl"></span>
        </div>
        <div>
            <h4 class="font-bold text-white">Chế độ Khẩn cấp (Panic Button)</h4>
            <p class="text-xs text-gray-400 mt-1">Khóa toàn bộ truy cập vào website trừ IP của bạn trong 60 phút.</p>
        </div>
    </div>
    <button class="px-8 py-4 bg-white text-black font-bold text-xs rounded-2xl hover:bg-red-500 hover:text-white transition-all uppercase tracking-widest shadow-xl">Kích hoạt Panic</button>
</div>
