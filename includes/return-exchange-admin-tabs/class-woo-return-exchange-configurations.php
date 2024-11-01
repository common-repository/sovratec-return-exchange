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

class Woo_Return_Exchange_Configurations {

	function __construct() {
		$this->configurationFormDisplay();
	}

	public function configurationFormDisplay() {
		global $wpdb;
		if ( isset( $_POST['config_form_submitted'] ) ) {
			$saveStatus    = $this->save_config_data( $_POST );
			$nonce         = wp_create_nonce( 'my-nonce' );
			$approveAction = admin_url( 'admin.php?page=woo-return-exchange-dashboard&tab=configurations&nonce=' . $nonce );
			esc_attr_e( 'Please wait...', 'woo-return-exchange' );
			wp_redirect( $approveAction );
			exit;
		} else {
			$configDataArray = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wp_return_exchange_configuration WHERE config_id = 1" ) );
			?>
	<div class="wrap">
 
			<?php
			$nonce = $_REQUEST['nonce'];
			if ( wp_verify_nonce( $nonce, 'my-nonce' ) ) {
				$message = 'Configurations has been Updated!';
			} else {
				$message = '';
			}
			?>

			<?php
			if ( $message ) :
				?>
			<div class="alert alert-success alert-dismissible fade show" role="alert">
		<strong>Success!</strong> <?php echo esc_attr( $message ); ?>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		</div>
				<?php
			endif;
			?>

		<div id="poststuff">
			<form method="POST" id="config_control_form" class="config_control_form" onSubmit="return confirm('Are you sure?') "></form>
			<table class="configuration-control-table table">           
				<tbody>     
					<tr>
						<td colspan="1" >Email From:</td>
						<td colspan="5"> <input type="email" id="email_from" name="email_from" value="<?php echo esc_attr( isset( $configDataArray->email_from ) ? $configDataArray->email_from : 'imessenger@sovratec.com' ); ?>" class="email-from" form="config_control_form"></td>        
					</tr>
					<tr>
						<td colspan="6" >Email Confirmation when return/exchange was requested: </td>
					</tr>
					<tr>
						<td colspan="6"> <textarea id="returnExchangeRequested" name="returnExchangeRequested" form="config_control_form"><?php echo esc_attr( isset( $configDataArray->return_requested_msg ) ? $configDataArray->return_requested_msg : '' ); ?></textarea> </td>
					</tr>
					<tr>
						<td colspan="6" >Email Instruction How to Return Product after Approval(Specify return address that customer can send product back):</td>
					</tr>
					<tr>
						<td colspan="6"> <textarea id="returnAddressTextarea" name="returnAddressTextarea" form="config_control_form"><?php echo esc_attr( isset( $configDataArray->return_address_msg ) ? $configDataArray->return_address_msg : '' ); ?></textarea> </td>
					</tr>

					<tr>
						<td colspan="6" >Email notification if return/exchange rejected:</td>
					</tr>
					<tr>
						<td colspan="6"> <textarea id="returnExchangeRejected" name="returnExchangeRejected" form="config_control_form"><?php echo esc_attr( isset( $configDataArray->return_rejected_msg ) ? $configDataArray->return_rejected_msg : '' ); ?></textarea> </td>
					</tr>
					<tr>
						<td colspan="6" >Email Notification when return/exchange was completed: </td>
					</tr>
					<tr>
						<td colspan="6"> <textarea id="returnExchangeCompleted" name="returnExchangeCompleted" form="config_control_form"><?php echo esc_attr( isset( $configDataArray->return_completed_msg ) ? $configDataArray->return_completed_msg : '' ); ?></textarea> </td>
					</tr>

					<tr>  
						<td colspan="6" style="text-align: center;padding: 2rem 0;border-bottom: none!important;">
							<input type="submit" class="button button-primary button-large" value="Save Configurations" form="config_control_form">
							<input type="hidden" name="config_form_submitted" value="1" form="config_control_form" />
						</td>
					</tr>
				</tbody>                
			</table>            
		</div>
	</div>
			<?php
		}//end of if-else
	}

	public function save_config_data( $config_data ) {
		global $wpdb;
		$flag = 0;
		if ( isset( $config_data['config_form_submitted'] ) ) {
			// create record if not exists
			$recordExist = $wpdb->get_var( "SELECT config_id FROM {$wpdb->prefix}wp_return_exchange_configuration WHERE config_id = 1" );
			if ( ! $recordExist ) {
				$returnStatus = $wpdb->insert(
					$wpdb->prefix . 'wp_return_exchange_configuration',
					array(
						'email_from'           => $config_data['email_from'],
						'return_address_msg'   => $config_data['returnAddressTextarea'],
						'return_rejected_msg'  => $config_data['returnExchangeRejected'],
						'return_requested_msg' => $config_data['returnExchangeRequested'],
						'return_completed_msg' => $config_data['returnExchangeCompleted'],
					)
				);
			} else {
				$returnStatus = $wpdb->update(
					$wpdb->prefix . 'wp_return_exchange_configuration',
					array(
						'email_from'           => $config_data['email_from'],
						'return_address_msg'   => $config_data['returnAddressTextarea'],
						'return_rejected_msg'  => $config_data['returnExchangeRejected'],
						'return_requested_msg' => $config_data['returnExchangeRequested'],
						'return_completed_msg' => $config_data['returnExchangeCompleted'],
					),
					array(
						'config_id' => 1,
					)
				);
			}

			if ( ! ( $returnStatus == false ) ) {
				$flag = 1;
			} else {
				$flag = 0;
			}

			return ( $flag == 1 ) ? 1 : 0;
		}
	}
}
new Woo_Return_Exchange_Configurations();
