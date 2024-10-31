<?php

/*
Plugin Name: Network Posts Ext
Plugin URI: https://wordpress.org/plugins/network-posts-extended/
Description: Network Posts Extended plugin enables you to share posts over WP Multi Site network.  You can display on any blog in your network the posts selected by taxonomy from any blogs including main.
Version: 7.7.2
Author: John Cardell, Andrey Islentiev
Author URI: https://www.johncardell.com
Text Domain: network-posts-extended
Domain Path: /language
*/

if ( realpath( __FILE__ ) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) {
	exit( 'Please don\'t access this file directly.' );
}
define( 'NETSPOSTS_MAIN_PLUGIN_FILE', __FILE__ );

use NetworkPosts\Components\db\NetsPostsQuery;
use NetworkPosts\Components\db\NetsPostsReviewQuery;
use NetworkPosts\Components\NetsPostsDBQuery;
use NetworkPosts\Components\NetsPostsHtmlHelper;
use NetworkPosts\Components\NetsPostsShortcodeContainer;
use NetworkPosts\Components\NetsPostsThumbnailManager;
use NetworkPosts\DB\Category\CategoryInclusionMode;
use NetworkPosts\DB\Category\NetsPostsCategoryQuery;

function netsposts_path( $file ) {
	return plugin_dir_path( __FILE__ ) . $file;
}


require_once netsposts_path( 'network-posts-init.php' );

