<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 04.04.2017
 * Time: 8:13
 */

if ( ! defined( 'POST_VIEWS_PATH' ) ) {
	die();
}


$wrap_price_start = $shortcode_mgr->get( 'wrap_price_start' );
$wrap_price_end   = $shortcode_mgr->get( 'wrap_price_end' );

if ( $the_post['post_type'] == 'product' && $price_woocommerce == true ) {

	if( ! function_exists( 'wc_get_product' ) ){
		include_once ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php';
	}

	$_current_product = new WC_Product( $the_post['ID'] );

	if ( $_current_product ) {

		$html .= htmlspecialchars_decode( $wrap_price_start );

		$price = $_current_product->get_regular_price();

		if( is_numeric( $price ) ) {

			$html .= '<p class="netsposts-price">' . wc_price( floatval( $price ) ) . '</p>';

		}

		$html .= htmlspecialchars_decode( $wrap_price_end );

	}

} else if ( $the_post['post_type'] == 'estore' && $price_estore == true && $estore_installed ) {

	$html .= htmlspecialchars_decode( $wrap_price_start );

	$html .= '<p class="netsposts-price">' . wc_price( $the_post['price'] ) . '</p>';

	$html .= htmlspecialchars_decode( $wrap_price_end );

}