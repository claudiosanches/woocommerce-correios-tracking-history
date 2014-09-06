<?php
/**
 * Plugin Name: WooCommerce Correios - Tracking History
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-correios
 * Description: Exibe o histórico de entrega do pedido nos correios utilizando o código de rastreamento salvo no WooCommerce Correios.
 * Author: Claudio Sanches
 * Author URI: http://claudiosmweb.com/
 * Version: 0.0.1-beta1
 * License: GPLv2 or later
 * Text Domain: woocommerce-correios-tracking-history
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Display the order shipping history.
 *
 * @param  int $order_id [description]
 *
 * @return string|null
 */
function wc_correios_order_tracking_history( $order_id ) {
	$tracking_code = get_post_meta( $order_id, 'correios_tracking', true );

	if ( $tracking_code ) {
		$url      = 'http://websro.correios.com.br/sro_bin/txect01$.Inexistente?P_LINGUA=001&P_TIPO=002&P_COD_LIS=' . $tracking_code;
		$response = wp_remote_get( $url, array( 'sslverify' => false, 'timeout' => 30 ) );

		if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
			$table = '';
			preg_match( '/<table  border cellpadding=1 hspace=10>.*<\/TABLE>/s', $response['body'], $table );

			if ( 1 == count( $table ) ) {
				echo '<h4>' . __( 'Histórico de entrega', 'woocommerce-correios-tracking-history' ) . '</h4>';

				// Clears the trash!
				$table = preg_replace( '/ border cellpadding=1 hspace=10/', 'class="correios-rastreamento"', $table );
				$table = preg_replace( '/<colgroup(.*)>/', '', $table );
				$table = str_replace( '<font FACE=Tahoma color=\'#CC0000\' size=2><b>', '<strong>', $table );
				$table = str_replace( '</b></font>', '</strong>', $table );
				$table = str_replace( '<FONT COLOR="000000">', '', $table );
				$table = str_replace( '</font>', '', $table );
				$table = str_replace( '</TABLE>', '</table>', $table );

				// Fix the encode.
				$table = utf8_encode( $table[0] );

				echo $table;
			}
		}
	}
}

add_action( 'woocommerce_view_order', 'wc_correios_order_tracking_history', 2 );
