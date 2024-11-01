<?php

/**
 * Collection of all functions and dependencies
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://sovratec.com/
 * @since      1.1.0
 *
 * @package    Sov_Return_Exchange
 * @subpackage Sov_Return_Exchange/public/partials
 */

?>
<?php

function return_exchange_order_data( $orderIDargs = null ) {

	global $wpdb, $woocommerce;
	$data = array();
	if ( $orderIDargs == null ) {
		// Get all customer orders
		$customer_orders = get_posts(
			array(
				'numberposts' => - 1,
				'meta_key'    => '_customer_user',
				'orderby'     => 'date',
				'order'       => 'DESC',
				'meta_value'  => get_current_user_id(),
				'post_type'   => wc_get_order_types(),
				'post_status' => array_keys( wc_get_order_statuses() ),
				'post_status' => array(
					'wc-completed',
				),
			)
		);
	} else {
		// Get all customer orders
		$customer_orders = get_posts(
			array(
				'numberposts' => - 1,
				'meta_key'    => '_customer_user',
				'orderby'     => 'date',
				'order'       => 'DESC',
				'meta_value'  => get_current_user_id(),
				'post_type'   => wc_get_order_types(),
				'post_status' => array_keys( wc_get_order_statuses() ),
				'post_status' => array(
					'wc-completed',
				),
				'post__in'    => array( $orderIDargs ),
			)
		);
	}
	foreach ( $customer_orders as $customer_order ) {
		$quantity = 0;
		$orderq   = wc_get_order( $customer_order );
		// Product QTY in a specific order
		foreach ( $orderq->get_items() as $item_id => $item ) {
			$quantity += 1;
		}

		$orderID        = $orderq->get_id();
		$orderDate      = $orderq->get_date_created()
		->date_i18n( 'M d, Y' );
		$orderQTY       = 'Total ' . $quantity . ' items - $' . $orderq->get_total() . ' USD';
		$shippedTo      = formatted_shipping_address( $orderq );
		$billingTo      = formatted_billing_address( $orderq );
		$orderStatus    = ucfirst( $orderq->get_status() );
		$user_id        = $orderq->get_user_id();
		$order_user     = get_userdata( $user_id );
		$user_firstName = $order_user->first_name;

		$data[] = array(
			'orderID'     => $orderID,
			'orderDate'   => $orderDate,
			'orderQTY'    => $orderQTY,
			'shippedTo'   => $shippedTo,
			'billingTo'   => $billingTo,
			'orderStatus' => $orderStatus,
			'username'    => $user_firstName,
		);
	}

	$response['data']         = ! empty( $data ) ? $data : array();
	$response['recordsTotal'] = ! empty( $data ) ? count( $data ) : 0;

	return $response;
}

function formatted_shipping_address( $order ) {
	return $order->shipping_address_1 . ', ' .
	$order->shipping_address_2 . ' <br/>' .
	$order->shipping_city . ', ' .
	$order->shipping_state . ' <br/>' .
	$order->shipping_postcode;
}

function formatted_billing_address( $order ) {
	return $order->billing_address_1 . ', ' .
	$order->billing_address_2 . ' <br/>' .
	$order->billing_city . ', ' .
	$order->billing_state . ' <br/>' .
	$order->billing_postcode;
}

// Save form data in database
function save_order_data( $data = null ) {

	global $wpdb;
	$flag = 0;

	for ( $count = 0; $count < count( $data['product_id_hidden'] ); $count++ ) {
		if ( $data['returnqty'][ $count ] == 0 ) {
			$flag++;
			continue;
		}
		$wpdb->insert(
			$wpdb->prefix . 'wp_return_exchange',
			array(
				'order_id'                    => $data['order_id'],
				'product_id'                  => $data['product_id_hidden'][ $count ],
				'is_return_or_exchange'       => ucfirst( $data['returnProduct'][ $count ] ),
				'return_or_exchange_quantity' => $data['returnqty'][ $count ],
				'return_exchange_reason'      => $data['reason_returnProduct'][ $count ],
				'comments'                    => $data['customer_comments'][ $count ],
			)
		);

		if ( $wpdb->insert_id ) {
			$flag++;
		} else {
			$flag = 0;
		}
	}
	if ( $flag == count( $data['product_id_hidden'] ) ) {
		sendEmailToUser( 'New Return' );
	}
	return ( $flag == count( $data['product_id_hidden'] ) ) ? 1 : 0;
}

function return_exchange_reason() {
	 global $wpdb;
	$reasonArray = $wpdb->get_results(
		$wpdb->prepare( "SELECT reason_option, reason_value FROM {$wpdb->prefix}wp_return_exchange_reason" )
	);
	 return $reasonArray;
}

function is_return_request_exist( $orderID ) {
	global $wpdb;
	$orderCountAndData = $wpdb->get_var( "SELECT COUNT(*) order_no FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID" );
	return $orderCountAndData;
}

function sendEmailToUser( $action = null ) {

	global $wpdb;
	$configArray = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wp_return_exchange_configuration LIMIT 1" )
	);

	foreach ( $configArray as $configSetting ) {
		// Send email to user with predefined email content from db
		$current_user = wp_get_current_user();
		$to           = $current_user->user_email;

		switch ( $action ) {
			case 'New Return':
				if ( ! empty( $configSetting->return_requested_msg ) ) {
					$headers[] = isset( $configSetting->email_from ) ? 'From: Support <' . $configSetting->email_from . '>' : 'From: Sovratec Support <imessanger@sovratec.com>';
					$headers[] = 'Content-Type: text/html; charset=UTF-8';
					$subject   = 'Request for Return/Exchange received';
					$body      = isset( $configSetting->return_requested_msg ) ? $configSetting->return_requested_msg : '<p>Hello, we have received your return/exchange request.&nbsp; We will send you further notification on next step.&nbsp;</p><p>Thank you</p><p>support @mystore.com</p>';
					$body     .= '<br/>Thank You!';
				}
				break;
		}
	}
	$emailStatus = wp_mail( $to, $subject, $body, $headers );

	return $emailStatus;
}
