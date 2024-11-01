<div class="container">
<h1 class="page-title-heading">WooCommerce Return & Exchange</h1>
<?php
// check if global message variable is set
if ( isset( $_GET['orderUpdateID'] ) ) {
	echo '<h2 class="top-notice">Order with ID #' . esc_attr( htmlspecialchars( $_GET['orderUpdateID'] ) ) . ' has been updated!</h2>';
}

?>
<table id="orderTable">
	<thead>
		<th>ID</th>
		<th>Date</th>
		<th>QTY</th>
		<th>Shipping</th>       
		<th>Status</th>
		<th>Action</th>
	</thead>
	<tbody>
<?php
$orderData = return_exchange_order_data();
if ( isset( $orderData['recordsTotal'] ) && ( $orderData > 1 ) ) :
	for ( $count = 0; $count < $orderData['recordsTotal']; $count++ ) :
		?>
		<tr>
			<td><?php echo esc_attr( $orderData['data'][ $count ]['orderID'] ); ?> </td>
			<td><?php echo esc_attr( $orderData['data'][ $count ]['orderDate'] ); ?> </td>
			<td><?php echo esc_attr( $orderData['data'][ $count ]['orderQTY'] ); ?> </td>
			<td>
			<?php
						echo wp_kses(
							$orderData['data'][ $count ]['shippedTo'],
							array(
								'br'     => array(),
								'p'      => array(),
								'strong' => array(),
							)
						);
			?>
			 </td>
			<td><?php echo esc_attr( $orderData['data'][ $count ]['orderStatus'] ); ?> </td>
			<td><a href="<?php echo esc_url( '?editorder=' . $orderData['data'][ $count ]['orderID'] ); ?>" class="button primary" style="border-radius:99px;">
			<span>Return/Exchange</span>
		  </a></td>
		</tr>
		<?php
	endfor;
endif;
?>
	</tbody>
</table>
</div>
