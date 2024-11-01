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
class Woo_Return_Exchange_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 * Install database table and other dependencies
	 *
	 * @since    1.1.0
	 */
	public static function activate() {

		global $wpdb;
		$queries         = array();
		$charset_collate = $wpdb->get_charset_collate();

		// Return Exchange
		array_push(
			$queries,
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wp_return_exchange(
			 	`return_exchange_id` bigint(20) NOT NULL AUTO_INCREMENT,
				`order_id` bigint(20) NOT NULL,
				`product_id` bigint(20) NOT NULL,
				`is_return_or_exchange` enum('Return','Exchange') COLLATE utf8mb4_unicode_ci NOT NULL,
				`return_or_exchange_quantity` int(5) NOT NULL,
				`return_exchange_reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
				`comments` text COLLATE utf8mb4_unicode_ci NOT NULL,
				`request_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
				`store_owner_approval_status` enum('Approved','Rejected') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`store_owner_approval_status_date` datetime DEFAULT NULL,
				`order_authorization_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`is_item_received` tinyint(1) NOT NULL DEFAULT 0,
				`is_item_refunded` tinyint(1) NOT NULL DEFAULT 0,
				`is_item_exchanged` tinyint(1) DEFAULT 0,
				`store_owner_notes_private` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`return_exchange_status` enum('Requested','Approved','Rejected','Completed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`reject_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`mark_completed_by` bigint(20) NOT NULL,
				`final_status_date` datetime DEFAULT NULL,
				PRIMARY KEY (return_exchange_id)
		  	) $charset_collate;"
		);

		// Request/Return Reasons
		array_push(
			$queries,
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wp_return_exchange_reason(
			 	`reason_id` bigint(20) NOT NULL AUTO_INCREMENT,
				`reason_option` varchar(255) NOT NULL,
				`reason_value` varchar(255) NOT NULL,
				PRIMARY KEY (reason_id)
			) $charset_collate;"
		);

		// Configuration Settings Table
		array_push(
			$queries,
			"CREATE TABLE {$wpdb->prefix}wp_return_exchange_configuration(
				`config_id` bigint(20) NOT NULL AUTO_INCREMENT,
				`email_from` varchar(255) NOT NULL,
				`return_address_msg` text NOT NULL,
				`return_rejected_msg` text NOT NULL,
				`return_requested_msg` text NOT NULL,
				`return_completed_msg` text NOT NULL,
				PRIMARY KEY (config_id)
			  ) $charset_collate;"
		);

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $queries as $key => $sql ) {
			dbDelta( $sql );
		}

		// Insert Data in database table wp_return_exchange_reason
		$wpdb->replace(
			$wpdb->prefix . 'wp_return_exchange_reason',
			array(
				'reason_id'     => 1,
				'reason_option' => 'Too Small',
				'reason_value'  => 'too-small',
			)
		);
		$wpdb->replace(
			$wpdb->prefix . 'wp_return_exchange_reason',
			array(
				'reason_id'     => 2,
				'reason_option' => 'Too Big',
				'reason_value'  => 'too-big',
			)
		);
		$wpdb->replace(
			$wpdb->prefix . 'wp_return_exchange_reason',
			array(
				'reason_id'     => 3,
				'reason_option' => 'Material is not expected',
				'reason_value'  => 'material-is-not-expected',
			)
		);
		$wpdb->replace(
			$wpdb->prefix . 'wp_return_exchange_reason',
			array(
				'reason_id'     => 4,
				'reason_option' => 'It doesn’t fit properly',
				'reason_value'  => 'it-does-not-fit-properly',
			)
		);
		$wpdb->replace(
			$wpdb->prefix . 'wp_return_exchange_reason',
			array(
				'reason_id'     => 5,
				'reason_option' => 'Model/version/color is not expected',
				'reason_value'  => 'model-version-color-is-not-expected',
			)
		);
		$wpdb->replace(
			$wpdb->prefix . 'wp_return_exchange_reason',
			array(
				'reason_id'     => 6,
				'reason_option' => 'Product is defective',
				'reason_value'  => 'product-is-defective',
			)
		);

		// Insert Data in database table wp_return_exchange_reason
		$wpdb->replace(
			$wpdb->prefix . 'wp_return_exchange_configuration',
			array(
				'email_from'           => 'support@sovratec.com',
				'return_address_msg'   => '<p>Your return/exchange has been approved.&nbsp; Please send return or exchange package to following address:</p><p><strong>Mystore Return center</strong></p><p><strong>123 Main St.</strong></p><p><strong>New York, NY 23456</strong></p>',
				'return_rejected_msg'  => '<p>Your return/exchange has been disapproved becuase the return/exchange must be within 30 days from delivery date.&nbsp;</p><p>Should you have any questions, please contact us</p><p>Thank you</p>',
				'return_requested_msg' => '<p>Hello, we have received your return/exchange request.&nbsp; We will send you further notification on next step.&nbsp;</p><p>Thank you</p><p>support @mystore.com</p>',
				'return_completed_msg' => '<p>Hello, your return/exchange has been completed.&nbsp; Thank you for shopping with us.&nbsp;</p><p>Have a great day!</p><p>support -mystore.com</p>',
			)
		);
		// Create plugin pages on activation
		$check_page_exist = get_page_by_title( 'Return Exchange Client View', 'OBJECT', 'page' );
		// Check if the page already exists
		if ( empty( $check_page_exist ) ) {
			$page_id = wp_insert_post(
				array(
					'comment_status' => 'close',
					'ping_status'    => 'close',
					'post_author'    => get_current_user_id(),
					'post_title'     => ucwords( 'Return Exchange Client View' ),
					'post_name'      => strtolower( str_replace( ' ', '-', trim( 'Return Exchange Client View' ) ) ),
					'post_status'    => 'publish',
					'post_content'   => '[client_view]',
					'post_type'      => 'page',
					'post_parent'    => '',
				)
			);
		} else {
			// Make sure page only contains shortcode nothing else
			$existing_page_content = array(
				'ID'           => $check_page_exist->ID,
				'post_title'   => 'Return Exchange Client View',
				'post_content' => '[client_view]',
			);
			$page_id               = wp_update_post( $existing_page_content );
		}
	}
}
