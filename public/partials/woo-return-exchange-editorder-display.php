<?php
// Code to handle form submission
if ( isset( $_POST['form_submitted'] ) ) {
	global $wp, $wpdb;
	$saveStatus   = save_order_data( $_POST );
	$orderIDValue = sanitize_text_field( htmlspecialchars( $_GET['editorder'] ) );
	if ( $saveStatus ) {
		if ($saveStatus) {
			echo esc_attr('Please wait...');
			?>
			<script>
			var productVal = <?php echo esc_attr($orderIDValue) ?>; 
			window.location = window.location.pathname + window.location.hash +"/?orderUpdateID="+productVal; 
			</script>
		<?php 
		}
	}
}
	$orderID            = isset( $_GET['editorder'] ) ? sanitize_text_field( htmlspecialchars( $_GET['editorder'] ) ) : die;
	$orderData          = return_exchange_order_data( $orderID );
	$returnExchangeData = is_return_request_exist( $orderID );

if ( isset( $orderData['recordsTotal'] ) && ( $orderData['recordsTotal'] == 1 ) ) {
	$orderIDval   = $orderData['data'][0]['orderID'];
	$orderDateval = $orderData['data'][0]['orderDate'];
	$orderQTY     = $orderData['data'][0]['orderQTY'];
	$orderAddress = $orderData['data'][0]['shippedTo'];
}
?>
<div class="container">
<h2 style="text-align: center;padding: 2px 0px;font-size: 20px;font-weight: 600;">Order Details</div>
<div class="order-details-container">
<table>
<thead>
		<th>Order #:</th>
		<th>Order Date:</th>
		<th>Order QTY:</th>
		<th>Shipping Address:</th>        
	</thead>
	<tbody>
		<tr>
			<td><?php echo esc_attr( $orderIDval ); ?></td>
			<td><?php echo esc_attr( $orderDateval ); ?></td>
			<td><?php echo esc_attr( $orderQTY ); ?></td>
			<td>
			<?php
			echo wp_kses(
				$orderAddress,
				array(
					'br'     => array(),
					'p'      => array(),
					'strong' => array(),
				)
			);
			?>
			</td>
		</tr>
	</tbody>
</table>
</div>
<!-- Order Items Repeater -->
<?php

// get an instance of the WC_Order object
$order = wc_get_order( $orderID );

// The loop to get the order items which are WC_Order_Item_Product objects since WC 3+
foreach ( $order->get_items() as $item_id => $item ) {
	// Get the product ID
	$product_id = $item->get_product_id();

	// Get the variation ID
	$variation_id = $item->get_variation_id();

	// Get the WC_Product object
	$product = $item->get_product();

	// The quantity
	$quantity = $item->get_quantity();

	// Image
	$img = wp_get_attachment_url( $product->get_image_id() );

	// The product name
	$product_name = $item->get_name(); // … OR: $product->get_name();

	// Get the product SKU (using WC_Product method)
	$sku = $product->get_sku();

	// Get line item totals (non discounted)
	$total     = $item->get_subtotal(); // Total without tax (non discounted)
	$total_tax = $item->get_subtotal_tax(); // Total tax (non discounted)

	// Get line item totals (discounted when a coupon is applied)
	$total     = $item->get_total(); // Total without tax (discounted)
	$total_tax = $item->get_total_tax(); // Total tax (discounted)
}
?>
<div class="product-listing-container" >
<h2 style="text-align: center;padding: 2px 0px;font-size: 20px;font-weight: 600;margin-top: 35px;">Return/Exchange Listing</h2>
<form method="POST" id="return_exchange_form" class="return_exchange_form" onSubmit="return confirm('Please note that you won\'t be able to change anything after submitting this request!!') "></form>

