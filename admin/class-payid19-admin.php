<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://payid19.com
 * @since      1.0.0
 *
 * @package    Payid19
 * @subpackage Payid19/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Payid19
 * @subpackage Payid19/admin
 * @author     Payid19 <info@payid19.com>
 */
class Payid19_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action( 'wp_ajax_do_control', [$this,'do_control'] );
		add_action( 'wp_ajax_nopriv_do_control', [$this,'do_control'] );

	}
	function do_control(){
		global $woocommerce,$wpdb;
		$order = new WC_Order( sanitize_text_field($_POST['order_id']) ); 
		$order_status  = $order->get_status();

		if($order_status=='completed'){
			$status['status']='success';
			$status['redirect_url']=$order->get_checkout_order_received_url();
		}else{
			$status['status']= $order_status;
			$status['redirect_url']=$order->get_cancel_order_url_raw();
		}
		echo json_encode($status);
		wp_die(); 
	}
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Payid19_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Payid19_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/payid19-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Payid19_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Payid19_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/payid19-admin.js', array( 'jquery' ), $this->version, false );

	}

}
