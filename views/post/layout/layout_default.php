<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 03.04.2017
 * Time: 15:31
 */

if ( ! defined( 'POST_VIEWS_PATH' ) ) {
	die();
}

include POST_VIEWS_PATH . '/header.php';

if ( ! $shortcode_mgr->get_boolean( 'titles_only' ) ) {

	if ( $shortcode_mgr->get_boolean( 'thumbnail' ) ) {

		include POST_VIEWS_PATH . '/thumbnail.php';

	}


	include POST_VIEWS_PATH . '/content.php';

	if ( $price_woocommerce || $price_estore ) {

		include POST_VIEWS_PATH . '/commerce.php';
	}

}

