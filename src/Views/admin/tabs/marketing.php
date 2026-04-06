                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('Marketing & Công cụ', 'wp-plugin-security'); ?></h2>
                        <div class="wps-grid two">
                            <div class="wps-card">
        <h4><?php _e('Thanh thông báo', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
        <?php $this->render_checkbox_row('notification_bar_enabled', 'Bật thanh thông báo', $main_settings, 'Hiển thị thanh thông báo trên/dưới toàn site.'); ?>
        <tr><th scope="row"><label for="notification_bar_position"><?php _e('Vị trí', 'wp-plugin-security'); ?></label></th><td><select id="notification_bar_position" name="notification_bar_position"><option value="top" <?php selected(($main_settings['notification_bar_position'] ?? 'top'), 'top'); ?>>Trên</option><option value="bottom" <?php selected(($main_settings['notification_bar_position'] ?? 'top'), 'bottom'); ?>>Dưới</option></select></td></tr>
        <tr><th scope="row"><label for="notification_bar_text"><?php _e('Nội dung', 'wp-plugin-security'); ?></label></th><td><textarea id="notification_bar_text" name="notification_bar_text" rows="4" class="large-text"><?php echo esc_textarea($main_settings['notification_bar_text'] ?? ''); ?></textarea></td></tr>
        <tr><th scope="row"><label for="notification_bar_link"><?php _e('Liên kết nút', 'wp-plugin-security'); ?></label></th><td><input type="url" id="notification_bar_link" name="notification_bar_link" value="<?php echo esc_attr($main_settings['notification_bar_link'] ?? ''); ?>" class="large-text"></td></tr>
        <tr><th scope="row"><label for="notification_bar_button"><?php _e('Chữ nút', 'wp-plugin-security'); ?></label></th><td><input type="text" id="notification_bar_button" name="notification_bar_button" value="<?php echo esc_attr($main_settings['notification_bar_button'] ?? ''); ?>" class="regular-text"></td></tr>
                                </table>
                            </div>
                            <div class="wps-card">
        <h4><?php _e('Bong bóng chat', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
        <?php $this->render_checkbox_row('chat_enabled', 'Bật chat', $main_settings, 'Hiển thị nhanh các nút liên hệ nổi.'); ?>
        <tr><th scope="row"><label for="chat_phone"><?php _e('Điện thoại', 'wp-plugin-security'); ?></label></th><td><input type="text" id="chat_phone" name="chat_phone" value="<?php echo esc_attr($main_settings['chat_phone'] ?? ''); ?>" class="regular-text"></td></tr>
                                    <tr><th scope="row"><label for="chat_sms"><?php _e('SMS', 'wp-plugin-security'); ?></label></th><td><input type="text" id="chat_sms" name="chat_sms" value="<?php echo esc_attr($main_settings['chat_sms'] ?? ''); ?>" class="regular-text"></td></tr>
                                    <tr><th scope="row"><label for="chat_zalo"><?php _e('Zalo', 'wp-plugin-security'); ?></label></th><td><input type="text" id="chat_zalo" name="chat_zalo" value="<?php echo esc_attr($main_settings['chat_zalo'] ?? ''); ?>" class="regular-text" placeholder="https://zalo.me/..."></td></tr>
                                    <tr><th scope="row"><label for="chat_messenger"><?php _e('Messenger', 'wp-plugin-security'); ?></label></th><td><input type="text" id="chat_messenger" name="chat_messenger" value="<?php echo esc_attr($main_settings['chat_messenger'] ?? ''); ?>" class="regular-text" placeholder="https://m.me/..."></td></tr>
                                    <tr><th scope="row"><label for="chat_telegram"><?php _e('Telegram', 'wp-plugin-security'); ?></label></th><td><input type="text" id="chat_telegram" name="chat_telegram" value="<?php echo esc_attr($main_settings['chat_telegram'] ?? ''); ?>" class="regular-text" placeholder="https://t.me/..."></td></tr>
                                    <tr><th scope="row"><label for="chat_whatsapp"><?php _e('WhatsApp', 'wp-plugin-security'); ?></label></th><td><input type="text" id="chat_whatsapp" name="chat_whatsapp" value="<?php echo esc_attr($main_settings['chat_whatsapp'] ?? ''); ?>" class="regular-text" placeholder="https://wa.me/..."></td></tr>
                                </table>
                            </div>
                        </div>

                        <div class="wps-grid two" style="margin-top:18px;">
                            <div class="wps-card">
        <h4><?php _e('Chèn mã', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
        <tr><th scope="row"><label for="code_inject_head"><?php _e('Mã đầu trang', 'wp-plugin-security'); ?></label></th><td><textarea id="code_inject_head" name="code_inject_head" rows="8" class="large-text code"><?php echo esc_textarea($main_settings['code_inject_head'] ?? ''); ?></textarea></td></tr>
        <tr><th scope="row"><label for="code_inject_footer"><?php _e('Mã chân trang', 'wp-plugin-security'); ?></label></th><td><textarea id="code_inject_footer" name="code_inject_footer" rows="8" class="large-text code"><?php echo esc_textarea($main_settings['code_inject_footer'] ?? ''); ?></textarea></td></tr>
                                </table>
                            </div>
                            <div class="wps-card">
        <h4><?php _e('Chuyển hướng & Tìm và thay thế', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
        <tr><th scope="row"><label for="redirect_rules"><?php _e('Quy tắc chuyển hướng', 'wp-plugin-security'); ?></label></th><td><textarea id="redirect_rules" name="redirect_rules" rows="8" class="large-text code" placeholder="/old|/new|301"><?php echo esc_textarea($main_settings['redirect_rules'] ?? ''); ?></textarea><p class="description"><?php _e('Mỗi dòng: from|to|301. from có thể là path tương đối.', 'wp-plugin-security'); ?></p></td></tr>
        <tr><th scope="row"><label for="search_replace_from"><?php _e('Tìm', 'wp-plugin-security'); ?></label></th><td><input type="text" id="search_replace_from" name="search_replace_from" class="regular-text"></td></tr>
        <tr><th scope="row"><label for="search_replace_to"><?php _e('Thay thế', 'wp-plugin-security'); ?></label></th><td><input type="text" id="search_replace_to" name="search_replace_to" class="regular-text"></td></tr>
                                </table>
                                <p>
        <button type="submit" name="wps_search_replace_action" value="1" class="button button-secondary" onclick="return confirm('<?php echo esc_js(__('Tìm và thay thế sẽ thay đổi nội dung trong bài viết, postmeta và options có liên quan. Tiếp tục?', 'wp-plugin-security')); ?>');"><?php _e('Chạy tìm và thay thế', 'wp-plugin-security'); ?></button>
                                </p>
                            </div>
                        </div>
                        <input type="hidden" name="wps_save_settings" value="1">
        <?php submit_button(__('Lưu thiết lập Marketing', 'wp-plugin-security')); ?>
                    </form>

                