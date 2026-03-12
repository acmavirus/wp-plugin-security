<?php
// Copyright by AcmaTvirus
if (!defined('ABSPATH')) exit;
?>

<form method="post" action="" class="space-y-12">
    <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
        <div class="space-y-8">
            <div class="bg-gray-50 rounded-[40px] p-8 border border-gray-100">
                <h3 class="font-bold text-lg mb-6 flex items-center gap-2">
                    <span class="dashicons dashicons-admin-links text-black"></span> 
                    Đổi đường dẫn đăng nhập
                </h3>
                <div class="space-y-4">
                    <label class="block text-sm font-bold text-gray-700">Đường dẫn mới</label>
                    <div class="flex items-center bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                        <span class="px-4 py-3 bg-gray-50 text-gray-400 text-xs font-mono border-r border-gray-200"><?php echo home_url('/'); ?></span>
                        <input type="text" name="rename_login_slug" value="<?php echo esc_attr($main_settings['rename_login_slug'] ?? ''); ?>" placeholder="secret-login" class="flex-grow px-4 py-3 text-sm focus:outline-none focus:ring-0 border-none">
                    </div>
                    <p class="text-[10px] text-gray-400 font-medium">Nếu để trống, plugin sẽ dùng <code>wp-login.php</code> mặc định.</p>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                    <span class="dashicons dashicons-shield-alt text-black"></span>
                    <h3 class="font-bold text-lg">Brute Force Protection</h3>
                </div>
                <div class="space-y-4">
                    <?php 
                    $login_protection = [
                        'limit_login_attempts' => ['Giới hạn đăng nhập', 'Khóa IP nếu đăng nhập sai nhiều lần.'],
                        'mask_login_errors' => ['Ẩn lỗi đăng nhập', 'Không cho biết chi tiết lỗi (username hay password sai).'],
                    ];
                    foreach($login_protection as $key => $info):
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

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Thử tối đa (lần)</label>
                        <input type="number" name="max_login_attempts" value="<?php echo esc_attr($main_settings['max_login_attempts'] ?? 5); ?>" class="w-full border-none focus:ring-0 text-lg font-bold p-0">
                    </div>
                    <div class="p-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Khóa (phút)</label>
                        <input type="number" name="lockout_duration" value="<?php echo esc_attr($main_settings['lockout_duration'] ?? 60); ?>" class="w-full border-none focus:ring-0 text-lg font-bold p-0">
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-10">
            <div class="space-y-6">
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                    <span class="dashicons dashicons-admin-users text-black"></span>
                    <h3 class="font-bold text-lg">Chính sách & Xác thực</h3>
                </div>
                <div class="space-y-4">
                    <?php 
                    $auth_policies = [
                        'enforce_strong_password' => ['Bắt buộc mật khẩu mạnh', 'Sử dụng 12 ký tự + ký tự đặc biệt.'],
                        'enable_2fa' => ['Xác thực 2 lớp (2FA)', 'Gửi mã xác thực qua email khi đăng nhập.'],
                    ];
                    foreach($auth_policies as $key => $info):
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

                <div class="p-8 bg-black text-white rounded-[40px] shadow-2xl relative overflow-hidden group">
                    <div class="absolute -right-8 -top-8 w-24 h-24 bg-white/5 rounded-full transition-transform group-hover:scale-150"></div>
                    <h4 class="font-bold text-sm mb-4">Google reCAPTCHA v3</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Site Key</label>
                            <input type="text" name="recaptcha_site_key" value="<?php echo esc_attr($main_settings['recaptcha_site_key'] ?? ''); ?>" placeholder="6Ld..." class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-xs focus:ring-0 focus:border-white/30 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Secret Key</label>
                            <input type="password" name="recaptcha_secret_key" value="<?php echo esc_attr($main_settings['recaptcha_secret_key'] ?? ''); ?>" placeholder="***" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-xs focus:ring-0 focus:border-white/30 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-yellow-50 border border-yellow-100 rounded-3xl">
                <div class="flex items-center gap-3 mb-2">
                    <span class="dashicons dashicons-warning text-yellow-600"></span>
                    <h5 class="font-bold text-yellow-900 text-sm">Chú ý bảo mật</h5>
                </div>
                <p class="text-xs text-yellow-800 leading-relaxed">Sử dụng tính năng <strong>Đổi đường dẫn đăng nhập</strong> là cách hiệu quả nhất để ngăn chặn các cuộc tấn công Brute Force tự động từ Bot.</p>
            </div>
        </div>
    </div>

    <div class="flex justify-end pt-8 border-t border-gray-100 items-center gap-4">
        <span class="text-xs text-gray-400 font-bold italic">Lưu thay đổi trước khi chuyển Tab</span>
        <input type="hidden" name="wps_save_settings" value="1">
        <button type="submit" class="bg-black text-white px-10 py-4 rounded-2xl font-bold text-sm hover:shadow-2xl transition-all active:scale-95">Lưu thiết lập Đăng nhập</button>
    </div>
</form>
