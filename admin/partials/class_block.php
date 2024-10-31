<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Gateway_Payid19_Blocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'WC_Gateway_Payid19';// your payment gateway name
	
    public function initialize() {
        $this->settings = get_option( 'woocommerce_WC_Gateway_Payid19', [] );  
        //$this->gateway =  new WC_Gateway_Payid19();
		//print_r(get_option('woocommerce_WC_Gateway_Payid19_settings'));


    }

    public function is_active() {
        return true;
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            'WC_Gateway_Payid19-blocks-integration',
            plugin_dir_url(__FILE__) . 'payid19-checkout.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );
        if( function_exists( 'wp_set_script_translations' ) ) {            
            wp_set_script_translations( 'WC_Gateway_Payid19-blocks-integration');
            
        }
        return [ 'WC_Gateway_Payid19-blocks-integration' ];
    }

    public function get_payment_method_data() {
        return [
            'title' => get_option('woocommerce_WC_Gateway_Payid19_settings')['title'],
            'description' => get_option('woocommerce_WC_Gateway_Payid19_settings')['description'],
            'icon' => 'https://payid19.com/img/wc-logo.png',
        ];
    }
}