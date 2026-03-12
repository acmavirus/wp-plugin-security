<?php
// Copyright by AcmaTvirus
if (!defined('ABSPATH')) exit;
?>

<form method="post" action="" class="space-y-12">
    <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
        <div class="space-y-10">
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-2">
                    <h3 class="font-bold text-lg">IP Blacklist</h3>
                    <span class="text-[10px] font-bold text-red-500 bg-red-50 px-2 py-1 rounded-full uppercase">Restricted</span>
                </div>
                <p class="text-xs text-gray-400">Danh sách IP bị cấm truy cập hoàn toàn. Mỗi IP trên một dòng.</p>
                <textarea name="wps_blocked_ips_raw" rows="6" class="w-full bg-gray-50 border border-gray-100 rounded-[32px] p-6 text-sm font-mono focus:ring-0 focus:border-black transition-all" placeholder="0.0.0.0"><?php echo esc_textarea($ips_text); ?></textarea>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-2">
                    <h3 class="font-bold text-lg">IP Whitelist</h3>
                    <span class="text-[10px] font-bold text-green-500 bg-green-50 px-2 py-1 rounded-full uppercase">Trusted</span>
                </div>
                <p class="text-xs text-gray-400">IP tin cậy không bị khóa (ví dụ IP của bạn).</p>
                <textarea name="wps_whitelist_ips_raw" rows="4" class="w-full bg-gray-50 border border-gray-100 rounded-[32px] p-6 text-sm font-mono focus:ring-0 focus:border-black transition-all" placeholder="0.0.0.0"><?php echo esc_textarea($whitelist_text); ?></textarea>
            </div>

            <button type="submit" class="bg-black text-white px-8 py-4 rounded-2xl font-bold text-sm shadow-lg hover:shadow-2xl transition-all">Cập nhật danh sách</button>
        </div>

        <div class="space-y-6">
            <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                <span class="dashicons dashicons-shield-alt text-black"></span>
                <h3 class="font-bold text-lg uppercase text-[10px] tracking-widest text-gray-400">Auto-Blocked Log</h3>
            </div>
            <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                <?php
                $auto_blocked = array_filter($security_logs, fn($l) => in_array($l['type'] ?? '', ['ip_blocked', 'dangerous_request']));
                if (empty($auto_blocked)) : ?>
                    <div class="text-center py-20 bg-gray-50 rounded-[40px] border-2 border-dashed border-gray-200">
                        <span class="dashicons dashicons-shield-alt !text-4xl text-gray-200"></span>
                        <p class="mt-4 text-xs font-bold text-gray-400">Hệ thống đang an toàn.</p>
                    </div>
                    <?php else : foreach (array_slice($auto_blocked, 0, 15) as $log) : ?>
                        <div class="p-4 bg-white border border-gray-50 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-center mb-1">
                                <code class="text-xs font-bold text-black"><?php echo $log['ip'] ?? 'Unknown'; ?></code>
                                <span class="text-[9px] text-gray-400 font-bold"><?php echo isset($log['time']) ? date('H:i d/m', strtotime($log['time'])) : ''; ?></span>
                            </div>
                            <p class="text-[10px] text-gray-500 font-medium leading-relaxed"><?php echo $log['message'] ?? ''; ?></p>
                        </div>
                <?php endforeach;
                endif; ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="wps_save_settings" value="1">
</form>