function netsposts_shortcode( $atts ) {

	if ( is_string( $atts ) ) {
		$atts = array();
	}
	$shortcode_mgr = NetsPostsShortcodeContainer::newInstance( $atts );
	if( $shortcode_mgr->get_boolean( 'debug' ) ) {
		$GLOBALS['NETSPOSTS_DEBUG'] = true;
	}

	//$can_paginate = is_single() || is_singular() || is_page() || $shortcode_mgr->get_boolean( 'load_posts_dynamically' );
	//if ( ! $can_paginate ) {
		//$atts['paginate'] = false;
		//$shortcode_mgr->set_shortcode_attributes( $atts );
	//}

	global $wpdb;
	$db_manager = NetsPostsDBQuery::new_instance( $wpdb );

	$use_single_images_folder = get_option( 'use_single_images_folder', false );



	if ( ! empty( $_GET ) ) {
		$shortcode_mgr->add_attributes( $_GET );
	}

########  OUTPUT STAFF  ####################

	$titles_only = $shortcode_mgr->get_boolean( 'titles_only' );

	$thumbnail = $shortcode_mgr->get_boolean( 'thumbnail' );

	$paginate = $shortcode_mgr->get_boolean( 'paginate' );

	$auto_excerpt = $shortcode_mgr->get_boolean( 'auto_excerpt' );

	$show_author = $shortcode_mgr->get_boolean( 'show_author' );

	$full_text = $shortcode_mgr->get_boolean( 'full_text' );

	$prev_next = $shortcode_mgr->get_boolean( 'prev_next' );

	$random = $shortcode_mgr->get_boolean( 'random' );

	/* my updates are finished here */

	$price_woocommerce = false;

	$price_estore = false;

	$key_name = 'exclude_link_title_posts';

	if ( $shortcode_mgr->has_value( 'exclude_link_title_posts' ) ) {
		$exclude_title_links = $shortcode_mgr->split_array( 'exclude_link_title_posts', ',' );
	}

	global $img_sizes;

	global $wpdb;

	$key_name = 'include_price';

	if ( $shortcode_mgr->has_value( $key_name ) ) {
		$is_match = $shortcode_mgr->is_match( $key_name, '/(\|)+/' );

		if ( $is_match ) {
			$exs = $shortcode_mgr->split_array( $key_name, '|' );
		} else {
			$exs = [ $shortcode_mgr->get( $key_name ) ];
		}

		foreach ( $exs as $ex ) {
			if ( $ex == 'woocommerce' ) {
				$price_woocommerce = true;
			} elseif ( $ex == 'estore' ) {
				$price_estore = true;
			}
		}
	}

	$woocommerce_installed = $db_manager->is_woocommerce_installed();

	$estore_installed = $db_manager->is_estore_installed();

	global $table_prefix;

	if( ! defined( 'WOOCOMMERCE' ) ) {
		define( "WOOCOMMERCE", "woocommerce" );
	}
	if( ! defined( 'WPESTORE' ) ) {
		define( "WPESTORE", "estore" );
	}

	/* below is my updates */
	/*
	if ( is_archive() || $shortcode_mgr->get_boolean( 'load_posts_dynamically' ) ) {
		$page = isset( $_GET['npage'] ) ? (int)$_GET['npage'] : 1;
	} else {
		$page = get_query_var( 'paged' );

		if ( ! $page ) {
			$page = get_query_var( 'page' );
		}
	}
	*/
	$page = 1;

	if( isset( $_GET['npe-page'] ) ){
		$page = (int) $_GET['npe-page'];
	}

	$is_paginate = $page > 1 && $paginate;

	$blogs = netsposts_get_blogs( $shortcode_mgr );

	## Getting posts

	$postdata = array();

	$prices = array();

	if ( $blogs ) {
		global $wpdb;
		$posts_query = new NetsPostsQuery( $wpdb );
		$posts_query->include_blogs( $blogs );

		if ( $shortcode_mgr->has_value( 'days' ) ) {
			$days = $shortcode_mgr->get( 'days' );
			$posts_query->set_days( $days );
		}

		if ( $shortcode_mgr->has_value( 'limit' ) ) {
			$value = $shortcode_mgr->get( 'limit' );
			$posts_query->set_limit( intval( $value ) );
		}

		if ( $shortcode_mgr->has_value( 'offset' ) ) {
			$value = $shortcode_mgr->get( 'offset' );
			$posts_query->set_limit( intval( $value ) );
		}

		if ( $shortcode_mgr->has_value( 'random' ) ) {
			$posts_query->set_random();
		}

		if( $shortcode_mgr->has_value( 'author' ) ){
			$author_name = strtolower( $shortcode_mgr->get( 'author' ) );
			$author = null;
			if( $author_name === 'current_user' ){
				if( is_user_logged_in() ){
					$author = wp_get_current_user();
				}
			} else {
				$author = get_user_by( 'login', $author_name );
			}
			if( $author ) {
				$posts_query->set_author_id( $author->ID );
			}
		}

		if( $shortcode_mgr->has_value( 'exclude_author' ) ){
			$author_name = strtolower( $shortcode_mgr->get( 'exclude_author' ) );
			$author = get_user_by( 'login', $author_name );
			if( $author ) {
				$posts_query->exclude_author_id( $author->ID );
			}
		}

		if ( $shortcode_mgr->has_value( 'filter_by_title_keywords' ) ) {
			$keywords = $shortcode_mgr->split_array( 'filter_by_title_keywords', ',' );
			$posts_query->set_title_keywords( $keywords );
		}

		if ( $shortcode_mgr->get_boolean( 'page_has_no_child' ) ) {
			$posts_query->without_children();
		}
		$meta_keys = [];


		if ( $shortcode_mgr->has_value( 'post_type' ) ) {
			$post_type_array = $shortcode_mgr->split_array( 'post_type', ',' );
			$posts_query->set_post_type( $post_type_array );
		}

		if ( $shortcode_mgr->has_value( 'order_post_by_acf_date' ) ) {
			$sort_values = $shortcode_mgr->split_array( 'order_post_by_acf_date', ' ' );
			$meta_keys[] = $sort_values[0];
		}

		if ( $shortcode_mgr->has_value( 'show_after_date' ) ||
             $shortcode_mgr->has_value( 'exclude_all_past_events' ) ) {
			if ( $shortcode_mgr->has_value( 'show_after_date' ) ) {
				$filter_values = $shortcode_mgr->split_array( 'show_after_date', '::' );
				$filter_column = $filter_values[0];
				$date_str      = $filter_values[1];
				$date_format   = $shortcode_mgr->get( 'date_format' );
				$date          = DateTime::createFromFormat( $date_format, $date_str );
			} else {
				$filter_column = $shortcode_mgr->get( 'exclude_all_past_events' );
				$date          = new DateTime();
			}
			if ( $filter_column !== 'post_date' ) {
				$meta_keys[] = $filter_column;

				if ( $date ) {
					$posts_query->set_acf_date_filter_field( $filter_column );
					if ( class_exists( 'Jet_Engine' ) ) {
						$posts_query->filter_after_acf_date( $date->getTimestamp() );
					} else {
						$posts_query->filter_after_acf_date( $date->format( 'Ymd' ) );
					}

				}
			}
		}
		if ( $shortcode_mgr->has_value( 'show_before_date' ) ||
             $shortcode_mgr->has_value( 'show_past_events' ) ) {
			if ( $shortcode_mgr->has_value( 'show_before_date' ) ) {
				$filter_values = $shortcode_mgr->split_array( 'show_before_date', '::' );
				$filter_column = $filter_values[0];
				$date_str      = $filter_values[1];

				$date_format = $shortcode_mgr->get( 'date_format' );
				$date        = DateTime::createFromFormat( $date_format, $date_str );
			} else {
				$filter_column = $shortcode_mgr->get( 'show_past_events' );
				$date          = new DateTime();
			}
			if ( $filter_column !== 'post_date' ) {
				$meta_keys[] = $filter_column;

				if ( $date ) {
					$posts_query->set_acf_date_filter_field( $filter_column );
					$posts_query->filter_before_acf_date( $date->format( 'Ymd' ) );
				}
			}
		}
		if ( $shortcode_mgr->has_value( 'show_for_today' ) ) {
			$filter_column = $shortcode_mgr->get( 'show_for_today' );
			if ( $filter_column !== 'post_date' ) {
				$meta_keys[] = $filter_column;
				$posts_query->set_acf_date_filter_field( $filter_column );
				$date = date( 'Ymd' );
				$posts_query->filter_acf_date( $date );
			}
		}
		if ( $shortcode_mgr->has_value( 'show_only_after_x_days_old' ) ) {
			$after_days = $shortcode_mgr->get_int( 'show_only_after_x_days_old' );
			$posts_query->filter_after_days( $after_days );
		}

		$home_url = get_home_url();

		if ( $shortcode_mgr->get_boolean( 'remove_blog_prefix' ) ) {
			add_filter( 'pre_post_link', 'netsposts_remove_blog_prefix', 10, 3 );
		}

		if ( $shortcode_mgr->has_value( 'include_post' ) ) {
			$include_posts_id = $shortcode_mgr->split_array( 'include_post', ',' );
			foreach ( $blogs as $blog ) {
				$posts_query->include_blog_posts( $blog, $include_posts_id );
			}
		}

		if ( $shortcode_mgr->has_value( 'exclude_post' ) ) {
			$exclude_posts = $shortcode_mgr->split_array( 'exclude_post', ',' );
			foreach ( $blogs as $blog ) {
				$posts_query->exclude_posts( $blog, $exclude_posts );
			}
		}

		if ( $shortcode_mgr->has_value( 'order_post_by' ) ) {
			$tab_order_by1 = $shortcode_mgr->split_array( 'order_post_by', ' ' );

			$ordad = ( $tab_order_by1[1] ) ? $tab_order_by1[1] : "ASC";
			$posts_query->set_sort_type( $ordad );

			if ( $tab_order_by1[0] == "date_order" ) {
				$ordad0 = "post_date";
			} elseif ( $tab_order_by1[0] == "alphabetical_order" ) {
				$ordad0 = "post_title";
			} else {
				$ordad0 = "ID";
			}
			$posts_query->set_order_by( $ordad0 );
		} elseif ( $shortcode_mgr->has_value( 'order_post_by_acf_date' ) ) {
			$tab_order_by1 = $shortcode_mgr->split_array( 'order_post_by_acf_date', ' ' );
			if ( strtoupper( $tab_order_by1[1] ) == "DESC" ) {
				$ordad1 = 'desc';
			} else {
				$ordad1 = 'asc';
			}
			$posts_query->set_acf_date_filter_field( $tab_order_by1[0] );
			$posts_query->order_by_acf( $ordad1 );
		} elseif ( $shortcode_mgr->has_value( 'order_by_acf' ) ) {
			$tab_order_by1 = $shortcode_mgr->split_array( 'order_by_acf', ' ' );
			if ( strtoupper( $tab_order_by1[1] ) == "DESC" ) {
				$ordad1 = 'desc';
			} else {
				$ordad1 = 'asc';
			}
			$posts_query->set_primary_acf_field( $tab_order_by1[0] );
			$posts_query->order_by_acf( $ordad1 );
		}
		if ( $shortcode_mgr->get_boolean( 'hide_password_protected_posts' ) ){
			$posts_query->exclude_protected_posts();
		}

		$category_query = new NetsPostsCategoryQuery( $wpdb );
		$category_query->include_blogs( $blogs );

		if ( $shortcode_mgr->get_boolean( 'show_all_taxonomy_types' ) ) {
			$taxonomy_types = get_taxonomies();
			$category_query->set_taxonomy_type( $taxonomy_types );
		} elseif ( $shortcode_mgr->has_value( 'taxonomy_type' ) ) {
			$category_query->set_taxonomy_type( $shortcode_mgr->split_array( 'taxonomy_type', ',' ) );
		} else {
			$category_query->set_taxonomy_type( array( 'category', 'tag' ) );
		}
		if ( $shortcode_mgr->get_boolean( 'show_all_taxonomies' ) ) {
			$taxonomy = array();
			$mode = CategoryInclusionMode::INCLUDE_ANY;
		} elseif ( $shortcode_mgr->has_value( 'taxonomy' ) ) {
			$taxonomy             = $shortcode_mgr->split_array( 'taxonomy', ',' );
			if( $shortcode_mgr->get_boolean( 'must_include_categories' ) ){
				$mode = CategoryInclusionMode::INCLUDE_ALL;
			} elseif ( $shortcode_mgr->get_boolean( 'must_be_in_taxonomies_only' ) ){
				$mode = CategoryInclusionMode::INCLUDE_ONLY;
			} else {
				$mode = CategoryInclusionMode::INCLUDE_ANY;
			}
		}
		if( isset( $taxonomy ) ){
			try {
				$taxonomy_posts_table = $category_query->get_query_table( $taxonomy, $mode );
			} catch ( Exception $e ) {
				error_log( $e->getMessage() );
				$taxonomy_posts_table = false;
			}
		}

		if ( isset( $taxonomy_posts_table ) && $taxonomy_posts_table ) {
			$posts_query->include_posts_from_table( $taxonomy_posts_table );
		}
		if ( $shortcode_mgr->has_value( 'exclude_taxonomy' ) ) {
			$exclude_taxonomy       = $shortcode_mgr->split_array( 'exclude_taxonomy', ',' );
			try {
				$exclude_taxonomy_posts = $category_query->get_query_table( $exclude_taxonomy );
				$posts_query->exclude_posts_from_table( $exclude_taxonomy_posts );
			} catch ( Exception $e ) {
				error_log( $e->getMessage() );
			}
		}

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$post_ids   = $posts_query->get_ids();
			$blog_posts = array();
			foreach ( $post_ids as $post ) {
				if ( ! isset( $blog_posts[ $post['blog_id'] ] ) ) {
					$blog_posts[ $post['blog_id'] ] = array();
				}
				$blog_posts[ $post['blog_id'] ][] = $post['ID'];
			}

			$translation_query = new NetsPostsQuery( $wpdb );
			$translation_query->include_blogs( array_keys( $blog_posts ) );
			if( isset( $ordad ) ){
				$translation_query->set_sort_type( $ordad );
			}
			if( isset( $ordad0 ) ){
				$translation_query->set_order_by( $ordad0 );
			}
			foreach ( $blog_posts as $blog_id => $posts ) {
				$translations = array();
				switch_to_blog( $blog_id );
				foreach ( $posts as $post_id ) {
					$type           = get_post_type( $post_id );
					$translation_id = apply_filters( 'wpml_object_id', $post_id, $type, true );
					if( $translation_id ){
						$translations[] = $translation_id;
					}
				}
				restore_current_blog();
				$translation_query->include_blog_posts( $blog_id, $translations );
			}
			$the_post = $translation_query->get_posts();
		} else {
			$the_post = $posts_query->get_posts();
		}
		$category_query->drop_temp_tables();

		$count = count( $the_post );

		for ( $i = 0; $i < $count; $i ++ ) {
			if ( isset( $the_post[ $i ] ) ) {

				$item = $the_post[ $i ];
				switch_to_blog( $item['blog_id'] );
				if ( $the_post[ $i ]['post_type'] === 'product' ) {
					if ( defined( 'WOOCOMMERCE' ) ) {
						$id                           = $the_post[ $i ]['ID'];
						$the_post[ $i ]['categories'] = wp_get_post_terms( $id, 'product_cat' );
						$the_post[ $i ]['terms']      = wp_get_post_terms( $id, 'product_tag' );
					}
				} else {
					$the_post[ $i ]['categories'] = wp_get_post_categories( $item['ID'],
						array( 'fields' => 'all' ) );

					$the_post[ $i ]['terms'] = wp_get_post_terms( $item['ID'] );
				}
				$the_post[ $i ]['guid'] = get_permalink( $item['ID'] );

				if ( $shortcode_mgr->has_value( 'show_custom_taxonomies' ) ) {
					$show_custom_taxonomies = $shortcode_mgr->get_boolean( 'show_custom_taxonomies' );
					if ( $show_custom_taxonomies ) {
						$the_post[ $i ]['custom_taxonomies'] = netsposts_get_post_custom_taxonomies( $item['ID'] );
					} else {
						$custom_taxonomy                     = $shortcode_mgr->split_array( 'show_custom_taxonomies', ',' );
						$the_post[ $i ]['custom_taxonomies'] = netsposts_get_post_taxonomies( $item['ID'], $custom_taxonomy );
					}
				} else {
					$the_post[ $i ]['custom_taxonomies'] = netsposts_get_post_custom_taxonomies( $item['ID'] );
				}

				if ( $shortcode_mgr->get( 'domain_mapping' ) === 'home_url' ) {
					$site_url               = get_site_url();
					$the_post[ $i ]['guid'] = str_replace( $site_url, $home_url, $the_post[ $i ]['guid'] );
				}
			}
			restore_current_blog();
		}

		$postdata = $the_post;

	}
	/* exclude latest n elements from categories */
	if ( $shortcode_mgr->has_value( 'taxonomy_offsets' ) ) {

		$taxonomy_offsets = $shortcode_mgr->split_array( 'taxonomy_offsets', ',' );

		if ( $shortcode_mgr->has_value( 'taxonomy_offset_names' ) ) {
			$skipped_categories = $shortcode_mgr->split_array( 'taxonomy_offset_names', ',' );
		} elseif ( $shortcode_mgr->has_value( 'taxonomy' ) ) {
			$skipped_categories = $shortcode_mgr->split_array( 'taxonomy', ',' );
		} else {
			$skipped_categories = [];
		}

		if ( count( $skipped_categories ) > 0 ) {


			$skipped = [];
			$tmp     = [];

			$taxonomy_offsets = array_slice( $taxonomy_offsets, 0, count( $skipped_categories ) );

			$skipped_category_regex = array_map( function ( $cat_name ) {
				$name = str_replace( '%', '', preg_quote( $cat_name ) );

				return '/\b' . $name . '\b/i';
			}, $skipped_categories );

			for ( $i = 0; $i < count( $skipped_categories ); $i ++ ) {
				$skipped[ $i ] = 0;
			}

			$taxonomy_offset_type = strtolower( $shortcode_mgr->get( 'taxonomy_offset_type' ) );
			for ( $k = 0; $k < count( $postdata ); $k ++ ) {
				$post  = $postdata[ $k ];
				$found = false;
				for ( $i = 0; $i < count( $skipped_categories ); $i ++ ) {
					if ( isset( $taxonomy_offsets[ $i ] ) ) {
						$to_skip = $taxonomy_offsets[ $i ];
					} else {
						$to_skip = 0;
					}
					if ( $skipped[ $i ] < $to_skip ) {
						if ( $taxonomy_offset_type === 'category' || $taxonomy_offset_type === 'any' ) {
							if ( $post['categories'] ) {
								foreach ( $post['categories'] as $category ) {
									$has_skipped_category = $category->slug === $skipped_categories[ $i ] ||
                                                            preg_match( $skipped_category_regex[ $i ], $category->name );
									if ( $has_skipped_category ) {
										$skipped[ $i ] ++;
										$found = true;
										$tmp[] = $k;
										break;
									}
								}
							}
						}
						if ( $found ) {
							break;
						}
						if ( $taxonomy_offset_type === 'tag' || $taxonomy_offset_type === 'any' ) {
							if ( $post['terms'] ) {
								foreach ( $post['terms'] as $term ) {
									$has_skipped_tags = $term->slug === $skipped_categories[ $i ] ||
                                                        preg_match( $skipped_category_regex[ $i ], $term->name );
									if ( $has_skipped_tags ) {
										$skipped[ $i ] ++;
										$found = true;
										$tmp[] = $k;
										break;
									}
								}
							}
						}
					}
					if ( $found ) {
						break;
					}
				}

				$all_skipped = true;
				for ( $j = 0; $j < count( $skipped ); $j ++ ) {
					$all_skipped = $all_skipped && $skipped[ $j ] == $taxonomy_offsets[ $j ];
				}
				if ( $all_skipped ) {
					break;
				}
			}
			foreach ( $tmp as $skipped_idx ) {
				unset( $postdata[ $skipped_idx ] );
			}
		}
	}

	if ( $shortcode_mgr->get_boolean( 'remove_blog_prefix' ) ) {
		remove_filter( 'pre_post_link', 'netsposts_remove_blog_prefix' );
	}

	$list = $shortcode_mgr->get( 'list' );

	if ( is_array( $postdata ) ) {
		$skip = 0;

		if ( $shortcode_mgr->has_value( 'number_latest_x_posts_excluded' ) ) {
			$skip = (int) $shortcode_mgr->get( 'number_latest_x_posts_excluded' );
		}

		if ( $paginate ) {
			$total_records = count( $postdata ) - $skip;

			$total_pages = ceil( $total_records / $list );
			$postdata    = array_slice( $postdata, ( $page - 1 ) * $list + $skip, $list );
		} /* below is my updates */

		else {
			$postdata = array_slice( $postdata, $skip, $list );
		}
	}

	$html = '<div class="netsposts-menu">';

	if ( $shortcode_mgr->has_value( 'menu_name' ) ) {
		$menu = array(
            'menu'            => $shortcode_mgr->get( 'menu_name' ),
            'menu_class'      => $shortcode_mgr->get( 'menu_class' ),
            'container_class' => $shortcode_mgr->get( 'container_class' )
		);

		wp_nav_menu( $menu );
	}

	if ( $shortcode_mgr->has_value( 'link_open_new_window' ) ) {
		$link_open_new_window = strtolower( $shortcode_mgr->get( 'link_open_new_window' ) ) === 'true' ? true
			: $shortcode_mgr->split_array( 'link_open_new_window', ',' );
	} else {
		$link_open_new_window = false;
	}

	$html .= '</div>';

	if ( $postdata ) {

		if ( $shortcode_mgr->has_value( 'include_post_meta' ) ) {
			$keys      = $shortcode_mgr->split_array( 'include_post_meta', ',' );
			$meta_keys = array_unique( $keys );
			if ( ! empty( $meta_keys ) ) {
				foreach ( $postdata as &$post ) {
					switch_to_blog( $post['blog_id'] );
					foreach ( $meta_keys as $meta ) {
						$post[ $meta ] = get_post_meta( $post['ID'], $meta, true );
					}
					restore_current_blog();
				}
			}
		}

		$show_categories = $shortcode_mgr->get_boolean( 'show_categories' );

		$screen_classes = array( 'netsposts-screen' );
		if ( $shortcode_mgr->get_boolean( 'load_posts_dynamically' ) ) {
			$screen_classes[] = 'ajax_load';
		}
		$screen_classes = join( ' ', $screen_classes );

		if ( $shortcode_mgr->has_value( 'shortcode_id' ) ) {
			$shortcode_id = $shortcode_mgr->get( 'shortcode_id' );
			$html         .= '<div id="' . $shortcode_id . '" class="' . $screen_classes . '">';
		} else {
			$html .= '<div class="' . $screen_classes . '">';
		}

		$html .= '<div class="netsposts-block-wrapper current">';

		$html .= $shortcode_mgr->get_html( 'wrap_custom_title_start' );
		if( $shortcode_mgr->has_value( 'main_title' ) ) {
			$main_title_text = esc_html( $shortcode_mgr->get( 'main_title' ) );
			if( $shortcode_mgr->has_value( 'main_title_link' ) ) {
				$main_title_text = NetsPostsHtmlHelper::create_link(
					esc_url( $shortcode_mgr->get( 'main_title_link' ) ),
					$main_title_text
				);
			}
			$html .= '<h2 class="netsposts-main-title">' . $main_title_text . '</h2>';
		}

		if ( $shortcode_mgr->has_value( 'title' ) ) {
			$html .= '<h1 class="netsposts-title">' . $shortcode_mgr->get( 'title' ) . '</h1>';
		}
		$html .= $shortcode_mgr->get_html( 'wrap_custom_title_end' );

		$html .= '<div class="netsposts-items">';
		if ( $shortcode_mgr->has_value( 'post_height' ) ) {
			$post_height    = $shortcode_mgr->get( "post_height" );
			$height_content = "height: " . $post_height . "px;";
		} else {
			$height_content = "";
		}

		/* Moved to Line 649
		if ( $shortcode_mgr->has_value( 'title' ) ) {
			$html .= '<span class="netsposts-title">' . $shortcode_mgr->get( 'title' ) . '</span>';
		}
		*/

		$use_layout        = $shortcode_mgr->get( 'use_layout' );
		$use_inline_layout = isset( $use_layout ) && strtolower( $use_layout ) == "inline";

		if( $shortcode_mgr->has_value( 'wrap_content_start' ) ){
			$html .= $shortcode_mgr->get_html( 'wrap_content_start' );
		}
		$hide_all_links = get_option( 'hide_all_readmore_links' );
		$pages_string = get_option( 'hide_readmore_link_pages' );
		if( $shortcode_mgr->has_value( 'date_format' ) ){
			$format = $shortcode_mgr->get( 'date_format' );
			if( $format === 'settings' ){
				$format = get_option( 'date_format' );
			}
		}
		else{
			$format = 'M j';
		}
		$modify_image_url = ! get_option( 'use_single_images_folder', false );

		$review_query = new NetsPostsReviewQuery( $wpdb );
		foreach ( $postdata as $key => $the_post ) {

			if ( $shortcode_mgr->get_boolean( 'show_rating' ) ) {
				if(
					$review_query->is_installed( $the_post['blog_id'] )
				) {
					$the_post['rating'] = $review_query->get_post_avg_rating( $the_post['blog_id'], $the_post['ID'] );
				}
			}

			$open_link_in_new_tab = $link_open_new_window === true ||
                                    is_array( $link_open_new_window ) &&
                                    in_array( $the_post['ID'], $link_open_new_window ) ? ' target="_blank"' : '';

			$blog_details = get_blog_details( $the_post['blog_id'] );

			$blog_name = $blog_details->blogname;

			$blog_url = $blog_details->siteurl;

			if ( $shortcode_mgr->has_value( 'wrap_start' ) ) {
				$html .= $shortcode_mgr->get_html( 'wrap_start' );
			}

			$content_classes_arr = array(
                'blog-' . $the_post['blog_id'],
                'post-' . $the_post['ID']
			);
			foreach ( $the_post['categories'] as $category ) {
				$content_classes_arr[] = 'category-' . $category->slug;
			}
			foreach ( $the_post['terms'] as $term ) {
				$content_classes_arr[] = 'tag-' . $term->slug;
			}
			foreach ( $the_post['custom_taxonomies'] as $taxonomy ) {
				$content_classes_arr[] = 'custom-taxonomy-' . $taxonomy->slug;
			}

			$content_classes = join( ' ', $content_classes_arr );

			$html .= '<div class="netsposts-content ' . $content_classes . '" style="' . $height_content . '">';

			switch_to_blog( $the_post['blog_id'] );
			ob_start();
			if ( $use_inline_layout ) {
				include( POST_VIEWS_PATH . '/layout/layout_inline.php' );
			} else {
				include( POST_VIEWS_PATH . '/layout/layout_default.php' );
			}
			restore_current_blog();
			$html .= ob_get_clean();
			$html .= '</div>';//end of netsposts-content

			if ( $shortcode_mgr->has_value( 'wrap_end' ) ) {
				$html     .= $shortcode_mgr->get_html( 'wrap_end' );
			}
		}
		if( $shortcode_mgr->has_value( 'wrap_content_end' ) ){
			$html .= $shortcode_mgr->get_html( 'wrap_content_end' );
		}
		$html .= '<div class="end-netsposts-content"></div>';
		$html .= '</div>'; // end of .netsposts-items
		if ( ( $paginate ) and ( $total_pages > 1 ) ) {
			$html .= '<div class="netsposts-paginate">';

			$big = 999999999;
			$pagination = paginate_links( array(

				//'base' => $base,

                'format' => '?npe-page=%#%',

                'current' => $page,

                'total' => $total_pages,

                'show_all' => ! $shortcode_mgr->get_boolean( 'prev_next' ),

                'prev_next' => $shortcode_mgr->get_boolean( 'prev_next' ),

                'prev_text' => __( $shortcode_mgr->get( 'prev' ) ),

                'next_text' => __( $shortcode_mgr->get( 'next' ) ),

                'end_size' => $shortcode_mgr->get( 'end_size' ),

                'mid_size' => $shortcode_mgr->get( 'mid_size' )

			) );
			if ( is_single() ) {
				$url        = get_permalink();
				$pagination = netsposts_modify_pagination( $url, $pagination, $page );
			}
			$html .= $pagination;

			$html .= '</div>';
		}
		$html .= '</div>'; //end of netsposts-block-wrapper
		if ( $shortcode_mgr->get_boolean( 'load_posts_dynamically' ) ) {
			if ( $shortcode_mgr->has_value( 'posts_preloader_icon' ) ) {
				$preloader_url = $shortcode_mgr->get( 'posts_preloader_icon' );
			} else {
				$preloader_url = plugins_url( 'pictures/preloader.svg', __FILE__ );
			}
			if ( $shortcode_mgr->get_boolean( 'show_preloader_icon' ) ) {
				$html .= '<div class="netsposts-preloader hidden"><img alt="Loading" src="' . $preloader_url . '"></div>';
			} else {
				$html .= '<div class="netsposts-preloader hidden"></div>';
			}
		}
		$html .= '</div>'; // .netsposts-screen
	}
	unset( $GLOBALS['NETSPOSTS_DEBUG'] );
	return $html;
}

