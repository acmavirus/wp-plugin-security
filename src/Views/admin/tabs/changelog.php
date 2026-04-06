                    <div class="wps-section-head">
                        <div>
        <h3><?php _e('Changelog & Bản đồ tính năng', 'wp-plugin-security'); ?></h3>
        <p><?php _e('Tổng hợp các nhóm tính năng đã được ghi trong .agent/CHANGELOG.md và cách truy cập nhanh trong admin.', 'wp-plugin-security'); ?></p>
                        </div>
                    </div>

                    <div class="wps-grid two">
                        <div class="wps-card">
        <h4><?php _e('Bảo mật', 'wp-plugin-security'); ?></h4>
        <p><?php _e('Tắt XML-RPC, REST API, lọc upload file, ẩn version, SQL protection và cảnh báo request nguy hiểm.', 'wp-plugin-security'); ?></p>
                        </div>
                        <div class="wps-card">
        <h4><?php _e('SEO & Nội dung', 'wp-plugin-security'); ?></h4>
        <p><?php _e('TOC tự động, auto image saver, SEO URL rewrite, duplicate content, auto featured image.', 'wp-plugin-security'); ?></p>
                        </div>
                        <div class="wps-card">
        <h4><?php _e('Trình soạn thảo & Cập nhật', 'wp-plugin-security'); ?></h4>
        <p><?php _e('Trình soạn thảo cổ điển nâng cao, chặn cập nhật core/plugin/theme, anti copy, ẩn menu gọn hơn.', 'wp-plugin-security'); ?></p>
                        </div>
                        <div class="wps-card">
        <h4><?php _e('Google & Email', 'wp-plugin-security'); ?></h4>
        <p><?php _e('Google Indexing API, social login, reCAPTCHA, SMTP, thanh thông báo, cô lập người dùng, công cụ WooCommerce.', 'wp-plugin-security'); ?></p>
                        </div>
                    </div>

                    <div class="wps-card" style="margin-top: 18px;">
        <h4><?php _e('Thao tác nhanh', 'wp-plugin-security'); ?></h4>
        <p><?php _e('Dùng nút kiểm tra cập nhật nếu bạn muốn so sánh phiên bản hiện tại với bản GitHub mới nhất.', 'wp-plugin-security'); ?></p>
                        <p>
        <a href="#" class="button button-primary wps-check-update-btn" data-nonce="<?php echo esc_attr(wp_create_nonce('wps_check_update_nonce')); ?>"><?php _e('Kiểm tra cập nhật', 'wp-plugin-security'); ?></a>
                        </p>
                    </div>

                