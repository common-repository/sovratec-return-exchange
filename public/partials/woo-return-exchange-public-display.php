<?php

/**
 * Provide a public-facing view for the plugin
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
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php require_once 'woo-return-exchange-functions.php'; ?>
<?php
if ( isset( $_GET['editorder'] ) ) {
	include 'woo-return-exchange-editorder-display.php';
} else {
	include 'woo-return-exchange-order-display.php';
}
?>