<table class="return-order-table">
<thead>
		<th>Product Description</th>
		<th>Product QTY</th>
		<th>Return QTY</th>
		<th>Return/Exchange</th>
		<th>Reason for Return/Exchange</th>
		<th></th>
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
			$product_name = $item->get_name(); // … OR: $product->get_name();

			// Get line item totals (non discounted)
			$total     = $item->get_subtotal(); // Total without tax (non discounted)
			$total_tax = $item->get_subtotal_tax(); // Total tax (non discounted)

			// Get line item totals (discounted when a coupon is applied)
			$total     = $item->get_total(); // Total without tax (discounted)
			$total_tax = $item->get_total_tax(); // Total tax (discounted)

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
		<td><img src="<?php echo esc_url( $img ); ?>" width="75" /> &nbsp;&nbsp; <?php echo esc_attr( $product_name ); ?></td>
		<td><?php echo esc_attr( trim( $quantity . ' X $' . ( $total / $quantity ) ) ); ?></td>        
			<?php if ( $returnExchangeData > 0 ) : ?>
		<td>
			<input type="text" style="width: 2rem;text-align: center;" value="<?php $orderQtyToReturn = $wpdb->get_var( "SELECT return_or_exchange_quantity FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" ); echo esc_attr( $orderQtyToReturn ); ?>" form="return_exchange_form"  disabled/>
		</td>
			<?php else : ?>
		<td>
			<input type="number" min="0" max="<?php echo esc_attr( $quantity ); ?>" value="0" name="returnqty[]" form="return_exchange_form" class="return-qty-handler" data-productID="<?php echo esc_attr( $product_id ); ?>"  />
			<input type="hidden" name="product_id_hidden[]" value="<?php echo esc_attr( $product_id ); ?>" form="return_exchange_form"/>
		</td>
			<?php endif; ?>

			<?php if ( $returnExchangeData > 0 ) : ?>
		<td>
				<?php
				// global $wpdb;
				$isReturnorExchange = $wpdb->get_var( "SELECT is_return_or_exchange FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
				?>
		<select form="return_exchange_form" disabled>
			<option value="return" 
				<?php
				if ( $isReturnorExchange == 'Return' ) {
					echo 'selected=selected';
				}
				?>
									>Return</option>
			<option value="exchange" 
				<?php
				if ( $isReturnorExchange == 'Exchange' ) {
					echo 'selected=selected';
				}
				?>
									 >Exchange</option>
		</select>
		</td>
			<?php else : ?>
			<td>
			<select name="returnProduct[]" class="return_or_exchange" id="<?php echo esc_attr( $product_id ) . '_return_or_exchange'; ?>" form="return_exchange_form">
				<option value="return">Return</option>
				<option value="exchange">Exchange</option>
			</select>
		</td>
			<?php endif; ?>

			<?php if ( $returnExchangeData > 0 ) : ?>
		<td>
				<?php
				$return_reson           = return_exchange_reason();
				$return_exchange_reason = $wpdb->get_var( "SELECT return_exchange_reason FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
				?>
			<select form="return_exchange_form" disabled>
				<?php foreach ( $return_reson as $reason ) : ?>
			<option 
			value="<?php echo esc_attr( $reason->reason_value ); ?>" 
					<?php
					if ( $reason->reason_value == $return_exchange_reason ) {
						echo esc_attr( 'selected=selected' );
					}
					?>
			>
					<?php echo esc_attr( $reason->reason_option ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
			<?php else : ?>
			<td>
				<?php $return_reson = return_exchange_reason(); ?>
				<select name="reason_returnProduct[]" class="return_reason" id="<?php echo esc_attr( $product_id . '_return_reason' ); ?>" form="return_exchange_form">
					<?php foreach ( $return_reson as $reason ) : ?>
					<option value="<?php echo esc_attr( trim( $reason->reason_value ) ); ?>"><?php echo esc_attr( trim( $reason->reason_option ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<?php endif; ?>            
	</tr>

	<tr>
		<td></td>
		<td></td>

			<?php if ( $returnExchangeData > 0 ) : ?>
		<td colspan="3">
			<textarea cols="30" rows="10" form="return_exchange_form" disabled><?php
			$customer_comments = $wpdb->get_var( "SELECT comments FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
			echo esc_attr( trim( $customer_comments ) );
			?>
		</textarea>
		</td>
			<?php else : ?>
			<td colspan="3"><textarea name="customer_comments[]" placeholder="Customer Comments " class="return_comment" id="<?php echo esc_attr( $product_id . '_return_comment' ); ?>" cols="30" rows="10" form="return_exchange_form"></textarea></td>
			<?php endif; ?> 
	</tr>

			<?php if ( $returnExchangeData > 0 ) : ?>
	<tr>
		<td></td>
		<td></td>
		<td>
			<input type="checkbox" id="item_refunded<?php echo esc_attr( $product_id ); ?>" name="item_refunded[<?php echo esc_attr( $product_id ); ?>]" value="item_refunded" form="pending_form_<?php echo esc_attr( $orderID ); ?>"        
				<?php
				$item_refundedVal = $wpdb->get_var( "SELECT is_item_refunded FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
				echo ( isset( $item_refundedVal ) && $item_refundedVal == 1 ) ? 'checked' : '';
				?>
				disabled>
			<label for="item_refunded<?php echo esc_attr( $product_id ); ?>" style="margin-bottom: 1px;">Item Refunded</label>
		</td>
		<td>
			<input type="checkbox" id="item_received<?php echo esc_attr( $product_id ); ?>" name="item_received[<?php echo esc_attr( $product_id ); ?>]" value="item_received" form="pending_form_<?php echo esc_attr( $orderID ); ?>"
				<?php
				$item_receivedVal = $wpdb->get_var( "SELECT is_item_received FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
				echo ( isset( $item_receivedVal ) && $item_receivedVal == 1 ) ? 'checked' : '';
				?>
				disabled>
			<label for="item_received<?php echo esc_attr( $product_id ); ?>" style="margin-bottom: 1px;">Item Received</label>
		</td>
		<td>
			<input type="checkbox" id="item_exchanged<?php echo esc_attr( $product_id ); ?>" name="item_exchanged[<?php echo esc_attr( $product_id ); ?>]" value="item_exchanged" form="pending_form_<?php echo esc_attr( $orderID ); ?>"
				<?php
				$item_exchangedVal = $wpdb->get_var( "SELECT is_item_exchanged FROM {$wpdb->prefix}wp_return_exchange WHERE order_id = $orderID AND product_id = $product_id" );
				echo ( isset( $item_exchangedVal ) && $item_exchangedVal == 1 ) ? 'checked' : '';
				?>
				disabled>
			<label for="item_exchanged<?php echo esc_attr( $product_id ); ?>" style="margin-bottom: 1px;">Item Exchanged</label>
		</td> 
	</tr>
			<?php endif; ?>
		<?php }//endfor loop ?>
	
	<?php if ( $returnExchangeData > 0 ) : ?>
	<tr>        
		<td colspan="5" style="text-align: center;padding: 2rem 0;border-bottom: none!important;">
			<button class = "button primary" onclick="javascript:window.location = window.location.pathname + window.location.hash">Go Back</button>
		</td>
	</tr>
	<?php else : ?>
		<td colspan="5" style="text-align: center;padding: 2rem 0;border-bottom: none!important;">
			<input type="submit" value="Submit" form="return_exchange_form">
			<input type="reset" value="Cancel" onclick="javascript:window.location = window.location.pathname + window.location.hash" form="return_exchange_form">
			<input type="hidden" name="form_submitted" value="1" form="return_exchange_form" />
			<input type="hidden" name="order_id" value="<?php echo esc_attr( $orderID ); ?>" form="return_exchange_form" />
		</td>
	<?php endif; ?> 
	</tbody>
</table>
	 </div>     
</div>
