                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('Tường lửa & Tăng cứng', 'wp-plugin-security'); ?></h2>
                        <table class="form-table" role="presentation">
        <?php $this->render_checkbox_row('disable_xmlrpc', 'Vô hiệu hóa XML-RPC', $main_settings, 'Ngăn chặn brute-force qua XML-RPC.'); ?>
                            <?php $this->render_checkbox_row('disable_rest_api', 'Hạn chế REST API', $main_settings, 'Chỉ cho phép người dùng đã đăng nhập truy cập API.'); ?>
        <?php $this->render_checkbox_row('block_author_scan', 'Chặn quét tác giả', $main_settings, 'Ngăn bot dò tìm tên người dùng quản trị viên.'); ?>
                            <?php $this->render_checkbox_row('disable_directory_browsing', 'Chặn Directory Browsing', $main_settings, 'Ngăn truy cập liệt kê file trong thư mục.'); ?>
        <?php $this->render_checkbox_row('disable_file_editor', 'Tắt trình chỉnh sửa file', $main_settings, 'Vô hiệu hóa chỉnh sửa mã nguồn trong admin.'); ?>
                        </table>

                        <hr>

        <h2><?php _e('Quyền riêng tư & Nhật ký', 'wp-plugin-security'); ?></h2>
                        <table class="form-table" role="presentation">
        <?php $this->render_checkbox_row('hide_wp_version', 'Ẩn phiên bản WP', $main_settings, 'Xóa dấu hiệu nhận biết phiên bản từ mã nguồn.'); ?>
        <?php $this->render_checkbox_row('enable_security_headers', 'Tiêu đề bảo mật', $main_settings, 'Kích hoạt HSTS, XSS Protection, nosniff...'); ?>
        <?php $this->render_checkbox_row('enable_audit_log', 'Nhật ký kiểm tra', $main_settings, 'Lưu lại mọi hoạt động của người dùng.'); ?>
                        </table>

                        <input type="hidden" name="wps_save_settings" value="1">
                        <?php submit_button(__('Lưu thiết lập Hệ thống', 'wp-plugin-security')); ?>
                    </form>

                