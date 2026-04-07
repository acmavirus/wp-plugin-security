        <h2><?php _e('Công cụ bảo mật khẩn cấp', 'acma-security-shield'); ?></h2>
                    <div class="card" style="border-left: 4px solid #d63638;">
        <h3><?php _e('Ngắt toàn bộ phiên làm việc', 'acma-security-shield'); ?></h3>
        <p><?php _e('Đăng xuất tất cả session trên website, bao gồm cả tài khoản hiện tại.', 'acma-security-shield'); ?></p>
                        <form method="post" action="" onsubmit="return confirm('Tất cả session đăng nhập sẽ bị hủy. Tiếp tục?');">
                            <?php wp_nonce_field('wps_tool_nonce_action', 'wps_tool_nonce'); ?>
                            <input type="hidden" name="wps_tool_action" value="kill_sessions">
        <button type="submit" class="button button-link-delete" style="color: #d63638;"><?php _e('Kích hoạt Logout All', 'acma-security-shield'); ?></button>
                        </form>
                    </div>

                    <div class="card" style="border-left: 4px solid #d63638; margin-top: 20px;">
        <h3><?php _e('Đặt lại mật khẩu toàn website', 'acma-security-shield'); ?></h3>
        <p><?php _e('Đặt mật khẩu ngẫu nhiên mới cho tất cả tài khoản và hủy toàn bộ session hiện tại.', 'acma-security-shield'); ?></p>
                        <form method="post" action="" onsubmit="return confirm('CẢNH BÁO: Tất cả mật khẩu hiện tại sẽ bị vô hiệu hóa. Tiếp tục?');">
                            <?php wp_nonce_field('wps_tool_nonce_action', 'wps_tool_nonce'); ?>
                            <input type="hidden" name="wps_tool_action" value="force_pw_reset">
        <button type="submit" class="button button-link-delete" style="color: #d63638;"><?php _e('Kích hoạt đặt lại mật khẩu toàn diện', 'acma-security-shield'); ?></button>
                        </form>
                    </div>
                
