<?php
if (!defined('ABSPATH')) {
    exit;
}

class Site_Assistant {
    const OPTION_NAME = 'site_assistant_options';

    public function init() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function register_admin_menu() {
        add_options_page(
            __('Site Assistant', 'site-assistant'),
            __('Site Assistant', 'site-assistant'),
            'manage_options',
            'site-assistant',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
        echo '<form action="options.php" method="post">';
        settings_fields('site_assistant');
        do_settings_sections('site-assistant');
        submit_button(__('Save Changes', 'site-assistant'));
        echo '</form>';
        echo '</div>';
    }

    public function register_settings() {
        register_setting('site_assistant', self::OPTION_NAME, [
            'sanitize_callback' => [$this, 'sanitize_options'],
        ]);

        add_settings_section(
            'site_assistant_general_section',
            __('General Settings', 'site-assistant'),
            null,
            'site-assistant'
        );

        add_settings_field(
            'site_assistant_admin_email',
            __('Admin Email', 'site-assistant'),
            [$this, 'render_email_field'],
            'site-assistant',
            'site_assistant_general_section'
        );
        add_settings_field(
            'site_assistant_primary_colour',
            __('Primary Colour', 'site-assistant'),
            [$this, 'render_primary_colour_field'],
            'site-assistant',
            'site_assistant_general_section'
        );
        add_settings_field(
            'site_assistant_accent_colour',
            __('Accent Colour', 'site-assistant'),
            [$this, 'render_accent_colour_field'],
            'site-assistant',
            'site_assistant_general_section'
        );
    }

    public function sanitize_options($options) {
        $sanitized = [];
        $sanitized['admin_email']    = isset($options['admin_email']) ? sanitize_email($options['admin_email']) : '';
        $sanitized['primary_colour'] = isset($options['primary_colour']) ? sanitize_hex_color($options['primary_colour']) : '#0055aa';
        $sanitized['accent_colour']  = isset($options['accent_colour']) ? sanitize_hex_color($options['accent_colour']) : '#ffaa00';
        return $sanitized;
    }

    public function render_email_field() {
        $options = get_option(self::OPTION_NAME);
        $email   = isset($options['admin_email']) ? esc_attr($options['admin_email']) : get_option('admin_email');
        echo '<input type="email" name="' . self::OPTION_NAME . '[admin_email]" value="' . $email . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('Email notifications for voice messages will be sent here.', 'site-assistant') . '</p>';
    }

    public function render_primary_colour_field() {
        $options = get_option(self::OPTION_NAME);
        $primary = isset($options['primary_colour']) ? esc_attr($options['primary_colour']) : '#0055aa';
        echo '<input type="text" name="' . self::OPTION_NAME . '[primary_colour]" value="' . $primary . '" class="regular-text site-assistant-colour-field" data-default-color="#0055aa" />';
        echo '<p class="description">' . esc_html__('Primary colour used for the chat bubble and modal background.', 'site-assistant') . '</p>';
    }

    public function render_accent_colour_field() {
        $options = get_option(self::OPTION_NAME);
        $accent  = isset($options['accent_colour']) ? esc_attr($options['accent_colour']) : '#ffaa00';
        echo '<input type="text" name="' . self::OPTION_NAME . '[accent_colour]" value="' . $accent . '" class="regular-text site-assistant-colour-field" data-default-color="#ffaa00" />';
        echo '<p class="description">' . esc_html__('Accent colour used for buttons and highlights.', 'site-assistant') . '</p>';
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'site-assistant-style',
            SITE_ASSISTANT_PLUGIN_URL . 'css/site-assistant.css',
            [],
            SITE_ASSISTANT_VERSION
        );
        wp_enqueue_script(
            'site-assistant-script',
            SITE_ASSISTANT_PLUGIN_URL . 'js/site-assistant.js',
            ['wp-api'],
            SITE_ASSISTANT_VERSION,
            true
        );
        $options = get_option(self::OPTION_NAME);
        $data    = [
            'restUrl'       => esc_url_raw(rest_url('site-assistant/v1/')),
            'nonce'         => wp_create_nonce('wp_rest'),
            'primaryColour' => isset($options['primary_colour']) ? $options['primary_colour'] : '#0055aa',
            'accentColour'  => isset($options['accent_colour']) ? $options['accent_colour'] : '#ffaa00',
            'adminEmail'    => isset($options['admin_email']) ? $options['admin_email'] : get_option('admin_email'),
        ];
        wp_localize_script('site-assistant-script', 'SiteAssistant', $data);
    }

