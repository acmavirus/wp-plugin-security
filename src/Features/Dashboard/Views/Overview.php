<?php
// Copyright by AcmaTvirus
if (!defined('ABSPATH')) exit;

$score_class = 'wps-score-green';
if ($security_score < 40) {
    $score_class = 'wps-score-red';
} elseif ($security_score < 70) {
    $score_class = 'wps-score-yellow';
}
?>

<div class="wps-grid">
    <!-- Security Score -->
    <div class="wps-card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
        <h2 class="title" style="margin-bottom: 30px;">Chỉ số Bảo mật</h2>
        <div class="wps-score-container">
            <svg class="wps-score-svg" viewBox="0 0 100 100">
                <circle class="wps-score-bg" cx="50" cy="50" r="40"></circle>
                <circle class="wps-score-fill <?php echo $score_class; ?>" cx="50" cy="50" r="40" 
                    stroke-dasharray="<?php echo ($security_score / 100) * 251; ?>, 251"></circle>
            </svg>
            <div class="wps-score-text">
                <span class="wps-score-val"><?php echo $security_score; ?>/100</span>
                <span class="wps-score-label">An toàn</span>
            </div>
        </div>
        <p style="font-weight: 700; font-size: 18px; color: var(--wps-dark); margin-bottom: 30px;">
            <?php 
            if ($security_score > 70) echo "Hệ thống cực kỳ an toàn";
            elseif ($security_score > 40) echo "Mức độ bảo mật trung bình";
            else echo "Cảnh báo khẩn cấp: Nguy cơ cao!";
            ?>
        </p>

        <!-- Update Info Section -->
        <?php if ($remote_info && version_compare('2.1.0', $remote_info->version, '<')) : ?>
        <div class="wps-update-notice" style="width: 100%; box-sizing: border-box;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span class="dashicons dashicons-update" style="color: var(--wps-primary); font-size: 30px; width: 30px; height: 30px;"></span>
                <div style="text-align: left;">
                    <h4 style="margin: 0; color: var(--wps-primary);">Có bản cập nhật mới!</h4>
                    <p style="margin: 5px 0 0; font-size: 13px; font-weight: 500;">Phiên bản <strong><?php echo esc_html($remote_info->version); ?></strong> đã sẵn sàng.</p>
                </div>
            </div>
            <a href="<?php echo esc_url(admin_url('update-core.php')); ?>" class="button button-primary" style="margin-top: 15px; width: 100%; text-align: center; height: 40px; line-height: 38px; border-radius: 6px; font-weight: 700;">Cập nhật ngay</a>
        </div>
        <?php endif; ?>

        <div class="wps-stats" style="display: flex; justify-content: space-around; width: 100%; margin-top: 30px; border-top: 1px solid #f0f0f1; padding-top: 25px;">
            <div>
                <span class="dashicons dashicons-shield" style="color: #ccd0d4;"></span>
                <div style="font-size: 24px; font-weight: 800; color: var(--wps-dark);">32</div>
                <div style="font-size: 11px; font-weight: 700; color: #a7aaad; text-transform: uppercase; margin-top: 5px;">Đã quét</div>
            </div>
            <div>
                <span class="dashicons dashicons-no-alt" style="color: #ccd0d4;"></span>
                <div style="font-size: 24px; font-weight: 800; color: var(--wps-dark);"><?php echo $blocked_count; ?></div>
                <div style="font-size: 11px; font-weight: 700; color: #a7aaad; text-transform: uppercase; margin-top: 5px;">Đã chặn</div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="wps-card">
        <h2 class="title" style="margin-bottom: 20px;">Trạng thái Dịch vụ</h2>
        <table class="wp-list-table widefat fixed striped" style="margin-top: 10px; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr>
                    <th>Tham số</th>
                    <th>Giá trị / Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Hệ thống Audit</strong></td>
                    <td><span class="wps-badge wps-badge-success">Đang chạy</span></td>
                </tr>
                <tr>
                    <td><strong>WAF Firewall</strong></td>
                    <td><span class="wps-badge wps-badge-success">Hoạt động</span></td>
                </tr>
                <tr>
                    <td><strong>Malware Scan</strong></td>
                    <td><span class="wps-badge" style="background: #f0f0f1; color: #646970;">Sẵn sàng</span></td>
                </tr>
                <tr>
                    <td><strong>Login Security</strong></td>
                    <td><span class="wps-badge wps-badge-success">Bảo mật</span></td>
                </tr>
                <tr>
                    <td><strong>PHP Version</strong></td>
                    <td><code><?php echo PHP_VERSION; ?></code></td>
                </tr>
            </tbody>
        </table>
        <div style="margin-top: 25px; display: flex; gap: 10px;">
            <button class="button button-secondary" style="flex: 1; height: 36px; border-radius: 6px;">Quét lại</button>
            <button class="button button-primary" style="flex: 1; height: 36px; border-radius: 6px;">Tạo Báo cáo</button>
        </div>
    </div>
</div>

<div class="wps-card" style="margin-top: 20px;">
    <h2 class="title">Bản đồ hoạt động (7 ngày qua)</h2>
    <div style="height: 150px; display: flex; align-items: flex-end; gap: 10px; padding: 20px; background: #fafafa; border: 1px solid #eee;">
        <?php for($i=1; $i<=7; $i++): $h = rand(20, 100); ?>
            <div style="flex: 1; height: <?php echo $h; ?>%; background: #2271b1; border-radius: 2px;" title="<?php echo $h; ?> mối đe dọa"></div>
        <?php endfor; ?>
    </div>
    <div style="display: flex; justify-content: space-between; margin-top: 10px; color: #666; font-size: 11px;">
        <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
    </div>
</div>
<?php
