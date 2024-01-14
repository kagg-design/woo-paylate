<?php
/**
 * Plugin Name: Gateway for PayLate on WooCommerce
 * Plugin URI: https://wordpress.org/plugins/woo-paylate/
 * Description: WooCommerce payment gateway for PayLate service.
 * Author: KAGG Design
 * Version: 1.5.3
 * Author URI: https://kagg.eu/en/
 * Requires at least: 4.4
 * Tested up to: 6.4
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * WC requires at least: 3.0
 * WC tested up to: 8.4
 *
 * Text Domain: woo-paylate
 * Domain Path: /languages/
 *
 * @package woo-paylate
 * @author  KAGG Design
 */

use KAGG\Paylate\Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( defined( 'WOO_PAYLATE_VERSION' ) ) {
	return;
}

/**
 * Plugin version.
 */
const WOO_PAYLATE_VERSION = '1.5.3';

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
 * Return instance of Main.
 *
 * @return Main
 */
function wc_paylate_plugin() {
	static $plugin;

	if ( ! $plugin ) {
		$plugin = new Main();
	}

	return $plugin;
}

wc_paylate_plugin()->init();