    public function register_rest_routes() {
        register_rest_route(
            'site-assistant/v1',
            '/chat',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'handle_chat'],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            'site-assistant/v1',
            '/voice',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'handle_voice'],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            'site-assistant/v1',
            '/stats',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'handle_stats'],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            'site-assistant/v1',
            '/save_conversation',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'handle_save_conversation'],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            'site-assistant/v1',
            '/analyze_conversation',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'handle_analyze_conversation'],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            'site-assistant/v1',
            '/analyze_all_conversations',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'handle_analyze_all_conversations'],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function handle_chat(WP_REST_Request $request) {
        $params = $request->get_json_params();
        if (empty($params['messages']) || !is_array($params['messages'])) {
            return new WP_REST_Response(['error' => __('Invalid request payload.', 'site-assistant')], 400);
        }
        $payload  = [
            'maxTokens' => 16384,
            'messages'  => $params['messages'],
        ];
        $response = wp_remote_post('https://ominisender.com/wp-json/azurechat/v1/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body'    => wp_json_encode($payload),
            'timeout' => 30,
        ]);
        if (is_wp_error($response)) {
            return new WP_REST_Response(['error' => $response->get_error_message()], 500);
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_REST_Response(['error' => __('Invalid response from assistant.', 'site-assistant')], 502);
        }
        return new WP_REST_Response($data, 200);
    }

    public function handle_voice(WP_REST_Request $request) {
        if (!isset($request['recording']) || empty($request['recording'])) {
            return new WP_REST_Response(['error' => __('No recording provided.', 'site-assistant')], 400);
        }
        $audio_data = $request['recording'];
        if (strpos($audio_data, 'data:audio') !== 0) {
            return new WP_REST_Response(['error' => __('Invalid audio format.', 'site-assistant')], 400);
        }
        $parts = explode(',', $audio_data);
        $data  = base64_decode(end($parts));
        if (!$data) {
            return new WP_REST_Response(['error' => __('Failed to decode audio.', 'site-assistant')], 400);
        }
        $upload = wp_upload_dir();
        $dir    = trailingslashit($upload['basedir']) . 'site-assistant';
        if (!wp_mkdir_p($dir)) {
            return new WP_REST_Response(['error' => __('Unable to create upload directory.', 'site-assistant')], 500);
        }
        $filename = 'recording-' . time() . '.webm';
        $filepath = $dir . '/' . $filename;
        if (!file_put_contents($filepath, $data)) {
            return new WP_REST_Response(['error' => __('Unable to save recording.', 'site-assistant')], 500);
        }
        $options     = get_option(self::OPTION_NAME);
        $admin_email = isset($options['admin_email']) ? $options['admin_email'] : get_option('admin_email');
        $upload_url  = trailingslashit($upload['baseurl']) . 'site-assistant/' . $filename;
        wp_mail(
            $admin_email,
            __('New Voice Message', 'site-assistant'),
            sprintf(__('A new voice message has been received. You can download it here: %s', 'site-assistant'), $upload_url)
        );
        return new WP_REST_Response(['success' => true, 'url' => $upload_url], 200);
    }

    public function handle_stats(WP_REST_Request $request) {
        return new WP_REST_Response(['success' => true], 200);
    }

    public function handle_save_conversation(WP_REST_Request $request) {
        $params = $request->get_json_params();
        if (empty($params['conversation']) || !is_array($params['conversation'])) {
            return new WP_REST_Response(['error' => __('Invalid conversation data.', 'site-assistant')], 400);
        }
        $conversations   = get_option('site_assistant_conversations', []);
        $conversations[] = [
            'date'         => current_time('mysql'),
            'conversation' => array_slice($params['conversation'], 0, 20),
        ];
        update_option('site_assistant_conversations', $conversations);
        return new WP_REST_Response(['success' => true], 200);
    }

    public function handle_analyze_conversation(WP_REST_Request $request) {
        $params = $request->get_json_params();
        if (empty($params['conversation']) || !is_array($params['conversation'])) {
            return new WP_REST_Response(['error' => __('Invalid conversation data.', 'site-assistant')], 400);
        }
        $payload  = [
            'maxTokens' => 2048,
            'messages'  => [
                [
                    'role'    => 'system',
                    'content' => 'Summarise the following conversation.',
                ],
                [
                    'role'    => 'user',
                    'content' => wp_json_encode($params['conversation']),
                ],
            ],
        ];
        $response = wp_remote_post('https://ominisender.com/wp-json/azurechat/v1/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body'    => wp_json_encode($payload),
            'timeout' => 30,
        ]);
        if (is_wp_error($response)) {
            return new WP_REST_Response(['error' => $response->get_error_message()], 500);
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_REST_Response(['error' => __('Invalid response from assistant.', 'site-assistant')], 502);
        }
        return new WP_REST_Response($data, 200);
    }

    public function handle_analyze_all_conversations(WP_REST_Request $request) {
        $conversations = get_option('site_assistant_conversations', []);
        if (empty($conversations)) {
            return new WP_REST_Response(['error' => __('No conversations available.', 'site-assistant')], 404);
        }
        $all_text = '';
        foreach ($conversations as $conv) {
            $all_text .= wp_json_encode($conv['conversation']) . "\n\n";
        }
        $payload  = [
            'maxTokens' => 4096,
            'messages'  => [
                [
                    'role'    => 'system',
                    'content' => 'Provide a high-level summary and common themes for the following conversations.',
                ],
                [
                    'role'    => 'user',
                    'content' => $all_text,
                ],
            ],
        ];
        $response = wp_remote_post('https://ominisender.com/wp-json/azurechat/v1/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body'    => wp_json_encode($payload),
            'timeout' => 30,
        ]);
        if (is_wp_error($response)) {
            return new WP_REST_Response(['error' => $response->get_error_message()], 500);
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_REST_Response(['error' => __('Invalid response from assistant.', 'site-assistant')], 502);
        }
        return new WP_REST_Response($data, 200);
    }
}
