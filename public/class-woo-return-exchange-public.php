<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://sovratec.com/
 * @since      1.1.0
 *
 * @package    Sov_Return_Exchange
 * @subpackage Sov_Return_Exchange/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Sov_Return_Exchange
 * @subpackage Sov_Return_Exchange/public
 * @author     Sovratec <https://sovratec.com/>
 */
class Woo_Return_Exchange_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Register Client Edit View Page
		// add_action( 'admin_menu', 'order_edit_view_page' );
		add_action( 'admin_menu', array( $this, 'order_edit_view_page' ) );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.1.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( 'jquery-datatables-css', plugin_dir_url( __FILE__ ) . 'css/datatables.min.css' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-return-exchange-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.1.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( 'jquery-datatables-js', plugin_dir_url( __FILE__ ) . 'js/datatables.min.js', array( 'jquery' ) );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-return-exchange-public.js', array( 'jquery' ), $this->version, false );
	}

	public function order_edit_view_page() {
		add_submenu_page( '', __( 'Edit Order', 'woo-return-exchange' ), __( 'Edit Order', 'woo-return-exchange' ), 'manage_options', 'edit-order', array( $this, 'woo_return_exchange_edit' ) );
	}

	public function woo_return_exchange_edit() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/woo-return-exchange-edit-display.php';
	}
}
new Woo_Return_Exchange_Public( 'Woo_Return_Exchange', '1.1.0' );
