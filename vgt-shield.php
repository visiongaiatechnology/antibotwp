<?php
/**
 * Plugin Name: VGT Shield - Anti-Bot Intelligence
 * Description: Ultra-High-End Anti-Bot Alternative. Zero-UI, Proof-of-Work Engine.
 * Version: 2.0.0
 * Author: VISIONGAIATECHNOLOGY
 * Author URI:  https://visiongaiatechnology.de
 * License:     AGPLv3
 * License URI: https://www.gnu.org/licenses/agpl-3.0.html
 * Text Domain: vgt-myrmidon
 */

if (!defined('ABSPATH')) exit;

define('VGT_SHIELD_PATH', plugin_dir_path(__FILE__));
define('VGT_SHIELD_URL', plugin_dir_url(__FILE__));

// Architektur-Load
require_once VGT_SHIELD_PATH . 'inc/class-vgt-security.php';
require_once VGT_SHIELD_PATH . 'inc/class-vgt-core.php';
require_once VGT_SHIELD_PATH . 'inc/class-vgt-admin.php';
require_once VGT_SHIELD_PATH . 'inc/class-vgt-scanner.php';

// Initialisierung der Systeme
add_action('plugins_loaded', ['VGT_Core', 'init']);

if (is_admin()) {
    add_action('plugins_loaded', ['VGT_Admin', 'init']);
}