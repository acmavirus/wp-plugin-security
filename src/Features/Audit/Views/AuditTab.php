<?php
// Copyright by AcmaTvirus
if (!defined('ABSPATH')) exit;
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h3 class="font-bold text-2xl">Audit Trail</h3>
        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Hành vi hệ thống trong 30 ngày qua</p>
    </div>
    <div class="bg-black text-white text-[10px] font-bold px-4 py-2 rounded-full uppercase shadow-xl"><?php echo count($audit_logs); ?> entries found</div>
</div>

<div class="overflow-x-auto">
    <table class="w-full text-left border-separate border-spacing-y-3">
        <thead>
            <tr class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                <th class="px-6 py-4">Timestamp</th>
                <th class="px-6 py-4">User</th>
                <th class="px-6 py-4">Action</th>
                <th class="px-6 py-4">Activity Details</th>
                <th class="px-6 py-4">IP Source</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($audit_logs)) : ?>
                <tr>
                    <td colspan="5" class="py-20 text-center text-gray-400 font-bold italic">No log entries recorded yet.</td>
                </tr>
            <?php else : foreach (array_reverse(array_slice($audit_logs, -50)) as $log) :
                    $label_color = 'text-gray-400 bg-gray-100';
                    $action_type = strtolower($log['action'] ?? '');
                    if (strpos($action_type, 'login') !== false) {
                        $label_color = 'text-green-600 bg-green-50';
                    } elseif (strpos($action_type, 'security') !== false) {
                        $label_color = 'text-red-500 bg-red-50';
                    }
            ?>
                    <tr class="bg-gray-50/50 hover:bg-gray-50 transition-colors group">
                        <td class="px-6 py-4 rounded-l-3xl">
                            <div class="text-xs font-bold text-black"><?php echo date('H:i:s', strtotime($log['time'] ?? 'now')); ?></div>
                            <div class="text-[10px] text-gray-400"><?php echo date('d/m/Y', strtotime($log['time'] ?? 'now')); ?></div>
                        </td>
                        <td class="px-6 py-4 font-bold text-xs"><?php echo esc_html($log['user'] ?? 'Guest'); ?></td>
                        <td class="px-6 py-4">
                            <span class="text-[9px] font-black uppercase px-2 py-1 rounded-lg <?php echo $label_color; ?>">
                                <?php echo esc_html($log['action'] ?? 'INFO'); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500"><?php echo esc_html($log['message'] ?? ''); ?></td>
                        <td class="px-6 py-4 rounded-r-3xl">
                            <code class="text-[10px] font-bold text-black underline decoration-gray-200"><?php echo esc_html($log['ip'] ?? '0.0.0.0'); ?></code>
                        </td>
                    </tr>
            <?php endforeach;
            endif; ?>
        </tbody>
    </table>
</div>
