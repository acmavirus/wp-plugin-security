                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('Quản lý cập nhật', 'acma-security-shield'); ?></h2>
                        <div class="wps-grid two">
                            <div class="wps-card">
        <h4><?php _e('Khóa cập nhật', 'acma-security-shield'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
        <?php $this->render_checkbox_row('block_core_updates', __('Chặn cập nhật Core', 'acma-security-shield'), $main_settings, __('Chặn kiểm tra/cập nhật WordPress core.', 'acma-security-shield')); ?>
        <?php $this->render_checkbox_row('block_plugin_updates', __('Chặn cập nhật Plugin', 'acma-security-shield'), $main_settings, __('Chặn cập nhật plugin.', 'acma-security-shield')); ?>
        <?php $this->render_checkbox_row('block_theme_updates', __('Chặn cập nhật Theme', 'acma-security-shield'), $main_settings, __('Chặn cập nhật theme.', 'acma-security-shield')); ?>
                                </table>
                            </div>
                            <div class="wps-card">
        <h4><?php _e('Kiểm tra bản phát hành', 'acma-security-shield'); ?></h4>
        <p><?php _e('Nhấn vào Kiểm tra cập nhật để so sánh phiên bản hiện tại với bản GitHub mới nhất.', 'acma-security-shield'); ?></p>
        <p><a href="#" class="button button-primary wps-check-update-btn" data-nonce="<?php echo esc_attr(wp_create_nonce('wps_check_update_nonce')); ?>"><?php _e('Kiểm tra cập nhật', 'acma-security-shield'); ?></a></p>
                            </div>
                        </div>

                        <input type="hidden" name="wps_save_settings" value="1">
        <?php submit_button(__('Lưu thiết lập Cập nhật', 'acma-security-shield')); ?>
                    </form>

                
