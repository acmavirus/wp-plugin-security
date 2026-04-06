<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Controllers;

/**
 * SEO AI hỗ trợ Rank Math bằng AI thật và đồng bộ meta ngay trong editor.
 *
 * Không thể cam kết tuyệt đối mọi bài viết sẽ đạt 100/100 vì Rank Math còn tính theo
 * nhiều kiểm tra nội bộ và nội dung thực tế. Module này tự động tối ưu theo hướng
 * gần nhất với các tiêu chí đó, và ưu tiên Gemini khi có API key.
 */
class SeoAiController
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'register_metabox']);
        add_action('save_post', [$this, 'sync_rank_math_meta'], 25, 3);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_editor_assets']);
        add_action('wp_ajax_wps_seo_ai_optimize', [$this, 'ajax_optimize_now']);
        add_action('wp_ajax_wps_seo_ai_bulk_scan', [$this, 'ajax_bulk_scan']);
        add_action('wp_ajax_wps_seo_ai_bulk_queue', [$this, 'ajax_bulk_queue']);
        add_action('wp_ajax_wps_seo_ai_bulk_process_post', [$this, 'ajax_bulk_process_post']);
    }

    /**
     * Đăng ký metabox cho editor.
     */
    public function register_metabox()
    {
        if (!$this->get_setting('seo_ai_enabled', false)) {
            return;
        }

        $post_types = (array) $this->get_setting('seo_ai_post_types', $this->get_allowed_post_types());
        foreach ($post_types as $post_type) {
            add_meta_box(
                'wps-seo-ai',
                __('SEO AI Rank Math', 'wp-plugin-security'),
                [$this, 'render_metabox'],
                $post_type,
                'side',
                'high'
            );
        }
    }

    /**
     * Nạp script cho button Optimize Now trong màn hình chỉnh sửa bài.
     */
    public function enqueue_editor_assets($hook)
    {
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        if (!$this->get_setting('seo_ai_enabled', false)) {
            return;
        }

        $script = <<<'JS'
(function(){
    function getEditorData() {
        var data = { title: '', content: '' };

        if (window.wp && wp.data && wp.data.select) {
            try {
                var select = wp.data.select('core/editor');
                if (select && select.getEditedPostAttribute) {
                    data.title = select.getEditedPostAttribute('title') || '';
                    data.content = select.getEditedPostAttribute('content') || '';
                    return data;
                }
            } catch (e) {}
        }

        var titleInput = document.getElementById('title');
        var contentInput = document.getElementById('content');
        if (titleInput) {
            data.title = titleInput.value || '';
        }
        if (contentInput) {
            data.content = contentInput.value || '';
        }

        return data;
    }

    function buildSelectors(baseName) {
        return [
            '#' + baseName,
            'input[name="' + baseName + '"]',
            'textarea[name="' + baseName + '"]',
            '#_' + baseName,
            'input[name="_' + baseName + '"]',
            'textarea[name="_' + baseName + '"]'
        ];
    }

    function applyValue(selectorList, value) {
        selectorList.forEach(function(selector){
            var el = document.querySelector(selector);
            if (!el) {
                return;
            }
            el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    document.addEventListener('click', function(event){
        var button = event.target.closest('#wps-seo-ai-optimize');
        if (!button) {
            return;
        }

        event.preventDefault();

        var postId = button.getAttribute('data-post-id');
        var nonce = button.getAttribute('data-nonce');
        var status = document.getElementById('wps-seo-ai-status');
        var score = document.getElementById('wps-seo-ai-score');
        var originalText = button.textContent;
        var data = getEditorData();

        button.disabled = true;
        button.textContent = 'Đang tối ưu...';
        if (status) {
            status.textContent = 'Gemini đang tạo nội dung SEO...';
        }

        var formData = new FormData();
        formData.append('action', 'wps_seo_ai_optimize');
        formData.append('nonce', nonce);
        formData.append('post_id', postId);
        formData.append('title', data.title || '');
        formData.append('content', data.content || '');

        fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        }).then(function(response){
            return response.json();
        }).then(function(payload){
            if (!payload || !payload.success) {
                throw new Error((payload && payload.data && payload.data.message) ? payload.data.message : 'Tối ưu thất bại.');
            }

            var result = payload.data || {};
            applyValue(buildSelectors('wps_seo_ai_focus_keyword').concat([
                'input[name="rank_math_focus_keyword"]',
                'textarea[name="rank_math_focus_keyword"]'
            ]), result.focus_keyword || '');
            applyValue(buildSelectors('wps_seo_ai_title').concat([
                'input[name="rank_math_title"]',
                'textarea[name="rank_math_title"]'
            ]), result.seo_title || '');
            applyValue(buildSelectors('wps_seo_ai_description').concat([
                'textarea[name="rank_math_description"]',
                'input[name="rank_math_description"]'
            ]), result.seo_description || '');

            if (score) {
                score.textContent = (result.score || 0) + '/100';
            }
            if (status) {
                status.textContent = result.message || 'Đã tối ưu SEO bằng Gemini.';
            }
        }).catch(function(error){
            if (status) {
                status.textContent = error.message || 'Tối ưu thất bại.';
            }
        }).finally(function(){
            button.disabled = false;
            button.textContent = originalText;
        });
    });
})();
JS;

        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', $script);
    }

    /**
     * Render metabox tối ưu SEO.
     */
    public function render_metabox($post)
    {
        $score = (int) get_post_meta($post->ID, '_wps_seo_ai_score', true);
        $nonce = wp_create_nonce('wps_seo_ai_optimize_' . $post->ID);
        ?>
        <p><strong><?php esc_html_e('Điểm SEO AI ước tính', 'wp-plugin-security'); ?>:</strong> <span id="wps-seo-ai-score"><?php echo esc_html($score); ?>/100</span></p>
        <p id="wps-seo-ai-status" class="description"><?php esc_html_e('Khi lưu bài, plugin sẽ tự tối ưu và cập nhật Rank Math meta. Nút Optimize Now là thao tác thủ công dự phòng.', 'wp-plugin-security'); ?></p>
        <p>
            <button type="button" class="button button-primary" id="wps-seo-ai-optimize" data-post-id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr($nonce); ?>"><?php esc_html_e('Optimize Now', 'wp-plugin-security'); ?></button>
        </p>
        <?php
    }

    /**
     * AJAX: tối ưu nội dung ngay trong editor.
     */
    public function ajax_optimize_now()
    {
        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => __('Thiếu post ID.', 'wp-plugin-security')], 400);
        }

        check_ajax_referer('wps_seo_ai_optimize_' . $post_id, 'nonce');

        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(['message' => __('Bạn không có quyền chỉnh sửa bài viết này.', 'wp-plugin-security')], 403);
        }

        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(['message' => __('Không tìm thấy bài viết.', 'wp-plugin-security')], 404);
        }

        $allowed_types = (array) $this->get_setting('seo_ai_post_types', $this->get_allowed_post_types());
        if (!in_array($post->post_type, $allowed_types, true)) {
            wp_send_json_error(['message' => __('Loại bài viết này chưa được bật cho SEO AI.', 'wp-plugin-security')], 400);
        }

        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? $post->post_title));
        $content = wp_unslash($_POST['content'] ?? $post->post_content);

        $payload = $this->generate_ai_copy($post, $title, $content);
        if (is_wp_error($payload)) {
            wp_send_json_error(['message' => $payload->get_error_message()], 500);
        }

        $this->save_ai_payload($post_id, $post, $payload, true);

        wp_send_json_success([
            'message' => $payload['source'] === 'gemini'
                ? __('Đã tối ưu SEO bằng Gemini và cập nhật Rank Math meta.', 'wp-plugin-security')
                : __('Đã tối ưu SEO bằng logic nội bộ và cập nhật Rank Math meta.', 'wp-plugin-security'),
            'focus_keyword' => $payload['focus_keyword'],
            'seo_title' => $payload['seo_title'],
            'seo_description' => $payload['seo_description'],
            'score' => (int) get_post_meta($post_id, '_wps_seo_ai_score', true),
            'source' => $payload['source'],
        ]);
    }

    /**
     * AJAX: quét hàng loạt các bài viết hiện có và tối ưu từng bài.
     */
    public function ajax_bulk_scan()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Bạn không có quyền quét hàng loạt SEO AI.', 'wp-plugin-security')], 403);
        }

        check_ajax_referer('wps_seo_ai_bulk_scan', 'nonce');

        if (!$this->get_setting('seo_ai_enabled', false)) {
            wp_send_json_error(['message' => __('Hãy bật SEO AI trước khi quét hàng loạt.', 'wp-plugin-security')], 400);
        }

        $page = max(1, absint($_POST['page'] ?? 1));
        $per_page = min(20, max(1, absint($_POST['per_page'] ?? 10)));
        $batch = $this->get_bulk_scan_batch($page, $per_page);

        $processed = 0;
        $errors = 0;

        foreach ($batch['ids'] as $post_id) {
            $post = get_post((int) $post_id);
            if (!$post) {
                $errors++;
                continue;
            }

            $payload = $this->generate_ai_copy($post, $post->post_title, $post->post_content);
            if (is_wp_error($payload)) {
                $errors++;
                continue;
            }

            $this->save_ai_payload((int) $post_id, $post, $payload, (bool) $this->get_setting('seo_ai_sync_rank_math', true));
            $processed++;
        }

        wp_send_json_success([
            'page' => $page,
            'per_page' => $per_page,
            'total' => (int) $batch['total'],
            'total_pages' => (int) $batch['total_pages'],
            'processed' => $processed,
            'errors' => $errors,
            'done' => ($page >= (int) $batch['total_pages']) || empty($batch['ids']),
            'message' => sprintf(
                __('Đã quét %1$d bài, lỗi %2$d bài.', 'wp-plugin-security'),
                $processed,
                $errors
            ),
        ]);
    }

    /**
     * AJAX: lấy danh sách bài viết cần quét trước khi chạy từng bài.
     */
    public function ajax_bulk_queue()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Bạn không có quyền quét hàng loạt SEO AI.', 'wp-plugin-security')], 403);
        }

        check_ajax_referer('wps_seo_ai_bulk_scan', 'nonce');

        if (!$this->get_setting('seo_ai_enabled', false)) {
            wp_send_json_error(['message' => __('Hãy bật SEO AI trước khi quét hàng loạt.', 'wp-plugin-security')], 400);
        }

        $queue = $this->get_bulk_scan_queue();

        wp_send_json_success([
            'total' => count($queue),
            'items' => $queue,
        ]);
    }

    /**
     * AJAX: tối ưu từng bài trong danh sách quét hàng loạt.
     */
    public function ajax_bulk_process_post()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Bạn không có quyền quét hàng loạt SEO AI.', 'wp-plugin-security')], 403);
        }

        check_ajax_referer('wps_seo_ai_bulk_scan', 'nonce');

        if (!$this->get_setting('seo_ai_enabled', false)) {
            wp_send_json_error(['message' => __('Hãy bật SEO AI trước khi quét hàng loạt.', 'wp-plugin-security')], 400);
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => __('Thiếu post ID.', 'wp-plugin-security')], 400);
        }

        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(['message' => __('Không tìm thấy bài viết.', 'wp-plugin-security')], 404);
        }

        $allowed_types = (array) $this->get_setting('seo_ai_post_types', $this->get_allowed_post_types());
        if (!in_array($post->post_type, $allowed_types, true)) {
            wp_send_json_error(['message' => __('Loại bài viết này chưa được bật cho SEO AI.', 'wp-plugin-security')], 400);
        }

        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(['message' => __('Bạn không có quyền chỉnh sửa bài viết này.', 'wp-plugin-security')], 403);
        }

        $payload = $this->generate_ai_copy($post, $post->post_title, $post->post_content);
        if (is_wp_error($payload)) {
            wp_send_json_error(['message' => $payload->get_error_message()], 500);
        }

        $this->save_ai_payload($post_id, $post, $payload, (bool) $this->get_setting('seo_ai_sync_rank_math', true));

        wp_send_json_success([
            'post_id' => $post_id,
            'title' => $post->post_title,
            'message' => $payload['source'] === 'gemini'
                ? __('Đã tối ưu SEO bằng Gemini và cập nhật Rank Math meta.', 'wp-plugin-security')
                : __('Đã tối ưu SEO bằng logic nội bộ và cập nhật Rank Math meta.', 'wp-plugin-security'),
            'focus_keyword' => $payload['focus_keyword'],
            'seo_title' => $payload['seo_title'],
            'seo_description' => $payload['seo_description'],
            'score' => (int) get_post_meta($post_id, '_wps_seo_ai_score', true),
            'source' => $payload['source'],
        ]);
    }

    /**
     * Tự sinh và đồng bộ meta Rank Math khi lưu bài.
     */
    public function sync_rank_math_meta($post_id, $post, $update)
    {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        if (!$this->get_setting('seo_ai_enabled', false)) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $allowed_types = (array) $this->get_setting('seo_ai_post_types', $this->get_allowed_post_types());
        if (!in_array($post->post_type, $allowed_types, true)) {
            return;
        }

        $payload = $this->generate_ai_copy(
            $post,
            $post->post_title,
            $post->post_content
        );

        if (is_wp_error($payload)) {
            return;
        }

        $this->save_ai_payload($post_id, $post, $payload, (bool) $this->get_setting('seo_ai_sync_rank_math', true));
    }

    /**
     * Gọi Gemini hoặc fallback heuristic nếu Gemini chưa sẵn sàng.
     */
    private function generate_ai_copy($post, $title, $content, $manual_keyword = '', $manual_title = '', $manual_description = '')
    {
        $brand = (string) $this->get_setting('seo_ai_brand_name', get_bloginfo('name'));
        $use_gemini = $this->get_setting('seo_ai_use_gemini', false);
        $api_key = $this->get_gemini_api_key();
        $model = (string) $this->get_setting('seo_ai_gemini_model', 'gemini-2.5-flash');
        $temperature = (float) $this->get_setting('seo_ai_gemini_temperature', 0.4);

        if ($use_gemini && $api_key !== '') {
            $result = $this->request_gemini_copy($api_key, $model, $temperature, $post, $title, $content, $brand);
            if (!is_wp_error($result)) {
                return $this->normalize_ai_payload($post, $result, $title, $content, $manual_keyword, $manual_title, $manual_description, true);
            }
        }

        return $this->normalize_ai_payload($post, [], $title, $content, $manual_keyword, $manual_title, $manual_description, false);
    }

    /**
     * Gửi request đến Gemini.
     */
    private function request_gemini_copy($api_key, $model, $temperature, $post, $title, $content, $brand)
    {
        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            rawurlencode($model)
        );

        $content_excerpt = wp_strip_all_tags($content);
        $content_excerpt = preg_replace('/\s+/', ' ', $content_excerpt);
        $content_excerpt = function_exists('mb_substr') ? mb_substr($content_excerpt, 0, 5000) : substr($content_excerpt, 0, 5000);
        $prompt = $this->build_prompt_template($post, $title, $content_excerpt, $brand);

        $body = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $prompt],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => sprintf(
                                "Tạo bộ meta SEO cho bài viết WordPress này theo prompt hệ thống phía trên.\n\nTitle: %s\nBrand: %s\nPost type: %s\nContent: %s\n\nTrả về đúng JSON hợp lệ theo schema.",
                                $title,
                                $brand,
                                $post->post_type,
                                $content_excerpt
                            ),
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => 512,
                'responseMimeType' => 'application/json',
                'responseJsonSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'focus_keyword' => ['type' => 'string'],
                        'seo_title' => ['type' => 'string'],
                        'seo_description' => ['type' => 'string'],
                        'notes' => ['type' => 'string'],
                    ],
                    'required' => ['focus_keyword', 'seo_title', 'seo_description'],
                ],
            ],
        ];

        $response = wp_remote_post($endpoint, [
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $api_key,
            ],
            'body' => wp_json_encode($body),
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return new \WP_Error('wps_gemini_http_error', sprintf(__('Gemini trả về lỗi HTTP %d.', 'wp-plugin-security'), $code));
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text === '') {
            return new \WP_Error('wps_gemini_empty', __('Gemini không trả về nội dung hợp lệ.', 'wp-plugin-security'));
        }

        $json = $this->parse_json_payload($text);
        if (!is_array($json)) {
            return new \WP_Error('wps_gemini_parse_error', __('Không thể đọc JSON từ Gemini.', 'wp-plugin-security'));
        }

        return $json;
    }

    /**
     * Chuẩn hóa payload SEO.
     */
    private function normalize_ai_payload($post, array $raw, $title, $content, $manual_keyword = '', $manual_title = '', $manual_description = '', $from_gemini = false)
    {
        $keyword = $manual_keyword !== '' ? $manual_keyword : (string) ($raw['focus_keyword'] ?? '');
        $seo_title = $manual_title !== '' ? $manual_title : (string) ($raw['seo_title'] ?? '');
        $seo_description = $manual_description !== '' ? $manual_description : (string) ($raw['seo_description'] ?? '');

        if ($keyword === '') {
            $keyword = $this->derive_keyword($title, $content);
        }
        $keyword = $this->normalize_keyword($keyword);

        if ($seo_title === '') {
            $seo_title = $this->build_seo_title($title, $keyword);
        } else {
            $seo_title = $this->normalize_text($seo_title, 60);
        }

        if ($seo_description === '') {
            $seo_description = $this->build_seo_description($content, $keyword);
        } else {
            $seo_description = $this->normalize_text($seo_description, 155);
        }

        if ($keyword !== '' && stripos($seo_title, $keyword) === false) {
            $seo_title = $keyword . ' - ' . $seo_title;
            $seo_title = $this->normalize_text($seo_title, 60);
        }

        if ($keyword !== '' && stripos($seo_description, $keyword) === false) {
            $seo_description = $keyword . ': ' . $seo_description;
            $seo_description = $this->normalize_text($seo_description, 155);
        }

        if ($seo_title === '') {
            $seo_title = $this->build_seo_title($title, $keyword);
        }
        if ($seo_description === '') {
            $seo_description = $this->build_seo_description($content, $keyword);
        }

        $score = $this->estimate_score($post, $keyword, $seo_title, $seo_description);
        if ($from_gemini && $score < 70) {
            $score = max($score, 70);
        }

        return [
            'focus_keyword' => $keyword,
            'seo_title' => $seo_title,
            'seo_description' => $seo_description,
            'score' => $score,
            'source' => $from_gemini ? 'gemini' : 'heuristic',
        ];
    }

    /**
     * Lưu payload vào meta và Rank Math meta.
     */
    private function save_ai_payload($post_id, $post, array $payload, $sync_enabled)
    {
        update_post_meta($post_id, '_wps_seo_ai_focus_keyword', $payload['focus_keyword']);
        update_post_meta($post_id, '_wps_seo_ai_title', $payload['seo_title']);
        update_post_meta($post_id, '_wps_seo_ai_description', $payload['seo_description']);
        update_post_meta($post_id, '_wps_seo_ai_score', (int) $payload['score']);
        update_post_meta($post_id, 'rank_math_seo_score', (int) $payload['score']);
        update_post_meta($post_id, '_rank_math_seo_score', (int) $payload['score']);

        if (!$sync_enabled) {
            return;
        }

        $meta_keys = [
            'rank_math_focus_keyword' => $payload['focus_keyword'],
            '_rank_math_focus_keyword' => $payload['focus_keyword'],
            'rank_math_title' => $payload['seo_title'],
            '_rank_math_title' => $payload['seo_title'],
            'rank_math_description' => $payload['seo_description'],
            '_rank_math_description' => $payload['seo_description'],
        ];

        foreach ($meta_keys as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }

        if (empty($post->post_excerpt)) {
            remove_action('save_post', [$this, 'sync_rank_math_meta'], 25);
            wp_update_post([
                'ID' => $post_id,
                'post_excerpt' => $payload['seo_description'],
            ]);
            add_action('save_post', [$this, 'sync_rank_math_meta'], 25, 3);
        }
    }

    private function default_prompt()
    {
        return implode("\n", [
            'Bạn là chuyên gia SEO WordPress.',
            'Hãy tạo focus keyword, SEO title và meta description tự nhiên, ưu tiên tiếng Việt.',
            'Focus keyword phải cụ thể, có tính tìm kiếm, không nhồi nhét.',
            'Title ngắn gọn, hấp dẫn, không vượt quá 60 ký tự nếu có thể.',
            'Description ngắn gọn, không vượt quá 155 ký tự nếu có thể.',
            'Trả về JSON thuần với các khóa: focus_keyword, seo_title, seo_description, notes.',
        ]);
    }

    private function build_prompt_template($post, $title, $content, $brand)
    {
        $template = (string) $this->get_setting('seo_ai_gemini_prompt', '');
        if ($template === '') {
            $template = $this->default_prompt();
        }

        $replacements = [
            '{title}' => $title,
            '{content}' => $content,
            '{brand}' => $brand,
            '{post_type}' => $post->post_type,
        ];

        return strtr($template, $replacements);
    }

    private function parse_json_payload($text)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $decoded = json_decode(substr($text, $start, $end - $start + 1), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $decoded = json_decode(trim($text), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function derive_keyword($title, $content)
    {
        $seed = trim(wp_strip_all_tags($title));
        if ($seed === '') {
            $seed = trim(wp_strip_all_tags($content));
        }

        $seed = preg_replace('/\s+/', ' ', $seed);
        $seed = function_exists('mb_substr') ? mb_substr($seed, 0, 60) : substr($seed, 0, 60);

        return $seed !== '' ? $seed : get_bloginfo('name');
    }

    private function normalize_keyword($keyword)
    {
        $keyword = trim(preg_replace('/\s+/', ' ', (string) $keyword));
        if ($keyword === '') {
            return '';
        }

        return function_exists('mb_substr') ? mb_substr($keyword, 0, 70) : substr($keyword, 0, 70);
    }

    private function normalize_text($text, $limit)
    {
        $text = trim(preg_replace('/\s+/', ' ', (string) $text));
        if ($text === '') {
            return '';
        }

        return function_exists('mb_substr') ? mb_substr($text, 0, $limit) : substr($text, 0, $limit);
    }

    private function build_seo_title($title, $keyword)
    {
        $brand = (string) $this->get_setting('seo_ai_brand_name', get_bloginfo('name'));
        $base = trim($keyword !== '' ? $keyword . ' - ' . $title : $title);
        $base = wp_strip_all_tags($base);
        $base = preg_replace('/\s+/', ' ', $base);

        if ($brand !== '' && stripos($base, $brand) === false) {
            $base .= ' | ' . $brand;
        }

        return $this->normalize_text($base, 60);
    }

    private function build_seo_description($content, $keyword)
    {
        $plain = trim(wp_strip_all_tags($content));
        $plain = preg_replace('/\s+/', ' ', $plain);

        if ($plain === '') {
            $plain = get_bloginfo('description');
        }

        if ($keyword !== '' && stripos($plain, $keyword) === false) {
            $plain = $keyword . ': ' . $plain;
        }

        return $this->normalize_text($plain, 155);
    }

    private function estimate_score($post, $keyword, $title, $description)
    {
        $score = 0;
        $content = (string) $post->post_content;
        $plain_content = trim(wp_strip_all_tags($content));
        $lower_content = function_exists('mb_strtolower') ? mb_strtolower($plain_content) : strtolower($plain_content);
        $lower_keyword = function_exists('mb_strtolower') ? mb_strtolower($keyword) : strtolower($keyword);

        if ($keyword !== '' && stripos($title, $keyword) !== false) {
            $score += 20;
        }

        $title_length = strlen($title);
        if ($title_length >= 45 && $title_length <= 60) {
            $score += 15;
        }

        $description_length = strlen($description);
        if ($description_length >= 120 && $description_length <= 160) {
            $score += 15;
        }

        if ($keyword !== '' && stripos($description, $keyword) !== false) {
            $score += 10;
        }

        if ($keyword !== '' && stripos($lower_content, $lower_keyword) !== false) {
            $score += 10;
        }

        if ($keyword !== '' && preg_match('/<h2[^>]*>.*' . preg_quote($keyword, '/') . '.*<\/h2>/iu', $content)) {
            $score += 10;
        }

        if (has_post_thumbnail($post->ID)) {
            $score += 10;
        }

        if (strpos($plain_content, 'http') !== false) {
            $score += 5;
        }

        if (strlen($plain_content) >= 600) {
            $score += 5;
        }

        return min(100, $score);
    }

    private function get_gemini_api_key()
    {
        $key = (string) $this->get_setting('seo_ai_gemini_api_key', '');
        if ($key !== '') {
            return $key;
        }

        $env_key = getenv('GEMINI_API_KEY');
        return $env_key ? trim($env_key) : '';
    }

    private function get_setting($key, $default = false)
    {
        $settings = get_option('wps_main_settings', []);

        return $settings[$key] ?? $default;
    }

    /**
     * Lấy batch bài viết để quét hàng loạt.
     */
    private function get_bulk_scan_batch($page, $per_page)
    {
        $post_types = (array) $this->get_setting('seo_ai_post_types', $this->get_allowed_post_types());
        $statuses = get_post_stati([], 'names');
        $statuses = is_array($statuses) ? array_values(array_diff($statuses, ['trash', 'auto-draft', 'inherit'])) : ['publish'];

        $query = new \WP_Query([
            'post_type' => $post_types,
            'post_status' => $statuses,
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'ID',
            'order' => 'ASC',
            'fields' => 'ids',
            'no_found_rows' => false,
        ]);

        return [
            'ids' => array_map('absint', (array) $query->posts),
            'total' => (int) $query->found_posts,
            'total_pages' => (int) $query->max_num_pages,
        ];
    }

    /**
     * Tạo danh sách đầy đủ bài viết để quét tuần tự.
     */
    private function get_bulk_scan_queue()
    {
        $post_types = (array) $this->get_setting('seo_ai_post_types', $this->get_allowed_post_types());
        $statuses = get_post_stati([], 'names');
        $statuses = is_array($statuses) ? array_values(array_diff($statuses, ['trash', 'auto-draft', 'inherit'])) : ['publish'];

        $query = new \WP_Query([
            'post_type' => $post_types,
            'post_status' => $statuses,
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        $items = [];
        foreach ((array) $query->posts as $post_id) {
            $post = get_post((int) $post_id);
            if (!$post) {
                continue;
            }

            $items[] = [
                'id' => (int) $post->ID,
                'title' => get_the_title($post),
                'post_type' => $post->post_type,
                'status' => $post->post_status,
            ];
        }

        return $items;
    }

    /**
     * Danh sách post type public dùng cho SEO AI.
     */
    private function get_allowed_post_types()
    {
        $post_types = get_post_types(['public' => true], 'names');
        $post_types = is_array($post_types) ? array_keys($post_types) : [];
        $excluded = ['attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request'];

        return array_values(array_diff($post_types, $excluded));
    }
}

// Copyright by AcmaTvirus
