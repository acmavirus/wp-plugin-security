<?php
// Copyright by AcmaTvirus

namespace Acma\WpSecurity\Controllers;

/**
 * Controller cho cac tinh nang changelog co the van hanh tren frontend/backend.
 */
class FeatureController
{
    public function __construct()
    {
        add_action('init', [$this, 'bootstrap_frontend_features'], 1);
        add_action('template_redirect', [$this, 'maybe_start_output_buffer'], 0);
        add_action('wp_head', [$this, 'maybe_print_preload_links'], 1);
        add_action('save_post', [$this, 'maybe_set_auto_featured_image'], 20, 3);

        add_filter('the_content', [$this, 'inject_toc_into_content'], 20);
        add_filter('wp_headers', [$this, 'filter_browser_cache_headers']);
        add_filter('script_loader_tag', [$this, 'filter_script_loader_tag'], 10, 3);
        add_filter('style_loader_src', [$this, 'maybe_rewrite_cdn_url'], 20, 2);
        add_filter('script_loader_src', [$this, 'maybe_rewrite_cdn_url'], 20, 2);
        add_filter('wp_get_attachment_url', [$this, 'maybe_rewrite_attachment_url'], 20, 2);

        add_filter('use_block_editor_for_post_type', [$this, 'maybe_disable_block_editor'], 10, 2);
        add_filter('mce_buttons', [$this, 'extend_tinymce_buttons']);
        add_filter('mce_buttons_2', [$this, 'extend_tinymce_buttons_2']);

        add_filter('pre_set_site_transient_update_plugins', [$this, 'maybe_freeze_plugin_updates']);
        add_filter('pre_set_site_transient_update_themes', [$this, 'maybe_freeze_theme_updates']);
        add_filter('pre_site_transient_update_core', [$this, 'maybe_freeze_core_updates']);
    }

    /**
     * Bat cac feature frontend theo option da luu.
     */
    public function bootstrap_frontend_features()
    {
        if ($this->get_setting('disable_emojis', true)) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            add_filter('emoji_svg_url', '__return_false');
        }

        if ($this->get_setting('disable_block_library_css', true)) {
            add_action('wp_enqueue_scripts', function () {
                wp_dequeue_style('wp-block-library');
                wp_dequeue_style('wp-block-library-theme');
                wp_dequeue_style('global-styles');
            }, 100);
        }

        if ($this->get_setting('disable_dashicons', false) && !is_user_logged_in()) {
            add_action('wp_enqueue_scripts', function () {
                wp_deregister_style('dashicons');
            }, 100);
        }

