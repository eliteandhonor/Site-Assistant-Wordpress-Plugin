<?php
class Site_Assistant_Tests extends WP_UnitTestCase {
    public function test_rest_routes_registered() {
        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();
        do_action('rest_api_init');
        $routes = $wp_rest_server->get_routes();
        $this->assertArrayHasKey('/site-assistant/v1/chat', $routes);
        $this->assertArrayHasKey('/site-assistant/v1/voice', $routes);
        $this->assertArrayHasKey('/site-assistant/v1/stats', $routes);
        $this->assertArrayHasKey('/site-assistant/v1/save_conversation', $routes);
    }
    public function test_sanitize_options() {
        $plugin = new Site_Assistant();
        $input  = [
            'admin_email'    => 'invalid-email',
            'primary_colour' => 'not-a-colour',
            'accent_colour'  => '#12ab34',
        ];
        $sanitized = $plugin->sanitize_options($input);
        $this->assertEquals('', $sanitized['admin_email']);
        $this->assertEquals('#0055aa', $sanitized['primary_colour']);
        $this->assertEquals('#12ab34', $sanitized['accent_colour']);
    }
}
