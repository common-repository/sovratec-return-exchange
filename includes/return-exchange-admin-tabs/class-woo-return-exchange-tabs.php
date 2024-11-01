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
class Woo_Return_Exchange_Tabs {

		// Constructor
	function __construct() {
		$this->tabs_management();
	}

	public function woo_return_exchange_tabs_management( $current = 'request' ) {

		global $wpdb;
		$tabs              = array(
			'request'        => __( 'Request for Return/Exchange', 'woo-return-exchange' ),
			'pending'        => __( 'Pending Return/Exchange', 'woo-return-exchange' ),
			'completed'      => __( 'Completed Return/Exchange', 'woo-return-exchange' ),
			'configurations' => __( 'Configurations', 'woo-return-exchange' ),
		);
		$request_counter   = $wpdb->get_var( "SELECT count( DISTINCT order_id) as request_counter FROM {$wpdb->prefix}wp_return_exchange WHERE store_owner_approval_status IS NULL" );
		$pending_counter   = $wpdb->get_var( "SELECT count( DISTINCT order_id) as pending_counter FROM {$wpdb->prefix}wp_return_exchange WHERE store_owner_approval_status = 'Approved' AND return_exchange_status IS NULL" );
		$completed_counter = $wpdb->get_var( "SELECT count( DISTINCT order_id) as completed_counter FROM {$wpdb->prefix}wp_return_exchange WHERE store_owner_approval_status IS NOT NULL AND return_exchange_status = 'Completed'" );
		?>        
			<h2 class="nav-tab-wrapper">
		  <?php
			foreach ( $tabs as $tab => $name ) :
				$class = ( $tab == $current ) ? ' nav-tab-active' : '';
				?>
					  <a class='nav-tab <?php echo esc_attr( $class ); ?>' href='?page=woo-return-exchange-dashboard&tab=<?php esc_attr_e( $tab, 'woo-return-exchange' ); ?>'><?php echo esc_attr( $name ); ?>
						<?php
						if ( $tab == 'request' ) {
							echo '<span class="tab-notification-counter-red" ><span class="plugin-count">' . esc_attr( $request_counter ) . '</span></span>';
						} elseif ( $tab == 'pending' ) {
							echo '<span class="tab-notification-counter-red" ><span class="plugin-count">' . esc_attr( $pending_counter ) . '</span></span>';
						} elseif ( $tab == 'completed' ) {
						}
						?>
									  
					</a>
				  <?php
			endforeach;
			?>
			</h2>   
		<?php
	}

	public function tabs_management() {
		?>
	
		<div class="wrap">
		  <?php
			if ( isset( $_GET['tab'] ) ) {
				$this->woo_return_exchange_tabs_management( $_GET['tab'] );
			} else {
				$this->woo_return_exchange_tabs_management( 'request' );
			}
			?>
		  <div id="poststuff">
			<?php
			if ( $_GET['page'] == 'woo-return-exchange-dashboard' ) {
				if ( isset( $_GET['tab'] ) ) {
					$tab = sanitize_text_field( $_GET['tab'] );
				} else {
					$tab = 'request';
				}
				switch ( $tab ) {
					case 'request':
																		include_once plugin_dir_path( __FILE__ ) . 'class-woo-return-exchange-request.php';

						break;
					case 'pending':
																		include_once plugin_dir_path( __FILE__ ) . 'class-woo-return-exchange-pending.php';

						break;
					case 'completed':
																		include_once plugin_dir_path( __FILE__ ) . 'class-woo-return-exchange-completed.php';

						break;
					case 'configurations':
																		include_once plugin_dir_path( __FILE__ ) . 'class-woo-return-exchange-configurations.php';
				}
			}
			?>
		  </div>
		</div>
		<?php
	}
}
new Woo_Return_Exchange_Tabs();
