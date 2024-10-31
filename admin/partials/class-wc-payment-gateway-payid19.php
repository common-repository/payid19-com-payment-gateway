<?php
/**
 * payid19 Mobile Payments Gateway.
 *
 * Provides a payid19 Mobile Payments Payment Gateway.
 *
 * @class       WC_Gateway_payid19
 * @extends     WC_Payment_Gateway
 * @version     2.1.0
 * @package     WooCommerce/Classes/Payment
 */
class WC_Gateway_Payid19 extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		// Setup general properties.
		$this->setup_properties();

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Get settings.
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->instructions       = $this->get_option( 'instructions' );
		$this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
		$this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes';


		
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );

		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		add_action('woocommerce_receipt_' . $this->id, array( $this, 'payid19checkoutmodal')); 
		add_action('woocommerce_api_' . strtolower($this->id), array( $this, 'payid19_callback') ); //Callback function return operation from the bank


		add_action( 'admin_notices', [$this,'payid19_apikey_notice'] );
		add_action( 'admin_notices', [$this,'test_mode_controll'] );


	}

	/**
	 * Setup general properties for the gateway.
	 */
	protected function setup_properties() {
		$this->id                 = 'WC_Gateway_Payid19';
		$this->icon               = 'https://payid19.com/img/wc-logo.png';
		$this->method_title       = __( 'Crypto Payment Gateway', 'payid19-payments-woo' );
		$this->method_description = __( 'Have your customers pay with payid19 crypto payments.', 'payid19-payments-woo' );
		$this->has_fields         = false;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'            => array(
				'title'       => __( 'Enable/Disable', 'WC_Gateway_Payid19-payments-woo' ),
				'label'       => __( 'Enable Payid19 Crypto Payment Gateway', 'WC_Gateway_Payid19-payments-woo' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'              => array(
				'title'       => __( 'Title', 'WC_Gateway_Payid19-payments-woo' ),
				'type'        => 'text',
				'description' => __( 'Title that the customer will see on your checkout page.', 'WC_Gateway_Payid19-payments-woo' ),
				'default'     => __( 'Pay with Cryptocurrency', 'WC_Gateway_Payid19-payments-woo' ),
				'desc_tip'    => true,
			),
			'description'        => array(
				'title'       => __( 'Description', 'WC_Gateway_Payid19-payments-woo' ),
				'type'        => 'textarea',
				'description' => __( 'Description that the customer will see on your checkout page.', 'WC_Gateway_Payid19-payments-woo' ),
				'default'     => __( 'You can pay safely with cryptocurrencies.', 'WC_Gateway_Payid19-payments-woo' ),
				'desc_tip'    => true,
			),
			'public_key' => array(
				'title' => 'Public Key',
				'type' => 'text',
				'description' =>  'Public Key (Get it from <a href="https://payid19.com">Payid19.com</a>)'),
			'private_key' => array(
				'title' => 'Private Key',
				'type' => 'text',
				'description' =>  'Private Key (Get it from <a href="https://payid19.com">Payid19.com</a>)'),
			'endpoint' => array(
				'title' => 'API Url',
				'type' => 'text',
				'description' =>  'Payid19.com API URL(dont change)',
				'default' => 'https://payid19.com/api/v1/create_invoice'),
			'timeout' => array(
				'title' => 'Expiration date',
				'type' => 'text',
				'description' =>  'Expiration data of invoice, write as hourly(max: 6)',
				'default' => '3'),
			'testMode' => array(
				'title' => 'Test',
				'type' => 'checkbox',
				'label' => 'Enable / Disable',
				'description' =>  'Use it for testing, don\'t forget to turn it off.',
				'default' => '0'),
			'margin_ratio' => array(
				'title' => 'Margin Ratio',
				'type' => 'text',
				'description' =>  'How lower USDT it can accept as successful?',
				'default' => '1'),
			'banned_coins' => array(
				'title' => 'Banned Coins',
				'type' => 'multiselect',
				'description' =>  'You can choose the coins you do not want to show to your customers.',
				'default' => 'no',
				'options'           => array(
					'USDT' => __('USDT', 'woocommerce' ),
					'USDT-ERC20' => __('USDT-ERC20', 'woocommerce' ),
					'USDT-TRC20' => __('USDT-TRC20', 'woocommerce' ),
					'USDT-BEP20' => __('USDT-BEP20', 'woocommerce' ),
					'BTC' => __('BTC', 'woocommerce' ),
					'ETH' => __('ETH', 'woocommerce' ),
					'TRX' => __('TRX', 'woocommerce' ),
					'BNB' => __('BNB', 'woocommerce' ),
					'LTC' => __('LTC', 'woocommerce' ),
				)
			),
			'white_label' => array(
				'title' => 'White Label',
				'type' => 'checkbox',
				'label' => 'Enable / Disable',
				'default' => 'no',
				'description'=>'Should your clients be directed to our payment page or should they pay on your page?'),
			'status_after_payment' => array(
				'title' => 'Status after successful payment',
				'type' => 'select',
				'description' =>  'Select order status after successful payment.',
				'default' => 'USDT',
				'options'           => array(
					'default' => __('Woocommerce Default', 'woocommerce' ),
					'completed' => __('Completed', 'woocommerce' ),
					'processing' => __('Processing', 'woocommerce' ),
					'on-hold' => __('On-hold', 'woocommerce' ),

				)
			),
		);
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		GLOBAL $woocommerce;

		$order = wc_get_order( $order_id );
		$order->add_order_note("Selected Payid19 For(order id) : " . $order->get_order_number()); //Sipariş Takibi
		
		if($this->get_option('white_label')=='no'){
			$result=$this->create_invoice($order_id);

			if(json_decode($result['body'])->status=='error'){
				$error = json_decode($result['body'])->message[0];
				wc_add_notice( $error, 'error' );
				$order->add_order_note("Error: " . $error); 
				$error = 1;
				return array(
					'result' => 'failure',
					'redirect' => ''
				);
			}else{
				$order->add_order_note("Customer redirected payment page or screen."); 
				return array(
					'result' => 'success',
					'redirect' => json_decode($result['body'])->message
				);
			}
		}else{
			return array(
				'result' => 'success',
				'redirect' => $order->get_checkout_payment_url(true),
			);
		}

	}

	public function payid19checkoutmodal(){
		if(is_wc_endpoint_url( 'order-pay' )) {
			$order_id = get_query_var('order-pay');
		}
		$order = wc_get_order( $order_id );
		$result=$this->create_invoice($order_id);
		$coins=json_decode(json_decode($result['body'])->message);
		require(WP_PLUGIN_DIR .'/payid19-com-payment-gateway/public/payid19-checkout-modal.php'); 
	}

	public function payid19_callback(){
		header( 'HTTP/1.1 200 OK' );
		
		GLOBAL $woocommerce;
		$data = json_decode(file_get_contents('php://input')); //catch request data
		$order_id = intval( $data->order_id );
        $order = new WC_Order( $order_id ); //select order
        if($data->privatekey!=$this->get_option('private_key')){       die;      }
        $order->add_order_note("Crypto Payment Completed"); 
        

        $status=$this->get_option('status_after_payment');

        if($status=='default'){
	        $order->payment_complete(); // Change the status of the order to paid
	    }else if($status=='completed'){
	    	wc_reduce_stock_levels($order_id);
	    	$order->update_status($status);
	    }else if($status=='processing'){
	    	wc_reduce_stock_levels($order_id);
	    	$order->update_status($status);
	    }else if($status=='on-hold'){
	    	wc_reduce_stock_levels($order_id);
	    	$order->update_status($status);
	    }else{
	        $order->payment_complete(); // Change the status of the order to paid
	    }
	    die();
	}

	public function create_invoice($order_id=null){
		GLOBAL $woocommerce;

		$order = wc_get_order( $order_id );
        	$testMode = $this->get_option('testMode'); //Test faturası mı?
        	if ($testMode == "yes") {
        		$testMode = 1;
        	} else {
        		$testMode = null;
        	}
        	$white_label=$this->get_option('white_label'); 
        	if ($white_label == "yes") {
        		$white_label = 1;
        	} else {
        		$white_label = null;
        	}
        	$post = [
        		'public_key' => $this->get_option('public_key'),
        		'private_key' => $this->get_option('private_key'),
        		'email' => $order->get_billing_email(),
        		'price_amount' => number_format($order->get_total(), 2, ".", ""),
        		'price_currency' => get_woocommerce_currency(),
        		'order_id' => $order_id,
        		'test' => $testMode,
        		'customer_id'=> $order->get_user_id(),
        		'cancel_url' => wc_get_cart_url(),
        		'success_url' => $order->get_checkout_order_received_url(),
        		'callback_url' => add_query_arg( 'wc-api', strtolower($this->id), get_site_url()."/" ),
        		'expiration_date' => 3,
        		'margin_ratio' => 1,
        		'white_label'=>$white_label,
        	];
        	if($this->get_option('banned_coins')!=''){
        		$post['banned_coins']=json_encode($this->get_option('banned_coins'));
        	}

        	return wp_remote_post($this->get_option('endpoint'),array('method' => 'POST','timeout' => 45,'redirection' => 5,	'httpversion' => '1.0',	'blocking'=> true,'headers'=> array(),'body'=> $post,'cookies'  => array()));
        }

        //Create message for blank api keys
        function payid19_apikey_notice() {
        	if($this->get_option('public_key')=='' || $this->get_option('private_key')==''){ ?>
        		<div class="notice notice-error is-dismissible">
        			<p>Payid19 Message: You need to set public and private keys. <a href="<?php get_admin_url();?>admin.php?page=wc-settings&tab=checkout&section=wc_gateway_payid19">Go Woocommerce->Settings->Payment Section!</a></p>
        		</div>
        	<?php } 
        } 

        function test_mode_controll() {
        	if( $this->get_option('testMode')=='yes'){ ?>
        		<div class="notice notice-warnin is-dismissible">
        			<p>Payid19 Message: Payid19 still working on test mode. <a href="<?php get_admin_url();?>admin.php?page=wc-settings&tab=checkout&section=wc_gateway_payid19">Go Woocommerce->Settings->Payment Section!</a></p>
        		</div>
        	<?php } 
        } 

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page() {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}

	/**
	 * Change payment complete order status to completed for payid19 orders.
	 *
	 * @since  3.1.0
	 * @param  string         $status Current order status.
	 * @param  int            $order_id Order ID.
	 * @param  WC_Order|false $order Order object.
	 * @return string
	 */
	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order && 'WC_Gateway_Payid19' === $order->get_payment_method() ) {
			$status = 'completed';
		}
		return $status;
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin  Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
		}
	}
	
	
}