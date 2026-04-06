                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('Người dùng', 'wp-plugin-security'); ?></h2>
                        <div class="wps-grid two">
                            <div class="wps-card">
        <h4><?php _e('Cô lập', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
                                    <?php $this->render_checkbox_row('user_isolation_enabled', 'Bật cô lập', $main_settings, 'Chặn user thường xem bài/media của người khác trong admin.'); ?>
                                </table>
                            </div>
                            <div class="wps-card">
        <h4><?php _e('Ảnh đại diện cục bộ', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
                                    <?php $this->render_checkbox_row('local_avatar_enabled', 'Bật ảnh đại diện cục bộ', $main_settings, 'Cho phép lưu avatar trong media của site.'); ?>
                                </table>
                            </div>
                        </div>
                        <input type="hidden" name="wps_save_settings" value="1">
                        <?php submit_button(__('Lưu thiết lập Người dùng', 'wp-plugin-security')); ?>
                    </form>

                