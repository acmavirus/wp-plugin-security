                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
                        <div class="card" style="max-width: 100%; margin-top: 0;">
        <h2><?php _e('Quản lý IP bị chặn', 'acma-security-shield'); ?></h2>
        <p class="description"><?php _e('Nhập mỗi địa chỉ IP trên một dòng.', 'acma-security-shield'); ?></p>
                            <textarea name="wps_blocked_ips_raw" rows="10" class="large-text code" style="width: 100%;"><?php echo esc_textarea($ips_text); ?></textarea>
                        </div>

                        <div class="card" style="max-width: 100%; margin-top: 20px;">
        <h2><?php _e('Nhật ký chặn tự động (Gần đây)', 'acma-security-shield'); ?></h2>
                            <table class="widefat fixed striped">
                                <thead>
                                    <tr>
        <th width="150"><?php _e('Thời gian', 'acma-security-shield'); ?></th>
        <th width="150"><?php _e('IP', 'acma-security-shield'); ?></th>
        <th><?php _e('Lý do', 'acma-security-shield'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $auto_blocked = array_filter($security_logs, function ($log) {
                                        return in_array($log['type'], ['ip_blocked', 'dangerous_request'], true);
                                    });
                                    if (empty($auto_blocked)) :
                                        ?>
                                        <tr>
        <td colspan="3"><?php _e('Chưa có IP bị chặn tự động.', 'acma-security-shield'); ?></td>
                                        </tr>
                                    <?php else : ?>
                                        <?php foreach (array_slice(array_reverse($auto_blocked), 0, 10) as $log) : ?>
                                            <tr>
                                                <td><?php echo esc_html(date('H:i d/m/Y', strtotime($log['time']))); ?></td>
                                                <td><code><?php echo esc_html($log['ip']); ?></code></td>
                                                <td><?php echo esc_html($log['message']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="wps_save_settings" value="1">
        <?php submit_button(__('Cập nhật Blacklist', 'acma-security-shield')); ?>
                    </form>

                