function netsposts_get_blogs( NetsPostsShortcodeContainer $shortcode_mgr ) {
	$site_in = '';
	if ( $shortcode_mgr->has_value( 'include_blog' ) ) {
		$site_in = 'AND blog_id IN (' . $shortcode_mgr->get( 'include_blog' ) . ')';
	}
	$site_not_in = '';
	if ( $shortcode_mgr->has_value( 'exclude_blog' ) ) {
		$site_not_in = 'AND blog_id NOT IN(' . $shortcode_mgr->get( 'exclude_blog' ) . ')';
	}
	global $wpdb;
	$prefix = $wpdb->base_prefix;
	$query = "SELECT blog_id FROM ${prefix}blogs WHERE public != 0 AND archived=0 AND spam=0 AND deleted=0 $site_in $site_not_in LIMIT 1000";
	$rows = $wpdb->get_results( $query, ARRAY_A );
	if( $rows ){
		return array_map( function( $item ){ return intval( $item['blog_id'] ); }, $rows );
	}
	return array();
}

/*
function netsposts_get_blogs( NetsPostsShortcodeContainer $shortcode_mgr ) {
	$attrs = array(
		'fields'   => 'ids',
		'public'   => 1,
		'archived' => 0,
		'spam'     => 0,
		'deleted'  => 0,
		'number'   => 1000
	);

	if ( $shortcode_mgr->has_value( 'include_blog' ) ) {
		$included          = $shortcode_mgr->split_array( 'include_blog', ',' );
		$attrs['site__in'] = $included;
	}
	if ( $shortcode_mgr->has_value( 'exclude_blog' ) ) {
		$exclude_arr           = $shortcode_mgr->split_array( 'exclude_blog', ',' );
		$attrs['site__not_in'] = $exclude_arr;
	}

	return get_sites( $attrs );
}
*/

function netsposts_remove_blog_prefix( $permalink, $post, $leavename ) {
	if ( strpos( $permalink, '/blog' ) === 0 ) {
		return mb_substr( $permalink, 5 );
	}

	return $permalink;
}

##########################################################

function netsposts_get_thumbnail( $post_id, $size, $image_class ) {
	$compress_images          = get_option( 'use_compressed_images', false );

	return NetsPostsThumbnailManager::get_thumbnail(
		$post_id,
		$size,
		array(
            'image_class'              => $image_class,
            'compress_images'          => $compress_images
		)
	);
}


function netsposts_create_estore_product_thumbnail( $image_url, $alt, $size = 'thumbnail', $image_class = '' ) {
	return NetsPostsThumbnailManager::get_estore_product_thumbnail( $image_url, $alt, $size, $image_class );
}


function netsposts_debug( string $text ): void {
	$file_path = plugin_dir_path( __FILE__ ) . 'log.txt';
	file_put_contents( $file_path, $text );
}
