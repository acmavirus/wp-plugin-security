                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('Tường lửa & Tăng cứng', 'acma-security-shield'); ?></h2>
                        <table class="form-table" role="presentation">
        <?php $this->render_checkbox_row('disable_xmlrpc', __('Vô hiệu hóa XML-RPC', 'acma-security-shield'), $main_settings, __('Ngăn chặn brute-force qua XML-RPC.', 'acma-security-shield')); ?>
                            <?php $this->render_checkbox_row('disable_rest_api', __('Hạn chế REST API', 'acma-security-shield'), $main_settings, __('Chỉ cho phép người dùng đã đăng nhập truy cập API.', 'acma-security-shield')); ?>
        <?php $this->render_checkbox_row('block_author_scan', __('Chặn quét tác giả', 'acma-security-shield'), $main_settings, __('Ngăn bot dò tìm tên người dùng quản trị viên.', 'acma-security-shield')); ?>
                            <?php $this->render_checkbox_row('disable_directory_browsing', __('Chặn Directory Browsing', 'acma-security-shield'), $main_settings, __('Ngăn truy cập liệt kê file trong thư mục.', 'acma-security-shield')); ?>
        <?php $this->render_checkbox_row('disable_file_editor', __('Tắt trình chỉnh sửa file', 'acma-security-shield'), $main_settings, __('Vô hiệu hóa chỉnh sửa mã nguồn trong admin.', 'acma-security-shield')); ?>
                        </table>

                        <hr>

        <h2><?php _e('Quyền riêng tư & Nhật ký', 'acma-security-shield'); ?></h2>
                        <table class="form-table" role="presentation">
        <?php $this->render_checkbox_row('hide_wp_version', __('Ẩn phiên bản WP', 'acma-security-shield'), $main_settings, __('Xóa dấu hiệu nhận biết phiên bản từ mã nguồn.', 'acma-security-shield')); ?>
        <?php $this->render_checkbox_row('enable_security_headers', __('Tiêu đề bảo mật', 'acma-security-shield'), $main_settings, __('Kích hoạt HSTS, XSS Protection, nosniff...', 'acma-security-shield')); ?>
        <?php $this->render_checkbox_row('enable_audit_log', __('Nhật ký kiểm tra', 'acma-security-shield'), $main_settings, __('Lưu lại mọi hoạt động của người dùng.', 'acma-security-shield')); ?>
                        </table>

                        <input type="hidden" name="wps_save_settings" value="1">
                        <?php submit_button(__('Lưu thiết lập Hệ thống', 'acma-security-shield')); ?>
                    </form>

                