        if ($this->get_setting('enable_toc', false)) {
            add_action('wp_enqueue_scripts', function () {
                wp_register_style('wps-feature-styles', false);
                wp_enqueue_style('wps-feature-styles');
                wp_add_inline_style('wps-feature-styles', '
                    .wps-toc{border:1px solid #dfeaf5;border-radius:16px;padding:18px 20px;margin:0 0 24px;background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);box-shadow:0 12px 24px rgba(1,34,61,.05)}
                    .wps-toc-title{font-weight:700;color:#003b6b;margin-bottom:10px}
                    .wps-toc-list{margin:0;padding-left:18px}
                    .wps-toc-item{margin:6px 0}
                    .wps-toc-item a{text-decoration:none;color:#1167ad}
                    .wps-toc-item a:hover{text-decoration:underline}
                ');
            }, 20);
        }
    }

    /**
     * Bo sung cache headers cho frontend.
     */
    public function filter_browser_cache_headers($headers)
    {
        if (is_admin() || wp_doing_ajax() || is_feed()) {
            return $headers;
        }

        if (!$this->get_setting('enable_browser_cache_headers', true)) {
            return $headers;
        }

        $headers['Cache-Control'] = 'public, max-age=31536000, s-maxage=31536000';
        $headers['Vary'] = isset($headers['Vary']) ? $headers['Vary'] . ', Accept-Encoding' : 'Accept-Encoding';

        return $headers;
    }

    /**
     * Them defer cho cac script khong thiet yeu.
     */
    public function filter_script_loader_tag($tag, $handle, $src)
    {
        if (is_admin()) {
            return $tag;
        }

        if (!$this->get_setting('defer_noncritical_js', false)) {
            return $tag;
        }

        $excluded = [
            'jquery',
            'jquery-core',
            'jquery-migrate',
            'wp-polyfill',
            'wp-hooks',
            'wp-element',
            'wp-i18n',
            'wp-api-request',
            'wp-emoji-release',
            'admin-bar',
        ];

        if (in_array($handle, $excluded, true)) {
            return $tag;
        }

        if (strpos($tag, ' defer') === false && strpos($tag, ' async') === false) {
            $tag = str_replace('<script ', '<script defer ', $tag);
        }

        return $tag;
    }

    /**
     * Print preload/preconnect hints tu setting.
     */
    public function maybe_print_preload_links()
    {
        if (is_admin() || !$this->get_setting('enable_preload_hints', false)) {
            return;
        }

        $assets = preg_split('/\r\n|\r|\n|,/', (string) $this->get_setting('preload_assets', ''));
        $assets = array_values(array_filter(array_map('trim', (array) $assets)));

        foreach ($assets as $asset) {
            $asset = esc_url($asset);
            if ($asset === '') {
                continue;
            }

            $as = 'image';
            $path = wp_parse_url($asset, PHP_URL_PATH) ?: '';
            if (preg_match('/\.(?:css)$/i', $path)) {
                $as = 'style';
            } elseif (preg_match('/\.(?:js)$/i', $path)) {
                $as = 'script';
            } elseif (preg_match('/\.(?:woff2?|ttf|otf)$/i', $path)) {
                $as = 'font';
            }

            echo '<link rel="preload" href="' . esc_url($asset) . '" as="' . esc_attr($as) . '" crossorigin="anonymous">' . "\n";
        }
    }

    /**
     * Rewrite asset url sang CDN neu da cau hinh.
     */
    public function maybe_rewrite_cdn_url($url, $handle = null)
    {
        $cdn_url = trim((string) $this->get_setting('cdn_url', ''));
        if ($cdn_url === '' || is_admin() || wp_doing_ajax()) {
            return $url;
        }

        return $this->replace_origin_with_cdn($url, $cdn_url);
    }

    /**
     * Rewrite attachment url sang CDN neu da cau hinh.
     */
    public function maybe_rewrite_attachment_url($url, $post_id)
    {
        $cdn_url = trim((string) $this->get_setting('cdn_url', ''));
        if ($cdn_url === '' || is_admin() || wp_doing_ajax()) {
            return $url;
        }

        return $this->replace_origin_with_cdn($url, $cdn_url);
    }

    /**
     * Thay origin sang CDN base.
     */
    private function replace_origin_with_cdn($url, $cdn_url)
    {
        $site_url = rtrim(home_url(), '/');
        $cdn_url = rtrim($cdn_url, '/');

        if (stripos($url, $site_url) === 0) {
            return $cdn_url . substr($url, strlen($site_url));
        }

        return $url;
    }

    /**
     * Bat output buffering de minify HTML khi duoc kich hoat.
     */
    public function maybe_start_output_buffer()
    {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        if (!$this->get_setting('minify_html', false)) {
            return;
        }

        ob_start([$this, 'minify_html_output']);
    }

    /**
     * Toi uu HTML output bang cach gom whitespace du thua.
     */
    public function minify_html_output($html)
    {
        if (stripos($html, '</html>') === false) {
            return $html;
        }

        $html = preg_replace('/>\s+</', '><', $html);
        $html = preg_replace('/\s{2,}/', ' ', $html);

        return trim($html);
    }

    /**
     * Chen muc luc tu dong vao content.
     */
    public function inject_toc_into_content($content)
    {
        if (is_admin() || !is_singular()) {
            return $content;
        }

        if (!$this->get_setting('enable_toc', false)) {
            return $content;
        }

        if (!is_main_query() || !in_the_loop()) {
            return $content;
        }

        $post_type = get_post_type();
        $allowed_types = $this->get_setting('toc_post_types', $this->get_toc_post_types());
        if (!in_array($post_type, (array) $allowed_types, true)) {
            return $content;
        }

        preg_match_all('/<h([23])[^>]*>(.*?)<\/h[23]>/', $content, $matches, PREG_SET_ORDER);
        if (count($matches) < 2) {
            return $content;
        }

        $toc_title = $this->get_setting('toc_title', __('Mục lục', 'wp-plugin-security'));
        $toc_items = [];
        $index = 1;

        foreach ($matches as $match) {
            $heading_text = wp_strip_all_tags($match[2]);
            $anchor = sanitize_title($heading_text) . '-' . $index;
            $index++;
            $toc_items[] = [
                'level' => (int) $match[1],
                'title' => $heading_text,
                'anchor' => $anchor,
            ];
        }

        if (empty($toc_items)) {
            return $content;
        }

        $toc = '<nav class="wps-toc"><div class="wps-toc-title">' . esc_html($toc_title) . '</div><ol class="wps-toc-list">';
        foreach ($toc_items as $item) {
            $toc .= '<li class="wps-toc-item wps-toc-level-' . (int) $item['level'] . '"><a href="#' . esc_attr($item['anchor']) . '">' . esc_html($item['title']) . '</a></li>';
        }
        $toc .= '</ol></nav>';

        $counter = 0;
        $content = preg_replace_callback('/<h([23])([^>]*)>(.*?)<\/h[23]>/', function ($heading) use (&$counter) {
            $counter++;
            $text = wp_strip_all_tags($heading[3]);
            $anchor = sanitize_title($text) . '-' . $counter;

            return '<h' . $heading[1] . $heading[2] . ' id="' . esc_attr($anchor) . '">' . $heading[3] . '</h' . $heading[1] . '>';
        }, $content);

        return $toc . $content;
    }

    /**
     * Vo hieu hoa block editor neu duoc yeu cau.
     */
    public function maybe_disable_block_editor($use_block_editor, $post_type)
    {
        if ($this->get_setting('disable_block_editor', false)) {
            return false;
        }

        return $use_block_editor;
    }

    /**
     * Tu dong gan featured image neu bai viet chua co thumbnail.
     */
    public function maybe_set_auto_featured_image($post_id, $post, $update)
    {
        if (!$this->get_setting('auto_featured_image', false)) {
            return;
        }

        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        if (!empty(get_post_thumbnail_id($post_id))) {
            return;
        }

        $content = (string) $post->post_content;
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches) !== 1) {
            return;
        }

        $image_url = esc_url_raw($matches[1]);
        $attachment_id = attachment_url_to_postid($image_url);
        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    /**
     * Mo rong cac nut TinyMCE.
     */
    public function extend_tinymce_buttons($buttons)
    {
        if (!$this->get_setting('enable_tinymce_advanced', true)) {
            return $buttons;
        }

        $extra = ['fontselect', 'fontsizeselect', 'styleselect', 'code'];

        return array_values(array_unique(array_merge($buttons, $extra)));
    }

    /**
     * Bo sung them mot so nut co ban cho editor.
     */
    public function extend_tinymce_buttons_2($buttons)
    {
        if (!$this->get_setting('enable_tinymce_advanced', true)) {
            return $buttons;
        }

        $extra = ['table', 'hr', 'forecolor', 'backcolor'];

        return array_values(array_unique(array_merge($buttons, $extra)));
    }

    /**
     * Freeze plugin updates neu option da bat.
     */
    public function maybe_freeze_plugin_updates($transient)
    {
        if (!$this->get_setting('block_plugin_updates', false) || !is_object($transient)) {
            return $transient;
        }

        $transient->response = [];
        $transient->no_update = $transient->no_update ?? [];

        return $transient;
    }

    /**
     * Freeze theme updates neu option da bat.
     */
    public function maybe_freeze_theme_updates($transient)
    {
        if (!$this->get_setting('block_theme_updates', false) || !is_object($transient)) {
            return $transient;
        }

        $transient->response = [];
        $transient->no_update = $transient->no_update ?? [];

        return $transient;
    }

    /**
     * Freeze core updates neu option da bat.
     */
    public function maybe_freeze_core_updates($value)
    {
        if (!$this->get_setting('block_core_updates', false)) {
            return $value;
        }

        if (is_object($value)) {
            $value->updates = [];
        }

        return $value;
    }

    /**
     * Lay option chung.
     */
    private function get_setting($key, $default = false)
    {
        $settings = get_option('wps_main_settings', []);

        return $settings[$key] ?? $default;
    }

    /**
     * Danh sách post type public cho mục lục tự động.
     */
    private function get_toc_post_types()
    {
        $post_types = get_post_types(['public' => true], 'names');
        $post_types = is_array($post_types) ? array_keys($post_types) : [];
        $excluded = ['attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request'];

        return array_values(array_diff($post_types, $excluded));
    }
}

// Copyright by AcmaTvirus
