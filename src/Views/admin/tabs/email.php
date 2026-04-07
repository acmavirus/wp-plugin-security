                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('Email & Thông báo', 'acma-security-shield'); ?></h2>
                        <div class="wps-grid two">
                            <div class="wps-card">
        <h4><?php _e('SMTP', 'acma-security-shield'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
                                    <?php $this->render_checkbox_row('smtp_enabled', __('Bật SMTP', 'acma-security-shield'), $main_settings, __('Ghi đè wp_mail bằng SMTP server bên ngoài.', 'acma-security-shield')); ?>
        <tr><th scope="row"><label for="smtp_host"><?php _e('Máy chủ SMTP', 'acma-security-shield'); ?></label></th><td><input type="text" id="smtp_host" name="smtp_host" value="<?php echo esc_attr($main_settings['smtp_host'] ?? ''); ?>" class="regular-text"></td></tr>
        <tr><th scope="row"><label for="smtp_port"><?php _e('Cổng SMTP', 'acma-security-shield'); ?></label></th><td><input type="number" id="smtp_port" name="smtp_port" value="<?php echo esc_attr($main_settings['smtp_port'] ?? 587); ?>" class="small-text"></td></tr>
        <tr><th scope="row"><label for="smtp_encryption"><?php _e('Mã hóa', 'acma-security-shield'); ?></label></th><td><select id="smtp_encryption" name="smtp_encryption"><option value="tls" <?php selected(($main_settings['smtp_encryption'] ?? 'tls'), 'tls'); ?>><?php _e('TLS', 'acma-security-shield'); ?></option><option value="ssl" <?php selected(($main_settings['smtp_encryption'] ?? 'tls'), 'ssl'); ?>><?php _e('SSL', 'acma-security-shield'); ?></option><option value="none" <?php selected(($main_settings['smtp_encryption'] ?? 'tls'), 'none'); ?>><?php _e('Không', 'acma-security-shield'); ?></option></select></td></tr>
        <tr><th scope="row"><label for="smtp_username"><?php _e('Tên đăng nhập', 'acma-security-shield'); ?></label></th><td><input type="text" id="smtp_username" name="smtp_username" value="<?php echo esc_attr($main_settings['smtp_username'] ?? ''); ?>" class="regular-text"></td></tr>
        <tr><th scope="row"><label for="smtp_password"><?php _e('Mật khẩu', 'acma-security-shield'); ?></label></th><td><input type="password" id="smtp_password" name="smtp_password" value="<?php echo esc_attr($main_settings['smtp_password'] ?? ''); ?>" class="regular-text"></td></tr>
        <tr><th scope="row"><label for="smtp_from_email"><?php _e('Email gửi', 'acma-security-shield'); ?></label></th><td><input type="email" id="smtp_from_email" name="smtp_from_email" value="<?php echo esc_attr($main_settings['smtp_from_email'] ?? ''); ?>" class="regular-text"></td></tr>
        <tr><th scope="row"><label for="smtp_from_name"><?php _e('Tên gửi', 'acma-security-shield'); ?></label></th><td><input type="text" id="smtp_from_name" name="smtp_from_name" value="<?php echo esc_attr($main_settings['smtp_from_name'] ?? ''); ?>" class="regular-text"></td></tr>
                                </table>
                            </div>
                            <div class="wps-card">
        <h4><?php _e('Thanh thông báo', 'acma-security-shield'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
        <?php $this->render_checkbox_row('notification_bar_enabled', __('Bật thanh thông báo', 'acma-security-shield'), $main_settings, __('Hiển thị thanh thông báo trên/dưới toàn site.', 'acma-security-shield')); ?>
        <tr><th scope="row"><label for="notification_bar_position"><?php _e('Vị trí', 'acma-security-shield'); ?></label></th><td><select id="notification_bar_position" name="notification_bar_position"><option value="top" <?php selected(($main_settings['notification_bar_position'] ?? 'top'), 'top'); ?>><?php _e('Trên', 'acma-security-shield'); ?></option><option value="bottom" <?php selected(($main_settings['notification_bar_position'] ?? 'top'), 'bottom'); ?>><?php _e('Dưới', 'acma-security-shield'); ?></option></select></td></tr>
        <tr><th scope="row"><label for="notification_bar_text"><?php _e('Nội dung', 'acma-security-shield'); ?></label></th><td><textarea id="notification_bar_text" name="notification_bar_text" rows="4" class="large-text"><?php echo esc_textarea($main_settings['notification_bar_text'] ?? ''); ?></textarea></td></tr>
        <tr><th scope="row"><label for="notification_bar_link"><?php _e('Liên kết nút', 'acma-security-shield'); ?></label></th><td><input type="url" id="notification_bar_link" name="notification_bar_link" value="<?php echo esc_attr($main_settings['notification_bar_link'] ?? ''); ?>" class="large-text"></td></tr>
        <tr><th scope="row"><label for="notification_bar_button"><?php _e('Chữ nút', 'acma-security-shield'); ?></label></th><td><input type="text" id="notification_bar_button" name="notification_bar_button" value="<?php echo esc_attr($main_settings['notification_bar_button'] ?? ''); ?>" class="regular-text"></td></tr>
                                </table>
                            </div>
                        </div>
                        <input type="hidden" name="wps_save_settings" value="1">
        <?php submit_button(__('Lưu thiết lập Email', 'acma-security-shield')); ?>
                    </form>

                
