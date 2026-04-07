                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('WooCommerce', 'acma-security-shield'); ?></h2>
                        <div class="wps-grid two">
                            <div class="wps-card">
        <h4><?php _e('Văn bản', 'acma-security-shield'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
        <tr><th scope="row"><label for="woo_add_to_cart_text"><?php _e('Chữ "Thêm vào giỏ"', 'acma-security-shield'); ?></label></th><td><input type="text" id="woo_add_to_cart_text" name="woo_add_to_cart_text" value="<?php echo esc_attr($main_settings['woo_add_to_cart_text'] ?? ''); ?>" class="regular-text" placeholder="<?php esc_attr_e('Thêm vào giỏ', 'acma-security-shield'); ?>"></td></tr>
        <tr><th scope="row"><label for="woo_price_zero_text"><?php _e('Chữ giá 0', 'acma-security-shield'); ?></label></th><td><input type="text" id="woo_price_zero_text" name="woo_price_zero_text" value="<?php echo esc_attr($main_settings['woo_price_zero_text'] ?? __('Liên hệ', 'acma-security-shield')); ?>" class="regular-text"></td></tr>
                                </table>
                            </div>
                            <div class="wps-card">
        <h4><?php _e('Cảnh báo Telegram', 'acma-security-shield'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
        <?php $this->render_checkbox_row('woo_telegram_enabled', __('Bật cảnh báo', 'acma-security-shield'), $main_settings, __('Gửi thông báo đơn hàng về Telegram bot.', 'acma-security-shield')); ?>
        <tr><th scope="row"><label for="woo_telegram_bot_token"><?php _e('Mã bot', 'acma-security-shield'); ?></label></th><td><input type="password" id="woo_telegram_bot_token" name="woo_telegram_bot_token" value="<?php echo esc_attr($main_settings['woo_telegram_bot_token'] ?? ''); ?>" class="regular-text"></td></tr>
        <tr><th scope="row"><label for="woo_telegram_chat_id"><?php _e('Chat ID', 'acma-security-shield'); ?></label></th><td><input type="text" id="woo_telegram_chat_id" name="woo_telegram_chat_id" value="<?php echo esc_attr($main_settings['woo_telegram_chat_id'] ?? ''); ?>" class="regular-text"></td></tr>
                                </table>
                            </div>
                        </div>
                        <input type="hidden" name="wps_save_settings" value="1">
        <?php submit_button(__('Lưu thiết lập WooCommerce', 'acma-security-shield')); ?>
                    </form>

                
