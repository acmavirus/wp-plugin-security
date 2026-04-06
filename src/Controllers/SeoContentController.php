<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Controllers;

/**
 * SEO Content: viết lại nội dung theo title/content hiện có bằng Gemini hoặc fallback.
 */
class SeoContentController
{
    public function __construct()
    {
        add_action('save_post', [$this, 'sync_seo_content_on_save'], 30, 3);
        add_action('wp_ajax_wps_seo_content_bulk_queue', [$this, 'ajax_bulk_queue']);
        add_action('wp_ajax_wps_seo_content_bulk_process_post', [$this, 'ajax_bulk_process_post']);
    }

    /**
     * Tự động viết lại nội dung khi lưu bài nếu tính năng được bật.
     */
    public function sync_seo_content_on_save($post_id, $post, $update)
    {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        if (!$this->get_setting('seo_content_enabled', false) || !$this->get_setting('seo_content_auto_update', false)) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $allowed_types = (array) $this->get_setting('seo_content_post_types', $this->get_allowed_post_types());
        if (!in_array($post->post_type, $allowed_types, true)) {
            return;
        }

        $payload = $this->generate_ai_content($post, $post->post_title, $post->post_content);
        if (is_wp_error($payload)) {
            return;
        }

        $this->save_ai_content_payload($post_id, $post, $payload);
    }

