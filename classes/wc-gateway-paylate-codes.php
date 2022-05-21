<?php
/**
 * WooCommerce PayLate Gateway Codes.
 *
 * @package woo-paylate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get result message.
 *
 * @param string $result Result code.
 *
 * @return string
 */
function wc_paylate_get_result_message( $result ) {
	$result_messages = [
		'-1' => __( 'Order is cancelled by client', 'woo-paylate' ),
		'0'  => __( 'PayLate requests to ship order', 'woo-paylate' ),
		'1'  => __( 'PayLate payment approved', 'woo-paylate' ),
	];

	if ( array_key_exists( $result, $result_messages ) ) {
		return $result_messages[ $result ];
	}

	return __( 'Unknown error', 'woo-paylate' );
}
