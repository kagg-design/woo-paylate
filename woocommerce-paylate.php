<?php
/**
 * Plugin Name: Gateway for PayLate on WooCommerce
 * Plugin URI: https://wordpress.org/plugins/woo-paylate/
 * Description: WooCommerce payment gateway for PayLate service.
 * Author: KAGG Design
 * Version: 1.5
 * Author URI: https://kagg.eu/en/
 * Requires at least: 4.4
 * Tested up to: 6.0
 * Requires PHP: 5.6
 * WC requires at least: 3.0
 * WC tested up to: 6.5
 *
 * Text Domain: woo-paylate
 * Domain Path: /languages/
 *
 * @package woo-paylate
 * @author  KAGG Design
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( defined( 'WOO_PAYLATE_VERSION' ) ) {
	return;
}

/**
 * Plugin version.
 */
const WOO_PAYLATE_VERSION = '1.5';

/**
 * Path to the plugin dir.
 */
const WOO_PAYLATE_PATH = __DIR__;

/**
 * Plugin dir url.
 */
define( 'WOO_PAYLATE_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Main plugin file.
 */
const WOO_PAYLATE_FILE = __FILE__;

require_once WOO_PAYLATE_PATH . '/vendor/autoload.php';

/**
 * Return instance of WC_PayLate_Plugin.
 *
 * @return WC_PayLate_Plugin
 */
function wc_paylate_plugin() {

	return new WC_PayLate_Plugin();
}

wc_paylate_plugin()->maybe_run();
