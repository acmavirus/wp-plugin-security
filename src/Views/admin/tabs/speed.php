                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
                        <h2><?php _e('Tốc độ & Tối ưu', 'acma-security-shield'); ?></h2>
                        <div class="wps-grid two">
                            <div class="wps-card">
                                <h4><?php _e('Cache & Delivery', 'acma-security-shield'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
                                    <?php $this->render_checkbox_row('disable_emojis', __('Tắt Emoji', 'acma-security-shield'), $main_settings, __('Tắt script và style emoji trên frontend/backend.', 'acma-security-shield')); ?>
                                    <?php $this->render_checkbox_row('disable_block_library_css', __('Tắt CSS Block', 'acma-security-shield'), $main_settings, __('Bỏ wp-block-library CSS không cần thiết trên frontend.', 'acma-security-shield')); ?>
                                    <?php $this->render_checkbox_row('disable_dashicons', __('Tắt Dashicons', 'acma-security-shield'), $main_settings, __('Vô hiệu hóa Dashicons cho visitor chưa đăng nhập.', 'acma-security-shield')); ?>
                                    <?php $this->render_checkbox_row('minify_html', __('Rút gọn HTML', 'acma-security-shield'), $main_settings, __('Gom khoảng trắng thừa trong HTML output.', 'acma-security-shield')); ?>
                                    <?php $this->render_checkbox_row('enable_browser_cache_headers', __('Browser Cache Headers', 'acma-security-shield'), $main_settings, __('Thêm Cache-Control và Vary cho tài nguyên frontend.', 'acma-security-shield')); ?>
                                    <?php $this->render_checkbox_row('defer_noncritical_js', __('Defer JavaScript', 'acma-security-shield'), $main_settings, __('Trì hoãn script không thiết yếu để tăng tốc hiển thị ban đầu.', 'acma-security-shield')); ?>
                                    <?php $this->render_checkbox_row('enable_preload_hints', __('Preload Hints', 'acma-security-shield'), $main_settings, __('In link preload cho font, ảnh banner hoặc CSS/JS quan trọng.', 'acma-security-shield')); ?>
                                    <tr>
                                        <th scope="row"><label for="cdn_url"><?php _e('CDN URL', 'acma-security-shield'); ?></label></th>
                                        <td>
                                            <input type="url" id="cdn_url" name="cdn_url" value="<?php echo esc_attr($main_settings['cdn_url'] ?? ''); ?>" class="regular-text" placeholder="https://cdn.example.com">
                                            <p class="description"><?php _e('Nếu có CDN, plugin sẹ rewrite URL của assets và media sang domain CDN này.', 'acma-security-shield'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="preload_assets"><?php _e('Preload assets', 'acma-security-shield'); ?></label></th>
                                        <td>
                                            <textarea id="preload_assets" name="preload_assets" rows="4" class="large-text code" placeholder="<?php esc_attr_e("https://example.com/wp-content/uploads/banner.webp\nhttps://example.com/wp-content/themes/theme/fonts/main.woff2", 'acma-security-shield'); ?>"><?php echo esc_textarea($main_settings['preload_assets'] ?? ''); ?></textarea>
                                            <p class="description"><?php _e('Mỗi dòng hoặc mỗi dấu phẩy là một URL preload. Hỗ trợ ảnh, font, CSS, JS.', 'acma-security-shield'); ?></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="wps-card">
                                <h4><?php _e('Media & Database', 'acma-security-shield'); ?></h4>
                                <p><?php _e('Lazy loading ảnh/iframe đã là mặc định của WordPress hiện đại. Phần này tập trung vào dọn rác và tối ưu dữ liệu.', 'acma-security-shield'); ?></p>
                                <p>
                                    <button type="submit" name="wps_maintenance_action" value="cleanup_autodrafts" class="button"><?php _e('Clean Auto Drafts', 'acma-security-shield'); ?></button>
                                    <button type="submit" name="wps_maintenance_action" value="cleanup_revisions" class="button"><?php _e('Clean Revisions', 'acma-security-shield'); ?></button>
                                    <button type="submit" name="wps_maintenance_action" value="cleanup_spam_comments" class="button"><?php _e('Clean Spam Comments', 'acma-security-shield'); ?></button>
                                    <button type="submit" name="wps_maintenance_action" value="cleanup_transients" class="button"><?php _e('Clean Transients', 'acma-security-shield'); ?></button>
                                    <button type="submit" name="wps_maintenance_action" value="optimize_database_tables" class="button button-primary"><?php _e('Optimize Database Tables', 'acma-security-shield'); ?></button>
                                </p>
                                <p class="description"><?php _e('Page cache và object cache phụ thuộc hạ tầng như LiteSpeed, Redis hoặc Memcached. Plugin này chỉ cung cấp lớp tối ưu an toàn ở mức ứng dụng.', 'acma-security-shield'); ?></p>
                            </div>
                        </div>

                        <input type="hidden" name="wps_save_settings" value="1">
                        <?php submit_button(__('Lưu thiết lập Tốc độ', 'acma-security-shield')); ?>
                    </form>

                
