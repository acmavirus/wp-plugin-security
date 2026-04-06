                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('Quản lý cập nhật', 'wp-plugin-security'); ?></h2>
                        <div class="wps-grid two">
                            <div class="wps-card">
        <h4><?php _e('Khóa cập nhật', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
        <?php $this->render_checkbox_row('block_core_updates', 'Chặn cập nhật Core', $main_settings, 'Chặn kiểm tra/cập nhật WordPress core.'); ?>
        <?php $this->render_checkbox_row('block_plugin_updates', 'Chặn cập nhật Plugin', $main_settings, 'Chặn cập nhật plugin.'); ?>
        <?php $this->render_checkbox_row('block_theme_updates', 'Chặn cập nhật Theme', $main_settings, 'Chặn cập nhật theme.'); ?>
                                </table>
                            </div>
                            <div class="wps-card">
        <h4><?php _e('Kiểm tra bản phát hành', 'wp-plugin-security'); ?></h4>
        <p><?php _e('Nhấn vào Kiểm tra cập nhật để so sánh phiên bản hiện tại với bản GitHub mới nhất.', 'wp-plugin-security'); ?></p>
        <p><a href="#" class="button button-primary wps-check-update-btn" data-nonce="<?php echo esc_attr(wp_create_nonce('wps_check_update_nonce')); ?>"><?php _e('Kiểm tra cập nhật', 'wp-plugin-security'); ?></a></p>
                            </div>
                        </div>

                        <input type="hidden" name="wps_save_settings" value="1">
        <?php submit_button(__('Lưu thiết lập Cập nhật', 'wp-plugin-security')); ?>
                    </form>

                