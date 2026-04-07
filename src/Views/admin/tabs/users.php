                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('Người dùng', 'acma-security-shield'); ?></h2>
                        <div class="wps-grid two">
                            <div class="wps-card">
        <h4><?php _e('Cô lập', 'acma-security-shield'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
                                    <?php $this->render_checkbox_row('user_isolation_enabled', __('Bật cô lập', 'acma-security-shield'), $main_settings, __('Chặn user thường xem bài/media của người khác trong admin.', 'acma-security-shield')); ?>
                                </table>
                            </div>
                            <div class="wps-card">
        <h4><?php _e('Ảnh đại diện cục bộ', 'acma-security-shield'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
                                    <?php $this->render_checkbox_row('local_avatar_enabled', __('Bật ảnh đại diện cục bộ', 'acma-security-shield'), $main_settings, __('Cho phép lưu avatar trong media của site.', 'acma-security-shield')); ?>
                                </table>
                            </div>
                        </div>
                        <input type="hidden" name="wps_save_settings" value="1">
                        <?php submit_button(__('Lưu thiết lập Người dùng', 'acma-security-shield')); ?>
                    </form>

                
