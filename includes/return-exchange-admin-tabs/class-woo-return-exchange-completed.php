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
if ( ! class_exists( 'Woo_Return_Exchange_Completed' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Woo_Return_Exchange_Completed extends WP_List_Table {

	function __construct() {
		global $status, $page;
		parent::__construct(
			array(
				'singular' => 'order',
				'plural'   => 'orders',
				'ajax'     => false,
			)
		);
	}

	function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'orderID'        => __( 'Order ID', 'woo-return-exchange' ),
			'orderDate'      => __( 'Order Date', 'woo-return-exchange' ),
			'approvalStatus' => __( 'Approval Status', 'woo-return-exchange' ),
			'requestedDate'  => __( 'Requested Date', 'woo-return-exchange' ),
			'completedDate'  => __( 'Completed Date', 'woo-return-exchange' ),
			'orderQTY'       => __( 'Products QTY', 'woo-return-exchange' ),
			'shippedTo'      => __( 'Shipping Address', 'woo-return-exchange' ),
			'actions'        => __( 'Actions', 'woo-return-exchange' ),
		);
		return $columns;
	}

	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'orderID'        => array(
				'orderID',
				true,
			),
			'orderDate'      => array(
				'orderDate',
				true,
			),
			'orderQTY'       => array(
				'orderQTY',
				true,
			),
			'shippedTo'      => array(
				'shippedTo',
				true,
			),
			'requestedDate'  => array(
				'requestedDate',
				true,
			),
			'completedDate'  => array(
				'completedDate',
				true,
			),
			'approvalStatus' => array(
				'approvalStatus',
				true,
			),
		);
		return $sortable_columns;
	}

	public function get_hidden_columns() {
		// Setup Hidden columns and return them
		return array();
	}

	function column_cb( $item ) {

		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['ID'] );
	}

	function column_orderID( $item ) {
		// Build row actions
		$actions = array(
			'view' => sprintf( '<a href="#" data-order_id="%s"  class="order-item-view" data-bs-toggle="modal" data-bs-target="#myModal_%s" >%s</a>', $item['orderID'], $item['orderID'], __( 'View Details', 'woo-return-exchange' ) ),
		);

		// Return the title contents
		return sprintf(
			'<span style="color:#555;">(#: %1$s)</span>%2$s', /*$1%s*/
			$item['orderID'] . ' ' . $item['username'], /*$2%s*/
			$this->row_actions( $actions )
		);
	}
	private function table_data() {
		global $wpdb;
		$data = array();
		if ( isset( $_GET['s'] ) ) {
			$search          = sanitize_text_field( wp_unslash( $_GET['s'] ) );
			$search          = trim( $search );
			$customer_orders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wp_return_exchange WHERE store_owner_approval_status IS NOT NULL AND return_exchange_status = 'Completed' AND order_id = $search LIMIT 1" ) );
		} else {
			$customer_orders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wp_return_exchange WHERE store_owner_approval_status IS NOT NULL AND return_exchange_status = 'Completed' GROUP BY order_id ORDER BY final_status_date DESC" ) );
		}
		foreach ( $customer_orders as $customer_order ) {
			$orderq   = wc_get_order( $customer_order->order_id );
			$quantity = 0;
			// Product QTY in a specific order
			foreach ( $orderq->get_items() as $item_id => $item ) {
				$quantity += 1;
			}

			$orderID           = $orderq->get_id();
			$orderDate         = $orderq->get_date_created()->date_i18n( 'M d, Y H:i' );
			$orderReturnDate   = date( 'M d, Y H:i', strtotime( $customer_order->request_time ) );
			$orderCompleteDate = date( 'M d, Y H:i', strtotime( $customer_order->final_status_date ) );
			$orderQTY          = 'Total ' . $quantity . ' items - $' . $orderq->get_total() . ' USD';
			$shippedTo         = $this->formatted_shipping_address( $orderq );
			$billingTo         = $this->formatted_billing_address( $orderq );
			$orderStatus       = ucfirst( $orderq->get_status() );
			$user_id           = $orderq->get_user_id();
			$order_user        = get_userdata( $user_id );
			$user_firstName    = $order_user->first_name;
			$orderRowAction    = '<a href="#" id="return-exchange-details" data-bs-toggle="modal" data-bs-target="#myModal_' . $orderID . '" class="button button-primary button-large">Order Details</a>';
			$data[]            = array(
				'orderID'        => $orderID,
				'orderDate'      => $orderDate,
				'orderQTY'       => $orderQTY,
				'shippedTo'      => $shippedTo,
				'approvalStatus' => $customer_order->store_owner_approval_status,
				'requestedDate'  => $orderReturnDate,
				'completedDate'  => $orderCompleteDate,
				'username'       => $user_firstName,
				'actions'        => $orderRowAction,
			);
		}
		return $data;
	}
	// Function to return popup header information
	public function orderDetailsData( $orderID ) {
		global $wpdb;
		$data            = array();
		$customer_orders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wp_return_exchange WHERE store_owner_approval_status IS NOT NULL AND return_exchange_status = 'Completed' AND order_id = $orderID LIMIT 1" ) );
		foreach ( $customer_orders as $customer_order ) {
			$orderq   = wc_get_order( $customer_order->order_id );
			$quantity = 0;
			// Product QTY in a specific order
			foreach ( $orderq->get_items() as $item_id => $item ) {
				$quantity += 1;
			}

			$orderID           = $orderq->get_id();
			$orderDate         = $orderq->get_date_created()->date_i18n( 'M d, Y H:i' );
			$orderReturnDate   = date( 'M d, Y H:i', strtotime( $customer_order->request_time ) );
			$orderApprovedDate = date( 'M d, Y H:i', strtotime( $customer_order->store_owner_approval_status_date ) );
			$orderQTY          = 'Total ' . $quantity . ' items - $' . $orderq->get_total() . ' USD';
			$shippedTo         = $this->formatted_shipping_address( $orderq );
			$billingTo         = $this->formatted_billing_address( $orderq );
			$orderStatus       = ucfirst( $orderq->get_status() );
			$user_id           = $orderq->get_user_id();
			$order_user        = get_userdata( $user_id );
			$user_firstName    = $order_user->first_name;
			$data[]            = array(
				'orderID'       => $orderID,
				'orderDate'     => $orderDate,
				'orderQTY'      => $orderQTY,
				'shippedTo'     => $shippedTo,
				'requestedDate' => $orderReturnDate,
				'approvedDate'  => $orderApprovedDate,
				'username'      => $user_firstName,
			);
		}
		return $data;
	}
	public function formatted_shipping_address( $order ) {
		return $order->shipping_address_1 . ', ' .
			$order->shipping_address_2 . ' ' .
			$order->shipping_city . ', ' .
			$order->shipping_state . ' ' .
			$order->shipping_postcode;
	}

	public function formatted_billing_address( $order ) {
		return $order->billing_address_1 . ', ' .
			$order->billing_address_2 . ' ' .
			$order->billing_city . ', ' .
			$order->billing_state . ' ' .
			$order->billing_postcode;
	}
	public function prepare_items() {
		global $wpdb;
		$perpage  = 10;
		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$hidden   = $this->get_hidden_columns();
		$this->process_bulk_action();
		$data                  = $this->table_data();
		$totalitems            = count( $data );
		$this->_column_headers = array(
			$columns,
			$hidden,
			$sortable,
		);

		function usort_reorder( $a, $b ) {
			$orderby = ( ! empty( sanitize_text_field( $_REQUEST['orderby'] ) ) ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'ID';
			// If no sort, default to title
			$order = ( ! empty( sanitize_text_field( $_REQUEST['order'] ) ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'asc';
			// If no order, default to asc
			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
			// Determine sort order
			return ( $order === 'asc' ) ? $result : -$result;
			// Send final sort direction to usort
		}
		usort( $data, 'usort_reorder' );
		$totalpages  = ceil( $totalitems / $perpage );
		$currentPage = $this->get_pagenum();
		$data        = array_slice( $data, ( ( $currentPage - 1 ) * $perpage ), $perpage );
		$this->set_pagination_args(
			array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page'    => $perpage,
			)
		);
		$this->items = $data;
	}

	public function admin_return_exchange_reason() {
		global $wpdb;
		$reasonArray = $wpdb->get_results( $wpdb->prepare( "SELECT reason_option, reason_value FROM {$wpdb->prefix}wp_return_exchange_reason" ) );
		return $reasonArray;
	}

	function orderList_handler() {
		global $wpdb;
		// Search box
		if ( isset( $_GET['s'] ) ) {
			$this->prepare_items( sanitize_text_field( $_GET['s'] ) );
		} else {
			$this->prepare_items();
		}
		?>

<div class="wrap">
<!-- All Modals -->

<!-- Modal -->
		<?php
		$row_data  = $this->table_data();
		$cancelURl = admin_url( 'admin.php?page=woo-return-exchange-dashboard&tab=pending' );
		for ( $i = 0; $i < count( $row_data ); $i++ ) {
			$orderID         = $row_data[ $i ]['orderID'];
			$order           = wc_get_order( $orderID );
			$orderHeaderData = $this->orderDetailsData( $orderID );
			?>
<div id="myModal_<?php echo esc_attr( $row_data[ $i ]['orderID'] ); ?>" class="modal fade" role="dialog">
  <div class="modal-dialog">

	<!-- Modal content-->
	<div class="modal-content">
	  <div class="modal-header">
	  <div class="outer container">        
			<div class="order-date-section">               
				<div class="col-sm-3 date-section-item"><span>Order #:</span> <?php echo esc_attr( $orderID ); ?></div>
				<div class="col-sm-3 date-section-item"><span>Customer Name:</span><?php echo esc_attr( $orderHeaderData[0]['username'] ); ?></div>
				<div class="col-sm-3 date-section-item"><span>Requested Date:</span> <?php echo esc_attr( $orderHeaderData[0]['requestedDate'] ); ?></div>
				<div class="col-sm-3 date-section-item"><span>
				<?php
				if ( $row_data[ $i ]['approvalStatus'] == 'Rejected' ) {
					echo 'Rejected Date:';
				} else {
					echo 'Approved Date:'; }
				?>
				</span> <?php echo esc_attr( $orderHeaderData[0]['approvedDate'] ); ?> </div>
			</div>

			<div class="order-customer-section">
				<div class="col-sm-3 product-section-item"><?php echo esc_attr( $orderHeaderData[0]['orderQTY'] ); ?></div>
				<div class="col-sm-3 product-section-item"><span>Order Date:</span><?php echo esc_attr( $orderHeaderData[0]['orderDate'] ); ?> </div>                    
			</div>

		</div>
		<button type="button" class="close" data-bs-dismiss="modal">&times;</button>        
	  </div>
	  <div class="modal-body">
	  <div class="product-listing-container" >
	  <form method="POST" id="pending_form" class="return_exchange_form" onSubmit="return confirm('Are you sure?') "></form>
<table class="return-order-table table table-responsive">
<thead>
	<tr>
		<th>Product</th>
		<th>Return QTY</th>
		<th>Return/Exchange</th>
		<th>Reason</th> 
	</tr>     
	</thead>
	<tbody>
			<?php
			foreach ( $order->get_items() as $item_id => $item ) {
				global $wpdb;
				$product_id = $item->get_product_id();
				// Get the WC_Product object
				$product = $item->get_product();
				// The quantity
				$quantity = $item->get_quantity();
				// Image
				$img = wp_get_attachment_url( $product->get_image_id() );
				// The product name
				$product_name = $item->get_name();
				// … OR: $product->get_name();

				// Get line item totals (non discounted)
				$total = $item->get_subtotal();
				// Total without tax (non discounted)
				$total_tax = $item->get_subtotal_tax();
				// Total tax (non discounted)

				// Get line item totals (discounted when a coupon is applied)
				$total = $item->get_total();
				// Total without tax (discounted)
				$total_tax = $item->get_total_tax();
				// Total tax (discounted)
				// Check if any return/exchange exist for a specific orderID
				$is_return_exist_for_product = $wpdb->get_var( "SELECT count(*) as rowCount FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
				if ( $is_return_exist_for_product == 0 ) {
					// Check if there is already order exist too
					$is_order_exist_for_product = $wpdb->get_var( "SELECT count(*) as rowCount FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID" );
					if ( $is_order_exist_for_product > 0 ) {
						continue;
					}
				}
				?>
				 

	<tr>
		<td><img src="<?php echo esc_url( $img ); ?>" width="75" /> &nbsp;&nbsp; <span class="product-title-with-img"><?php echo esc_attr( $product_name ); ?></span></td>
		<td>
			<input type="text" value="<?php $orderQtyToReturn = $wpdb->get_var( "SELECT return_or_exchange_quantity FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" ); echo esc_attr( trim( 'Total ' . $orderQtyToReturn . ' items - $' . ( $total / $quantity ) * $orderQtyToReturn . ' USD' ) ); ?>"  disabled/>
		</td>

		<td>
				<?php
				$isReturnorExchange = $wpdb->get_var( "SELECT is_return_or_exchange FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
				?>
		 <input type="text" value="<?php echo esc_attr( $isReturnorExchange ); ?>" disabled/> 
		</td>

		<td>
				<?php
				   $return_reson        = $this->admin_return_exchange_reason();
				$return_exchange_reason = $wpdb->get_var( "SELECT return_exchange_reason FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
				?>
				  
			  <input type="text" 
				value="<?php
				foreach ( $return_reson as $reason ) {
					if ( $reason->reason_value == $return_exchange_reason ) {
						echo esc_attr( trim( $reason->reason_option ) );
					}
				}
				?>
				" style="width: 220px;" disabled/>
		</td>      
	</tr>

	<tr>
		<td></td>
		<td>
			<h3 style="position: relative;" class="comment-heading" >Customer Comment</h3>
			<textarea cols="50" rows="2" form="pending_form" disabled><?php
			$customer_comments = $wpdb->get_var( "SELECT comments FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
			echo esc_attr( trim( $customer_comments ) );
			?>
			</textarea>
		</td>
		<td>
			<h3 style="position: relative;" class="comment-heading" > Private Notes</h3>
			<textarea cols="50" rows="2"  
			name="private_notes[<?php echo esc_attr( $product_id ); ?>]" 
			form="pending_form" disabled><?php
			$store_owner_notes_private = $wpdb->get_var( "SELECT store_owner_notes_private FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
			echo esc_attr( $store_owner_notes_private );
			?>
			</textarea>
		</td> 
		<td>
			<h3 style="position: relative;" class="comment-heading" > Store Owner Approval</h3><input type="text" value="<?php
			$storeOwnerApprovalStatus = $wpdb->get_var( "SELECT store_owner_approval_status FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
			echo esc_attr( isset( $storeOwnerApprovalStatus ) ? $storeOwnerApprovalStatus : 'N/A' );
			?>" disabled/>
		</td>
	</tr>

	<tr>
		<td></td>
		<td>
			<input type="checkbox" id="item_refunded<?php echo esc_attr( $product_id ); ?>" name="item_refunded[<?php echo esc_attr( $product_id ); ?>]" value="item_refunded" form="pending_form"        
				<?php
				$item_refundedVal = $wpdb->get_var( "SELECT is_item_refunded FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
				echo ( isset( $item_refundedVal ) && $item_refundedVal == 1 ) ? 'checked' : '';
				?>
			disabled>
			<label for="item_refunded<?php echo esc_attr( $product_id ); ?>" style="margin-bottom: 1px;">Item Refunded</label>
		</td>
		<td>
			<input type="checkbox" id="item_received<?php echo esc_attr( $product_id ); ?>" name="item_received[<?php echo esc_attr( $product_id ); ?>]" value="item_received" form="pending_form"
				<?php
				$item_receivedVal = $wpdb->get_var( "SELECT is_item_received FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
				echo ( isset( $item_receivedVal ) && $item_receivedVal == 1 ) ? 'checked' : '';
				?>
				disabled>
			<label for="item_received<?php echo esc_attr( $product_id ); ?>" style="margin-bottom: 1px;">Item Received</label>
		</td>
		<td>
			<input type="checkbox" id="item_exchanged<?php echo esc_attr( $product_id ); ?>" name="item_exchanged[<?php echo esc_attr( $product_id ); ?>]" value="item_exchanged" form="pending_form"
				<?php
				$item_exchangedVal = $wpdb->get_var( "SELECT is_item_exchanged FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
				echo ( isset( $item_exchangedVal ) && $item_exchangedVal == 1 ) ? 'checked' : '';
				?>
				disabled>
			<label for="item_exchanged<?php echo esc_attr( $product_id ); ?>" style="margin-bottom: 1px;">Item Exchanged</label>
		</td> 
		<input type="hidden" name="product_id_hidden[]" value="<?php echo esc_attr( $product_id ); ?>" form="pending_form" />       
	</tr>
	
	<tr>
		<td colspan="5"><hr/></td>
	</tr>

				<?php
			}//endfor loop
			?>

	</tbody>
</table>
	 </div>

	  </div>       
	  <!-- 
		  End of modal body  
	  <div class="modal-footer">
		<button type="button" class="button button-primary button-large" data-dismiss="modal">Close</button>
	  </div> 
	-->
	</div>

  </div>
</div>

			<?php
		} //end for loop
		?>
<!-- End of Modals Section -->
<form id="user-filter" method="get">
	<p class="search-box">
		<label class="screen-reader-text" for="<?php echo esc_attr( 'search' ); ?>"><?php echo esc_attr( $text ); ?>:</label>
		<input type="search" id="<?php echo esc_attr( 'search' ); ?>" name="s" value="" />
		<input type="submit" class="button" value="<?php esc_attr_e( 'Find', 'woo-return-exchange' ); ?>">
	</p>
	<input type="hidden" name="page" value="<?php echo esc_attr( htmlspecialchars( $_REQUEST['page'] ) ); ?>" />
	<input type="hidden" name="tab" value="<?php echo esc_attr( htmlspecialchars( $_REQUEST['tab'] ) ); ?>"/>
		<?php $this->display(); ?>
</form>
</div>

		<?php
	}
}
$wp_list_table = new Woo_Return_Exchange_Completed();
$wp_list_table->orderList_handler();