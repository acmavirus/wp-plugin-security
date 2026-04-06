                    <form method="post" action="">
                        <?php wp_nonce_field('wps_settings_action', 'wps_settings_nonce'); ?>
        <h2><?php _e('SEO AI + Rank Math', 'wp-plugin-security'); ?></h2>
                        <div class="wps-grid two">
                            <div class="wps-card">
                                <h4><?php _e('Tự động tối ưu', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
                                    <?php $this->render_checkbox_row('seo_ai_enabled', 'Bật SEO AI', $main_settings, 'Tự sinh focus keyword, SEO title và meta description cho Rank Math khi lưu bài.'); ?>
                                    <?php $this->render_checkbox_row('seo_ai_sync_rank_math', 'Đồng bộ Rank Math', $main_settings, 'Ghi meta vào các field của Rank Math để plugin này đọc trực tiếp.'); ?>
                                    <tr>
                                        <th scope="row"><label for="seo_ai_brand_name"><?php _e('Tên thương hiệu', 'wp-plugin-security'); ?></label></th>
                                        <td><input type="text" id="seo_ai_brand_name" name="seo_ai_brand_name" value="<?php echo esc_attr($main_settings['seo_ai_brand_name'] ?? get_bloginfo('name')); ?>" class="regular-text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e('Loại bài viết', 'wp-plugin-security'); ?></th>
                                        <td>
                                            <?php
                                            $seo_ai_types = (array) ($main_settings['seo_ai_post_types'] ?? $this->get_seo_ai_post_types());
                                            foreach ($this->get_seo_ai_post_types() as $post_type) :
                                                $post_object = get_post_type_object($post_type);
                                                $label = $post_object && !empty($post_object->labels->singular_name) ? $post_object->labels->singular_name : $post_type;
                                                ?>
                                                <label><input type="checkbox" name="seo_ai_post_types[]" value="<?php echo esc_attr($post_type); ?>" <?php checked(in_array($post_type, $seo_ai_types, true)); ?>> <?php echo esc_html($label); ?></label><br>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="wps-card">
                                <h4><?php _e('Cách hoạt động', 'wp-plugin-security'); ?></h4>
                                <p><?php _e('Khi lưu bài viết, module sẽ tạo focus keyword, SEO title, meta description và đồng bộ vào meta của Rank Math.', 'wp-plugin-security'); ?></p>
                                <p><?php _e('Điểm SEO AI ước tính chỉ là checklist nội bộ. Điểm thật của Rank Math vẫn phụ thuộc vào nội dung và test của plugin đó.', 'wp-plugin-security'); ?></p>
                            </div>
                            <div class="wps-card">
                                <h4><?php _e('Gemini API', 'wp-plugin-security'); ?></h4>
                                <table class="form-table wps-form-table" role="presentation">
                                    <?php $this->render_checkbox_row('seo_ai_use_gemini', 'Dùng Gemini', $main_settings, 'Gọi Gemini để sinh title và description theo prompt thật.'); ?>
                                    <tr>
                                        <th scope="row"><label for="seo_ai_gemini_api_key"><?php _e('Gemini API key', 'wp-plugin-security'); ?></label></th>
                                        <td><input type="password" id="seo_ai_gemini_api_key" name="seo_ai_gemini_api_key" value="<?php echo esc_attr($main_settings['seo_ai_gemini_api_key'] ?? ''); ?>" class="regular-text" autocomplete="off"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="seo_ai_gemini_model"><?php _e('Model', 'wp-plugin-security'); ?></label></th>
                                        <td><input type="text" id="seo_ai_gemini_model" name="seo_ai_gemini_model" value="<?php echo esc_attr($main_settings['seo_ai_gemini_model'] ?? 'gemini-2.5-flash'); ?>" class="regular-text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="seo_ai_gemini_temperature"><?php _e('Temperature', 'wp-plugin-security'); ?></label></th>
                                        <td><input type="number" step="0.1" min="0" max="2" id="seo_ai_gemini_temperature" name="seo_ai_gemini_temperature" value="<?php echo esc_attr($main_settings['seo_ai_gemini_temperature'] ?? 0.4); ?>" class="small-text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="seo_ai_gemini_prompt"><?php _e('Prompt mẫu', 'wp-plugin-security'); ?></label></th>
                                        <td>
                                            <textarea id="seo_ai_gemini_prompt" name="seo_ai_gemini_prompt" rows="8" class="large-text code"><?php echo esc_textarea($main_settings['seo_ai_gemini_prompt'] ?? ''); ?></textarea>
                                            <p class="description"><?php _e('Để trống để dùng prompt tối ưu SEO mặc định của plugin. Hỗ trợ placeholder {title}, {content}, {brand}, {post_type}.', 'wp-plugin-security'); ?></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                <div class="wps-card" style="margin-top: 20px;">
                    <h4><?php _e('Quét bài viết hiện có', 'wp-plugin-security'); ?></h4>
                    <p class="description"><?php _e('Chạy quét hàng loạt để tạo SEO title, description và Rank Math meta cho các bài viết đã tồn tại.', 'wp-plugin-security'); ?></p>
                            <p>
                                <button type="button" class="button button-secondary" id="wps-seo-ai-bulk-scan" data-nonce="<?php echo esc_attr(wp_create_nonce('wps_seo_ai_bulk_scan')); ?>"><?php _e('Quét và tối ưu toàn bộ bài viết', 'wp-plugin-security'); ?></button>
                                <span id="wps-seo-ai-bulk-status" class="description" style="margin-left: 12px;"></span>
                            </p>
                            <div style="margin-top: 14px;">
                                <div style="height: 16px; border-radius: 999px; background: #e6edf3; overflow: hidden; box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08);">
                                    <div id="wps-seo-ai-bulk-progress-bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #1167ad 0%, #00a3c4 100%); transition: width 180ms ease;"></div>
                                </div>
                                <div id="wps-seo-ai-bulk-progress-text" class="description" style="margin-top: 8px;"><?php _e('Chưa bắt đầu quét.', 'wp-plugin-security'); ?></div>
                            </div>
                            <div style="margin-top: 18px;">
                                <div class="description" style="margin-bottom: 8px;"><?php _e('Danh sách bài viết sẽ được quét theo thứ tự:', 'wp-plugin-security'); ?></div>
                                <ol id="wps-seo-ai-bulk-list" style="margin: 0; padding-left: 20px; max-height: 280px; overflow: auto; background: #f8fbfe; border: 1px solid #d9e3ef; border-radius: 12px; padding-top: 12px; padding-bottom: 12px;"></ol>
                            </div>
                        </div>
                        <script>
                        (function(){
                            var button = document.getElementById('wps-seo-ai-bulk-scan');
                            var status = document.getElementById('wps-seo-ai-bulk-status');
                            var progressBar = document.getElementById('wps-seo-ai-bulk-progress-bar');
                            var progressText = document.getElementById('wps-seo-ai-bulk-progress-text');
                            var queueList = document.getElementById('wps-seo-ai-bulk-list');
                            if (!button) {
                                return;
                            }

                            function setStatus(text) {
                                if (status) {
                                    status.textContent = text;
                                }
                            }

                            function setProgress(current, total) {
                                var percent = 0;
                                if (total > 0) {
                                    percent = Math.min(100, Math.round((current / total) * 100));
                                }

                                if (progressBar) {
                                    progressBar.style.width = percent + '%';
                                }

                                if (progressText) {
                                    progressText.textContent = total > 0
                                        ? '<?php echo esc_js(__('Đã xử lý', 'wp-plugin-security')); ?> ' + current + '/' + total + ' (' + percent + '%)'
                                        : '<?php echo esc_js(__('Không có bài viết nào cần quét.', 'wp-plugin-security')); ?>';
                                }
                            }

                            function renderQueue(items) {
                                if (!queueList) {
                                    return;
                                }

                                queueList.innerHTML = '';
                                items.forEach(function(item, index) {
                                    var li = document.createElement('li');
                                    li.setAttribute('data-index', index);
                                    li.style.margin = '0 0 10px 0';
                                    li.style.padding = '8px 12px';
                                    li.style.background = '#ffffff';
                                    li.style.borderLeft = '4px solid #d1dbe7';
                                    li.style.borderRadius = '8px';
                                    li.style.display = 'flex';
                                    li.style.justifyContent = 'space-between';
                                    li.style.gap = '12px';

                                    var title = document.createElement('span');
                                    title.textContent = (index + 1) + '. ' + (item.title || ('#' + item.id));
                                    title.style.fontWeight = '600';
                                    title.style.color = '#1d2a3b';

                                    var meta = document.createElement('span');
                                    meta.textContent = item.post_type ? '[' + item.post_type + ']' : '';
                                    meta.className = 'description';

                                    var state = document.createElement('span');
                                    state.className = 'description';
                                    state.setAttribute('data-state', 'pending');
                                    state.textContent = '<?php echo esc_js(__('Chờ xử lý', 'wp-plugin-security')); ?>';

                                    li.appendChild(title);
                                    li.appendChild(meta);
                                    li.appendChild(state);
                                    queueList.appendChild(li);
                                });
                            }

                            function setItemState(index, text, color) {
                                if (!queueList) {
                                    return;
                                }

                                var item = queueList.querySelector('li[data-index="' + index + '"] [data-state]');
                                if (!item) {
                                    return;
                                }

                                item.textContent = text;
                                item.style.color = color || '';
                            }

                            function fetchQueue() {
                                var formData = new FormData();
                                formData.append('action', 'wps_seo_ai_bulk_queue');
                                formData.append('nonce', button.getAttribute('data-nonce'));

                                return fetch(ajaxurl, {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    body: formData
                                }).then(function(response){
                                    return response.json();
                                }).then(function(payload){
                                    if (!payload || !payload.success) {
                                        throw new Error((payload && payload.data && payload.data.message) ? payload.data.message : '<?php echo esc_js(__('Không tải được danh sách bài viết.', 'wp-plugin-security')); ?>');
                                    }

                                    return payload.data || {};
                                });
                            }

                            function processItem(item, index, total) {
                                var formData = new FormData();
                                formData.append('action', 'wps_seo_ai_bulk_process_post');
                                formData.append('nonce', button.getAttribute('data-nonce'));
                                formData.append('post_id', item.id);

                                setItemState(index, '<?php echo esc_js(__('Đang xử lý', 'wp-plugin-security')); ?>', '#1167ad');
                                setStatus('<?php echo esc_js(__('Đang xử lý bài:', 'wp-plugin-security')); ?> ' + (item.title || ('#' + item.id)));

                                return fetch(ajaxurl, {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    body: formData
                                }).then(function(response){
                                    return response.json();
                                }).then(function(payload){
                                    if (!payload || !payload.success) {
                                        throw new Error((payload && payload.data && payload.data.message) ? payload.data.message : '<?php echo esc_js(__('Quét hàng loạt thất bại.', 'wp-plugin-security')); ?>');
                                    }

                                    var data = payload.data || {};
                                    setItemState(index, '<?php echo esc_js(__('Hoàn tất', 'wp-plugin-security')); ?>', '#1f7a3f');
                                    setStatus((data.message || '<?php echo esc_js(__('Đã tối ưu xong.', 'wp-plugin-security')); ?>') + ' ' + (item.title || ('#' + item.id)));
                                    setProgress(index + 1, total);
                                    return data;
                                }).catch(function(error){
                                    setItemState(index, '<?php echo esc_js(__('Lỗi', 'wp-plugin-security')); ?>', '#b42318');
                                    setStatus((error && error.message ? error.message : '<?php echo esc_js(__('Quét hàng loạt thất bại.', 'wp-plugin-security')); ?>') + ' ' + (item.title || ('#' + item.id)));
                                    setProgress(index + 1, total);
                                    return null;
                                });
                            }

                            async function runQueue(items) {
                                renderQueue(items);
                                var total = items.length;
                                if (!total) {
                                    setStatus('<?php echo esc_js(__('Không có bài viết nào cần quét.', 'wp-plugin-security')); ?>');
                                    setProgress(0, 0);
                                    button.disabled = false;
                                    button.textContent = '<?php echo esc_js(__('Quét và tối ưu toàn bộ bài viết', 'wp-plugin-security')); ?>';
                                    return;
                                }

                                for (var i = 0; i < items.length; i++) {
                                    await processItem(items[i], i, total);
                                }

                                setStatus('<?php echo esc_js(__('Đã quét xong toàn bộ danh sách.', 'wp-plugin-security')); ?>');
                                setProgress(total, total);
                                button.disabled = false;
                                button.textContent = '<?php echo esc_js(__('Quét và tối ưu toàn bộ bài viết', 'wp-plugin-security')); ?>';
                            }

                            button.addEventListener('click', function(event){
                                event.preventDefault();
                                button.disabled = true;
                                button.textContent = '<?php echo esc_js(__('Đang tải danh sách...', 'wp-plugin-security')); ?>';
                                setStatus('<?php echo esc_js(__('Đang lấy danh sách bài viết...', 'wp-plugin-security')); ?>');
                                setProgress(0, 0);

                                fetchQueue().then(function(data){
                                    var items = Array.isArray(data.items) ? data.items : [];
                                    setProgress(0, items.length);
                                    return runQueue(items);
                                }).catch(function(error){
                                    setStatus(error && error.message ? error.message : '<?php echo esc_js(__('Quét hàng loạt thất bại.', 'wp-plugin-security')); ?>');
                                    button.disabled = false;
                                    button.textContent = '<?php echo esc_js(__('Quét và tối ưu toàn bộ bài viết', 'wp-plugin-security')); ?>';
                                });
                            });
                        })();
                        </script>
                        <input type="hidden" name="wps_save_settings" value="1">
                        <?php submit_button(__('Lưu thiết lập SEO AI', 'wp-plugin-security')); ?>
                    </form>

                