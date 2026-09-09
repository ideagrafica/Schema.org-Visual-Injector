<?php
/**
 * Plugin Name:       Schema.org Visual Injector
 * Plugin URI:        https://www.incod.it/schema-visual-injector/
 * Description:       Visual JSON-LD Schema.org injector for Posts, Pages, WooCommerce Products, and Custom Post Types with dynamic field mapping.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Marco De Sangro (inCod)
 * Author URI:        https://www.incod.it/schema-visual-injector/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       schema-org-visual-injector
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SOVI_VERSION', '1.0.0' );
define( 'SOVI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SOVI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SOVI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once SOVI_PLUGIN_DIR . 'includes/Class-Autoloader.php';

/**
 * Initialize the plugin.
 *
 * Registers the autoloader and boots the Core singleton.
 *
 * @return void
 */
function sovi_init_plugin() {
	\Schema_Org_Visual_Injector\Autoloader::register();
	\Schema_Org_Visual_Injector\Core::get_instance();
}
add_action( 'plugins_loaded', 'sovi_init_plugin' );

/**
 * Activation hook — flush rewrite rules for any future CPT needs.
 *
 * @return void
 */
function sovi_activate() {
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'sovi_activate' );

/**
 * Deactivation hook — flush rewrite rules.
 *
 * @return void
 */
function sovi_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'sovi_deactivate' );
