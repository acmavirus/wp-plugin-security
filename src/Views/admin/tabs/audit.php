        <h2><?php _e('Lịch sử hoạt động (Audit Trail)', 'acma-security-shield'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
        <th width="150"><?php _e('Thời gian', 'acma-security-shield'); ?></th>
        <th width="150"><?php _e('Người dùng', 'acma-security-shield'); ?></th>
        <th width="120"><?php _e('Hành động', 'acma-security-shield'); ?></th>
        <th><?php _e('Chi tiết', 'acma-security-shield'); ?></th>
        <th width="150"><?php _e('Địa chỉ IP', 'acma-security-shield'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($audit_logs)) : ?>
                                <tr>
        <td colspan="5"><?php _e('Chưa có hoạt động nào được ghi lại.', 'acma-security-shield'); ?></td>
                                </tr>
                            <?php else : ?>
                                <?php foreach (array_reverse($audit_logs) as $log) : ?>
                                    <?php
                                    $action = strtolower($log['action'] ?? 'info');
                                    $color = '#64748b';
                                    if (strpos($action, 'login') !== false) {
                                        $color = '#10b981';
                                    }
                                    if (strpos($action, 'failed') !== false || strpos($action, 'blocked') !== false) {
                                        $color = '#ef4444';
                                    }
                                    ?>
                                    <tr>
                                        <td><small><?php echo esc_html(date('d/m/Y H:i:s', strtotime($log['time'] ?? 'now'))); ?></small></td>
        <td><strong><?php echo esc_html($log['user'] ?? __('Khách', 'acma-security-shield')); ?></strong></td>
                                        <td>
                                            <span style="background: <?php echo esc_attr($color); ?>; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase;">
                                                <?php echo esc_html($log['action'] ?? 'INFO'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html($log['message'] ?? ''); ?></td>
                                        <td><code><?php echo esc_html($log['ip'] ?? '0.0.0.0'); ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                