    /**
     * Lấy danh sách bài viết cần quét trước.
     */
    public function ajax_bulk_queue()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Bạn không có quyền quét SEO Content.', 'wp-plugin-security')], 403);
        }

        check_ajax_referer('wps_seo_content_bulk_scan', 'nonce');

        if (!$this->get_setting('seo_content_enabled', false)) {
            wp_send_json_error(['message' => __('Hãy bật SEO Content trước khi quét.', 'wp-plugin-security')], 400);
        }

        wp_send_json_success([
            'total' => count($this->get_bulk_scan_queue()),
            'items' => $this->get_bulk_scan_queue(),
        ]);
    }

    /**
     * Xử lý từng bài trong danh sách quét.
     */
    public function ajax_bulk_process_post()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Bạn không có quyền quét SEO Content.', 'wp-plugin-security')], 403);
        }

        check_ajax_referer('wps_seo_content_bulk_scan', 'nonce');

        if (!$this->get_setting('seo_content_enabled', false)) {
            wp_send_json_error(['message' => __('Hãy bật SEO Content trước khi quét.', 'wp-plugin-security')], 400);
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => __('Thiếu post ID.', 'wp-plugin-security')], 400);
        }

        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(['message' => __('Không tìm thấy bài viết.', 'wp-plugin-security')], 404);
        }

        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(['message' => __('Bạn không có quyền chỉnh sửa bài viết này.', 'wp-plugin-security')], 403);
        }

        $allowed_types = (array) $this->get_setting('seo_content_post_types', $this->get_allowed_post_types());
        if (!in_array($post->post_type, $allowed_types, true)) {
            wp_send_json_error(['message' => __('Loại bài viết này chưa được bật cho SEO Content.', 'wp-plugin-security')], 400);
        }

        $payload = $this->generate_ai_content($post, $post->post_title, $post->post_content);
        if (is_wp_error($payload)) {
            wp_send_json_error(['message' => $payload->get_error_message()], 500);
        }

        $this->save_ai_content_payload($post_id, $post, $payload);

        wp_send_json_success([
            'post_id' => $post_id,
            'title' => $post->post_title,
            'message' => $payload['source'] === 'gemini'
                ? __('Đã viết lại nội dung bằng Gemini.', 'wp-plugin-security')
                : __('Đã tối ưu nội dung bằng logic nội bộ.', 'wp-plugin-security'),
            'score' => (int) get_post_meta($post_id, '_wps_seo_content_score', true),
            'source' => $payload['source'],
        ]);
    }

    /**
     * Tạo nội dung mới bằng Gemini hoặc fallback.
     */
    private function generate_ai_content($post, $title, $content)
    {
        $brand = (string) get_bloginfo('name');
        $use_gemini = $this->get_setting('seo_content_use_gemini', false);
        $api_key = $this->get_gemini_api_key();
        $model = (string) $this->get_setting('seo_ai_gemini_model', 'gemini-2.5-flash');
        $temperature = (float) $this->get_setting('seo_ai_gemini_temperature', 0.4);

        if ($use_gemini && $api_key !== '') {
            $result = $this->request_gemini_content($api_key, $model, $temperature, $post, $title, $content, $brand);
            if (!is_wp_error($result)) {
                return $this->normalize_ai_content($post, $result, $title, $content, true);
            }
        }

        return $this->normalize_ai_content($post, [], $title, $content, false);
    }

    /**
     * Gửi prompt viết lại nội dung tới Gemini.
     */
    private function request_gemini_content($api_key, $model, $temperature, $post, $title, $content, $brand)
    {
        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            rawurlencode($model)
        );

        $content_excerpt = wp_strip_all_tags($content);
        $content_excerpt = preg_replace('/\s+/', ' ', $content_excerpt);
        $content_excerpt = function_exists('mb_substr') ? mb_substr($content_excerpt, 0, 7000) : substr($content_excerpt, 0, 7000);
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
                                "Viết lại nội dung bài WordPress theo prompt hệ thống phía trên.\n\nTitle: %s\nBrand: %s\nPost type: %s\nContent: %s\n\nTrả về đúng JSON hợp lệ theo schema.",
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
                'maxOutputTokens' => 2048,
                'responseMimeType' => 'application/json',
                'responseJsonSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'content_html' => ['type' => 'string'],
                        'summary' => ['type' => 'string'],
                        'notes' => ['type' => 'string'],
                    ],
                    'required' => ['content_html'],
                ],
            ],
        ];

        $response = wp_remote_post($endpoint, [
            'timeout' => 45,
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
     * Chuẩn hóa nội dung sau khi Gemini hoặc fallback trả về.
     */
    private function normalize_ai_content($post, array $raw, $title, $content, $from_gemini = false)
    {
        $html = trim((string) ($raw['content_html'] ?? ''));
        $summary = trim((string) ($raw['summary'] ?? ''));

        if ($html === '') {
            $html = $this->build_fallback_content($title, $content);
        } else {
            $html = $this->clean_html($html);
        }

        $original_html = $this->clean_html($this->build_fallback_content($title, $content));
        if ($html === '' || $this->normalize_compare($html) === $this->normalize_compare($original_html)) {
            $html = $this->rewrite_content_with_structure($title, $content);
        }

        if ($summary === '') {
            $summary = $this->build_summary($html, $title);
        }

        return [
            'content_html' => $html,
            'summary' => $summary,
            'score' => $this->estimate_content_score($post, $title, $html),
            'source' => $from_gemini ? 'gemini' : 'heuristic',
        ];
    }

    /**
     * Lưu nội dung mới vào bài viết.
     */
    private function save_ai_content_payload($post_id, $post, array $payload)
    {
        update_post_meta($post_id, '_wps_seo_content_score', (int) $payload['score']);
        update_post_meta($post_id, '_wps_seo_content_summary', $payload['summary']);

        if (trim((string) $post->post_content) === trim((string) $payload['content_html'])) {
            return;
        }

        remove_action('save_post', [$this, 'sync_seo_content_on_save'], 30);

        $update = [
            'ID' => $post_id,
            'post_content' => $payload['content_html'],
        ];

        if (empty($post->post_excerpt) && $payload['summary'] !== '') {
            $update['post_excerpt'] = $payload['summary'];
        }

        wp_update_post($update);

        add_action('save_post', [$this, 'sync_seo_content_on_save'], 30, 3);
    }

    /**
     * Prompt mẫu cho SEO Content.
     */
    private function build_prompt_template($post, $title, $content, $brand)
    {
        $template = (string) $this->get_setting('seo_content_gemini_prompt', '');
        if ($template === '') {
            $template = implode("\n", [
                'Bạn là chuyên gia viết lại nội dung SEO WordPress.',
                'Giữ nguyên ý chính, mở rộng và làm rõ nội dung dựa trên title và content hiện có.',
                'Ưu tiên tiếng Việt tự nhiên, giọng văn dễ đọc, có cấu trúc heading hợp lý.',
                'Không bịa đặt thông tin mới, không thay đổi URL và số liệu nếu không cần thiết.',
                'Trả về JSON thuần với khóa content_html, summary, notes.',
                'content_html phải là HTML hợp lệ với các thẻ p, h2, h3, ul, ol, li nếu cần.',
            ]);
        }

        return strtr($template, [
            '{title}' => $title,
            '{content}' => $content,
            '{brand}' => $brand,
            '{post_type}' => $post->post_type,
        ]);
    }

    /**
     * Fallback nội bộ khi không dùng Gemini.
     */
    private function build_fallback_content($title, $content)
    {
        $title = trim(wp_strip_all_tags((string) $title));
        $content = trim((string) $content);
        if ($content === '') {
            return '<p>' . esc_html($title) . '</p>';
        }

        $content = wp_kses_post($content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        return wpautop($content);
    }

    /**
     * Tạo phiên bản nội dung mới có cấu trúc rõ ràng hơn.
     */
    private function rewrite_content_with_structure($title, $content)
    {
        $title = trim(wp_strip_all_tags((string) $title));
        $plain = trim(wp_strip_all_tags((string) $content));
        $plain = preg_replace('/\s+/', ' ', $plain);

        if ($plain === '') {
            return '<p>' . esc_html($title) . '</p>';
        }

        $sentences = preg_split('/(?<=[.!?])\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY);
        $sentences = array_values(array_filter(array_map('trim', (array) $sentences)));
        $intro = array_shift($sentences);
        $intro = $intro ?: $plain;

        $highlights = array_slice($sentences, 0, 4);
        if (empty($highlights)) {
            $highlights = [$plain];
        }

        $html = [];
        $html[] = '<p>' . esc_html($title) . ' là chủ đề đáng chú ý với các điểm chính được trình bày rõ ràng bên dưới.</p>';
        $html[] = '<p>' . esc_html($intro) . '</p>';
        $html[] = '<h2>' . esc_html__('Điểm chính', 'wp-plugin-security') . '</h2>';
        $html[] = '<ul>';
        foreach ($highlights as $item) {
            $html[] = '<li>' . esc_html($item) . '</li>';
        }
        $html[] = '</ul>';

        if (!empty($sentences)) {
            $html[] = '<h2>' . esc_html__('Chi tiết mở rộng', 'wp-plugin-security') . '</h2>';
            $html[] = '<p>' . esc_html(implode(' ', $sentences)) . '</p>';
        }

        $html[] = '<p>' . esc_html__('Phần nội dung trên đã được làm lại để dễ đọc hơn, có cấu trúc rõ ràng hơn và phù hợp với SEO on-page.', 'wp-plugin-security') . '</p>';

        return implode("\n", $html);
    }

    private function build_summary($content, $title)
    {
        $plain = wp_strip_all_tags((string) $content);
        $plain = preg_replace('/\s+/', ' ', $plain);
        $plain = trim($plain);

        if ($plain === '') {
            return $title;
        }

        return function_exists('mb_substr') ? mb_substr($plain, 0, 160) : substr($plain, 0, 160);
    }

    private function estimate_content_score($post, $title, $content)
    {
        $plain = wp_strip_all_tags((string) $content);
        $plain = preg_replace('/\s+/', ' ', $plain);
        $plain = trim($plain);
        $score = 0;

        if (strlen($plain) >= 500) {
            $score += 30;
        }

        if (strlen($plain) >= 1200) {
            $score += 20;
        }

        if (stripos($plain, $title) !== false) {
            $score += 20;
        }

        if (preg_match('/<h2[^>]*>/i', $content)) {
            $score += 15;
        }

        if (preg_match('/<p[^>]*>/i', $content)) {
            $score += 10;
        }

        if (preg_match('/<ul[^>]*>|<ol[^>]*>/i', $content)) {
            $score += 5;
        }

        return min(100, $score);
    }

    private function clean_html($html)
    {
        $html = wp_kses_post((string) $html);
        $html = preg_replace('/\s+/', ' ', $html);
        $html = str_replace(['<p></p>', '<p> </p>'], '', $html);

        return trim($html);
    }

    private function normalize_compare($html)
    {
        $html = wp_strip_all_tags((string) $html);
        $html = preg_replace('/\s+/', ' ', $html);

        return trim(function_exists('mb_strtolower') ? mb_strtolower($html) : strtolower($html));
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

    private function get_bulk_scan_queue()
    {
        $post_types = (array) $this->get_setting('seo_content_post_types', $this->get_allowed_post_types());
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

    private function get_allowed_post_types()
    {
        $post_types = get_post_types(['public' => true], 'names');
        $post_types = is_array($post_types) ? array_keys($post_types) : [];
        $excluded = ['attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request'];

        return array_values(array_diff($post_types, $excluded));
    }
}

// Copyright by AcmaTvirus
