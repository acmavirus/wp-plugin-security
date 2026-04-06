                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('SEO & Mục lục', 'wp-plugin-security'); ?></h2>
                        <div class="wps-grid two">
                            <div class="wps-card">
        <h4><?php _e('Mục lục tự động', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
        <?php $this->render_checkbox_row('enable_toc', 'Bật TOC', $main_settings, 'Tự động chèn mục lục vào bài viết/trang có heading.'); ?>
                                    <tr>
                <th scope="row"><label for="toc_title"><?php _e('Tiêu đề mục lục', 'wp-plugin-security'); ?></label></th>
                                        <td>
                <input type="text" id="toc_title" name="toc_title" value="<?php echo esc_attr($main_settings['toc_title'] ?? 'Mục lục'); ?>" class="regular-text">
                                        </td>
                                    </tr>
                                    <tr>
                <th scope="row"><?php _e('Loại bài viết', 'wp-plugin-security'); ?></th>
                                        <td>
                                            <?php
                                            $toc_types = (array) ($main_settings['toc_post_types'] ?? $this->get_toc_post_types());
                                            foreach ($this->get_toc_post_types() as $post_type) :
                                                $post_object = get_post_type_object($post_type);
                                                $label = $post_object && !empty($post_object->labels->singular_name) ? $post_object->labels->singular_name : $post_type;
                                                ?>
                                                <label><input type="checkbox" name="toc_post_types[]" value="<?php echo esc_attr($post_type); ?>" <?php checked(in_array($post_type, $toc_types, true)); ?>> <?php echo esc_html($label); ?></label><br>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="wps-card">
                                <h4><?php _e('Công cụ nội dung', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
                                    <?php $this->render_checkbox_row('auto_featured_image', 'Tự động ảnh đại diện', $main_settings, 'Tự lấy ảnh đầu tiên trong nội dung làm thumbnail nếu chưa có.'); ?>
                                </table>
                                <p class="description"><?php _e('Tinh nang TOC va thumbnail se duoc xu ly boi controller feature moi.', 'wp-plugin-security'); ?></p>
                            </div>

                        </div>

                        <input type="hidden" name="wps_save_settings" value="1">
        <?php submit_button(__('Lưu thiết lập SEO', 'wp-plugin-security')); ?>
                    </form>

                