<?php
// Copyright by AcmaTvirus
if (!defined('ABSPATH')) exit;

$current_version = '2.1.0';
?>

<div class="wps-card">
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 30px;">
        <div>
            <h2 class="title" style="margin: 0;">Kiểm tra Phiên bản</h2>
            <p style="color: var(--wps-text-muted); margin-top: 5px;">Đảm bảo plugin luôn ở phiên bản mới nhất để bảo mật tốt nhất.</p>
        </div>
        <?php if ($remote_info && version_compare('2.1.0', $remote_info->version, '<')) : ?>
            <span class="wps-badge wps-badge-warning">Có bản cập nhật mới</span>
        <?php else : ?>
            <span class="wps-badge wps-badge-success">Đang ở bản mới nhất</span>
        <?php endif; ?>
    </div>
    
    <div class="wps-update-bar" style="margin-top: 0; margin-bottom: 30px;">
        <div class="wps-update-info">
            <div class="wps-update-icon">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--wps-text-muted); text-transform: uppercase;">Phiên bản hiện tại</div>
                <div style="font-size: 18px; font-weight: 800;">2.1.0</div>
            </div>
        </div>
        <div style="height: 30px; border-left: 1px solid #f1f5f9;"></div>
        <div class="wps-update-info">
            <div class="wps-update-icon" style="background: #ecfeff; color: #0891b2;">
                <span class="dashicons dashicons-cloud"></span>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--wps-text-muted); text-transform: uppercase;">Phiên bản mới nhất</div>
                <div style="font-size: 18px; font-weight: 800; color: var(--wps-primary);">
                    <?php echo $remote_info ? esc_html($remote_info->version) : '...'; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($remote_info && !empty($remote_info->body)) : ?>
    <div style="background: #f8fafc; border-radius: 12px; padding: 25px; border: 1px solid #f1f5f9;">
        <h4 style="margin: 0 0 15px 0; font-weight: 800; color: var(--wps-text);"><span class="dashicons dashicons-list-view"></span> Nhật ký thay đổi</h4>
        <div style="max-height: 250px; overflow-y: auto; font-size: 14px; line-height: 1.6; color: var(--wps-text-muted);">
            <?php echo nl2br(esc_html($remote_info->body)); ?>
        </div>
    </div>
    <?php endif; ?>

    <div style="margin-top: 40px; display: flex; gap: 15px;">
        <?php if ($remote_info && version_compare('2.1.0', $remote_info->version, '<')) : ?>
            <a href="<?php echo esc_url(admin_url('update-core.php')); ?>" class="wps-btn-primary" style="flex: 1; justify-content: center; height: 50px; font-size: 16px;">
                <span class="dashicons dashicons-download"></span> Nâng cấp ngay bây giờ
            </a>
        <?php else : ?>
            <button class="wps-btn-primary" style="flex: 1; justify-content: center; height: 50px; font-size: 16px; background: #94a3b8;" onclick="location.reload();">
                <span class="dashicons dashicons-update"></span> Kiểm tra lại phiên bản
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="notice notice-info inline" style="margin-top: 30px; border-radius: 12px; border-left: 4px solid var(--wps-primary);">
    <p>Hệ thống tự động đồng bộ với GitHub mỗi 12 giờ. Nếu bạn không thấy bản cập nhật mới, vui lòng thử lại sau vài phút.</p>
</div>
