<?php
/**
 * Plugin Name: Site Assistant
 * Plugin URI: https://example.com/site-assistant
 * Description: Adds a floating AI assistant and voice message recorder to every page. Provides an admin settings page and REST endpoints to manage conversations, voice recordings, and analytics.
 * Version: 0.1.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL2
 * Text Domain: site-assistant
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('SITE_ASSISTANT_VERSION', '0.1.0');
define('SITE_ASSISTANT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SITE_ASSISTANT_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once SITE_ASSISTANT_PLUGIN_DIR . 'includes/class-site-assistant.php';

function site_assistant_init() {
    $plugin = new Site_Assistant();
    $plugin->init();
}
add_action('plugins_loaded', 'site_assistant_init');
