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


// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'site_assistant_activate');
register_deactivation_hook(__FILE__, 'site_assistant_deactivate');

/**
 * Activation callback to create custom table for storing conversations.
 */
function site_assistant_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'site_assistant_conversations';
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        conversation LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY created_at (created_at)
    ) $charset_collate;";
    dbDelta($sql);
}

/**
 * Deactivation callback. Currently retains the custom table for future use.
 */
function site_assistant_deactivate() {
    // Optionally clean up resources on deactivation.
}
