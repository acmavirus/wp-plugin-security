                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
                        <h2><?php _e('Rename Login', 'acma-security-shield'); ?></h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="rename_login_slug"><?php _e('Đường dẫn đăng nhập mới', 'acma-security-shield'); ?></label></th>
                                <td>
                                    <code><?php echo esc_html(home_url('/')); ?></code>
                <input type="text" id="rename_login_slug" name="rename_login_slug" value="<?php echo esc_attr($main_settings['rename_login_slug'] ?? ''); ?>" placeholder="<?php esc_attr_e('ví dụ: secret-login', 'acma-security-shield'); ?>" class="regular-text">
                                    <p class="description"><?php _e('Nếu để trống, plugin sẽ dùng wp-login.php mặc định.', 'acma-security-shield'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <hr>

                        <h2><?php _e('Brute Force Protection', 'acma-security-shield'); ?></h2>
                        <table class="form-table" role="presentation">
        <?php $this->render_checkbox_row('limit_login_attempts', __('Giới hạn đăng nhập', 'acma-security-shield'), $main_settings, __('Khóa IP nếu đăng nhập sai nhiều lần.', 'acma-security-shield')); ?>
        <?php $this->render_checkbox_row('mask_login_errors', __('Ẩn lỗi đăng nhập', 'acma-security-shield'), $main_settings, __('Không cho biết tên người dùng hay mật khẩu sai.', 'acma-security-shield')); ?>
        <?php $this->render_number_row('max_login_attempts', __('Số lần thử tối đa', 'acma-security-shield'), $main_settings, 5); ?>
        <?php $this->render_number_row('lockout_duration', __('Thời gian khóa (phút)', 'acma-security-shield'), $main_settings, 60); ?>
                        </table>

                        <hr>

                        <h2><?php _e('Chính sách người dùng', 'acma-security-shield'); ?></h2>
                        <table class="form-table" role="presentation">
        <?php $this->render_checkbox_row('enforce_strong_password', __('Mật khẩu mạnh', 'acma-security-shield'), $main_settings, __('Bắt buộc dùng mật khẩu mạnh với ít nhất 12 ký tự.', 'acma-security-shield')); ?>
                            <?php $this->render_number_row('idle_logout_time', __('Tự động đăng xuất (phút)', 'acma-security-shield'), $main_settings, 0, __('0 để tắt chức năng này.', 'acma-security-shield')); ?>
                        </table>

                        <hr>

                        <h2><?php _e('Xác thực 2 lớp', 'acma-security-shield'); ?></h2>
                        <table class="form-table" role="presentation">
                            <?php $this->render_checkbox_row('enable_two_factor', __('Bật 2FA', 'acma-security-shield'), $main_settings, __('Hiển thị ô mã xác thực 2FA trên form đăng nhập.', 'acma-security-shield')); ?>
                            <tr>
                                <th scope="row"><?php _e('Vai trò bắt buộc', 'acma-security-shield'); ?></th>
                                <td>
                                    <?php
                                    $required_roles = (array) ($main_settings['two_factor_required_roles'] ?? ['administrator']);
                                    $roles = wp_roles()->roles;
                                    foreach (['administrator', 'editor', 'author', 'contributor', 'subscriber'] as $role_key) :
                                        $role_label = $roles[$role_key]['name'] ?? ucfirst($role_key);
                                        ?>
                                        <label><input type="checkbox" name="two_factor_required_roles[]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $required_roles, true)); ?>> <?php echo esc_html($role_label); ?></label><br>
                                    <?php endforeach; ?>
                                    <p class="description"><?php _e('User trong các vai trò này sẽ phải nhập mã 2FA khi đăng nhập.', 'acma-security-shield'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <input type="hidden" name="wps_save_settings" value="1">
                        <?php submit_button(__('Lưu thiết lập Đăng nhập', 'acma-security-shield')); ?>
                    </form>

                                
