                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('Trình soạn thảo cổ điển & TinyMCE', 'wp-plugin-security'); ?></h2>
                        <div class="wps-grid two">
                            <div class="wps-card">
        <h4><?php _e('Chế độ soạn thảo', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
        <?php $this->render_checkbox_row('disable_block_editor', 'Tắt Block Editor', $main_settings, 'Ép dùng Gutenberg cho tất cả loại bài viết.'); ?>
        <?php $this->render_checkbox_row('enable_tinymce_advanced', 'TinyMCE nâng cao', $main_settings, 'Mở rộng các nút font, code, màu sắc và table.'); ?>
                                </table>
                            </div>
                            <div class="wps-card">
        <h4><?php _e('Ghi chú', 'wp-plugin-security'); ?></h4>
        <p><?php _e('Các nút TinyMCE được controller mới thêm vào editor khi tùy chọn này được bật.', 'wp-plugin-security'); ?></p>
                            </div>
                        </div>

                        <input type="hidden" name="wps_save_settings" value="1">
        <?php submit_button(__('Lưu thiết lập Editor', 'wp-plugin-security')); ?>
                    </form>

                