<?php
// Copyright by AcmaTvirus
if (!defined('ABSPATH')) exit;
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-12">
    <div class="space-y-8">
        <div class="bg-gray-50 rounded-[40px] p-8 border border-gray-100 relative overflow-hidden group">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-red-400/5 rounded-full transition-transform group-hover:scale-150"></div>
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                    <span class="dashicons dashicons-search text-red-500 !text-sm"></span>
                </div>
                <h3 class="font-bold">Malware Scanner</h3>
            </div>
            <div class="space-y-3">
                <?php if (empty($malware_files)) : ?>
                    <div class="flex items-center gap-3 p-6 bg-green-50 border border-green-100 rounded-[32px]">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                            <span class="dashicons dashicons-yes text-green-600"></span>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-green-700 block">Hệ thống sạch</span>
                            <span class="text-[10px] text-green-600/70 font-medium">Không tìm thấy mã độc trong thư mục uploads.</span>
                        </div>
                    </div>
                <?php else : foreach ($malware_files as $file) : ?>
                    <div class="p-4 bg-white rounded-2xl border border-red-50 flex justify-between items-center group/item hover:border-red-200 transition-all shadow-sm">
                        <div class="overflow-hidden">
                            <code class="text-[10px] text-red-500 font-bold block truncate"><?php echo $file['path']; ?></code>
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest"><?php echo $file['time']; ?></span>
                        </div>
                        <span class="bg-red-50 text-red-500 text-[9px] font-black px-2 py-1 rounded-lg"><?php echo $file['size']; ?></span>
                    </div>
                <?php endforeach; endif; ?>
            </div>
            <button class="w-full mt-6 bg-white border border-gray-200 py-3 rounded-2xl text-[10px] font-bold uppercase tracking-widest hover:bg-gray-50 transition-all">Chạy quét sâu (Root Scan)</button>
        </div>

        <div class="bg-gray-50 rounded-[40px] p-8 border border-gray-100 relative group overflow-hidden">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-blue-400/5 rounded-full transition-transform group-hover:scale-150"></div>
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                    <span class="dashicons dashicons-media-text text-blue-500 !text-sm"></span>
                </div>
                <h3 class="font-bold">File Integrity</h3>
            </div>
            <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2">
                <?php if (empty($integrity_changes)) : ?>
                    <div class="text-[10px] text-gray-400 font-bold text-center py-16 uppercase tracking-widest">Không có thay đổi trong 24h qua.</div>
                <?php else : foreach ($integrity_changes as $change) : ?>
                    <div class="flex justify-between items-center p-4 bg-white rounded-2xl shadow-sm border border-transparent hover:border-blue-100 transition-all">
                        <span class="text-[11px] font-bold text-black"><?php echo esc_html($change['file']); ?></span>
                        <span class="text-[9px] text-gray-400 font-bold"><?php echo $change['time']; ?></span>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <div class="space-y-8">
        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <span class="dashicons dashicons-admin-users text-black"></span>
                <h3 class="font-bold text-lg">Active Sessions</h3>
            </div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"><?php echo count($sessions); ?> active</span>
        </div>
        <div class="space-y-4">
            <?php foreach ($sessions as $verifier => $session) : 
                $is_current = (wp_get_session_token() === $verifier);
            ?>
                <div class="p-6 <?php echo $is_current ? 'bg-black text-white shadow-2xl scale-[1.02]' : 'bg-gray-50 text-gray-900 border border-gray-100'; ?> rounded-[32px] flex items-center gap-4 transition-all hover:shadow-lg">
                    <div class="w-12 h-12 rounded-2xl <?php echo $is_current ? 'bg-white/10' : 'bg-white shadow-sm'; ?> flex items-center justify-center">
                        <span class="dashicons <?php echo strpos(strtolower($session['ua'] ?? ''), 'mobile') !== false ? 'dashicons-smartphone' : 'dashicons-desktop'; ?> <?php echo $is_current ? 'text-white' : 'text-gray-400'; ?>"></span>
                    </div>
                    <div class="flex-grow min-w-0">
                        <h4 class="font-bold text-xs truncate opacity-90"><?php echo esc_html($session['ua'] ?? 'Unknown Browser'); ?></h4>
                        <div class="text-[9px] <?php echo $is_current ? 'text-gray-400' : 'text-gray-400'; ?> mt-1 font-bold">
                            IP: <?php echo $session['ip']; ?> <span class="mx-1">•</span> <?php echo date('H:i d/m/Y', $session['login']); ?>
                        </div>
                    </div>
                    <?php if ($is_current) : ?>
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                            <span class="text-[8px] font-black uppercase text-green-400">You</span>
                        </div>
                    <?php else: ?>
                        <button class="p-2 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-lg transition-colors">
                            <span class="dashicons dashicons-no-alt !text-sm"></span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="p-8 dark-glass-card rounded-[40px] mt-12">
            <h4 class="font-bold text-sm mb-2">Lời khuyên giám sát</h4>
            <p class="text-xs text-gray-400 leading-relaxed font-medium">Kiểm tra danh sách phiên làm việc thường xuyên để đảm bảo không có thiết bị lạ đang truy cập tài khoản của bạn.</p>
        </div>
    </div>
</div>
