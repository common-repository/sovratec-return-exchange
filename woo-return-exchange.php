<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sovratec.com/
 * @since             1.1.0
 * @package           Sov_Return_Exchange
 *
 * @wordpress-plugin
 * Plugin Name:       Sovratec Return & Exchange
 * Plugin URI:        https://sovratec.com/plugins/return-exchange
 * Description:       Manage orders return and exchange for clients
 * Version:           1.1.0
 * Author:            Sovratec
 * Author URI:        https://sovratec.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sovratec-return-exchange
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.1.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WREX_RETURN_EXCHANGE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-return-exchange-activator.php
 */
function WREX_activate_woo_return_exchange() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-return-exchange-activator.php';
	Woo_Return_Exchange_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function WREX_deactivate_woo_return_exchange() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-return-exchange-deactivator.php';
	Woo_Return_Exchange_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'WREX_activate_woo_return_exchange' );
register_deactivation_hook( __FILE__, 'WREX_deactivate_woo_return_exchange' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-return-exchange.php';

/**
 * Create the shortcode to print the map
 */
function WREX_clientview_shortcode( $atts ) {
	ob_start();
	require plugin_dir_path( __FILE__ ) . 'public/partials/woo-return-exchange-public-display.php';
	return ob_get_clean();
}
add_shortcode( 'client_view', 'WREX_clientview_shortcode' );

// Add link to woocommerce account page
function WREX_add_plugin_link( $menu_links ) {
	$return_exchange_link = strtolower( str_replace( ' ', '-', trim( 'Return Exchange Client View' ) ) );
	$new                  = array( $return_exchange_link => 'Return & Exchange' );
	$menu_links           = array_slice( $menu_links, 0, 1, true )
	+ $new
	+ array_slice( $menu_links, 1, null, true );
	return $menu_links;
}
add_filter( 'woocommerce_account_menu_items', 'WREX_add_plugin_link' );

// Second Filter to Redirect the WooCommerce endpoint to custom URL
function WREX_plugin_hook_endpoint( $url, $endpoint, $value, $permalink ) {
	$return_exchange_link = strtolower( str_replace( ' ', '-', trim( 'Return Exchange Client View' ) ) );
	if ( $endpoint === $return_exchange_link ) {
		// Add return/exchange url
		$url = site_url() . '/' . $return_exchange_link . '/';
	}
	return $url;
}
add_filter( 'woocommerce_get_endpoint_url', 'WREX_plugin_hook_endpoint', 10, 4 );

// allow widget text to run shortcodes
add_filter( 'widget_text', 'do_shortcode' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.1.0
 */
function WREX_run_woo_return_exchange() {
	$plugin = new Woo_Return_Exchange();
	$plugin->run();
}
WREX_run_woo_return_exchange();
