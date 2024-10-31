<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://payid19.com
 * @since             1.0.0
 * @package           Payid19
 *
 * @wordpress-plugin
 * Plugin Name:       Payid19 Crypto Payment Gateway
 * Plugin URI:        https://payid19.com
 * Description:       Crypto Payment Gateway you can accept USDT, Bitcoin, Ethereum, Bnb and TRX stable coins and withdraw as USDT.

 * Version:           2.0.0
 * Author:            Payid19
 * Author URI:        https://payid19.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       payid19
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PAYID19_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-payid19-activator.php
 */
function activate_payid19() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-payid19-activator.php';
	Payid19_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-payid19-deactivator.php
 */
function deactivate_payid19() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-payid19-deactivator.php';
	Payid19_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_payid19' );
register_deactivation_hook( __FILE__, 'deactivate_payid19' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-payid19.php';

add_filter( 'plugin_action_links_' .plugin_basename( __FILE__ ), 'add_action_links' );

function add_action_links ( $actions ) {
    $mylinks = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_payid19') . '">Settings</a>',
    );
    $actions = array_merge( $actions, $mylinks );
    return $actions;
}
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_payid19() {

	$plugin = new Payid19();
	$plugin->run();

}
run_payid19();
