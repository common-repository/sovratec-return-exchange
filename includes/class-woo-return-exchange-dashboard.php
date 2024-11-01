<?php

/**
 * Fired during plugin activation
 *
 * @link       https://sovratec.com/
 * @since      1.1.0
 *
 * @package    Sov_Return_Exchange
 * @subpackage Sov_Return_Exchange/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.1.0
 * @package    Sov_Return_Exchange
 * @subpackage Sov_Return_Exchange/includes
 * @author     Sovratec <https://sovratec.com/>
 */

class Woo_Return_Exchange_Dashboard {

	function __construct() {
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/return-exchange-admin-tabs/class-woo-return-exchange-tabs.php';
	}
}
$wp_list_table = new Woo_Return_Exchange_Dashboard();
