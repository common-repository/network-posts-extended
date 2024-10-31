<?php

namespace NetworkPosts\Components;

class NetsPostsHtmlHelper {
	public static function get_date($date, $format){
		return '<span>' . $date->format($format) . '</span><br/>';
	}

	public static function create_link($url, $label, $open_in_new_tab = '', $class = ''){
		if( $class ){
			$class_attr = ' class="' . $class . '"';
		} else {
			$class_attr = '';
		}
		return '<a href="' . $url . '" ' . $open_in_new_tab . $class_attr .'>' . $label . '</a>';
	}

	/**
	 * @param $url
	 * @param $label
	 * @param $open_in_new_tab
	 * @param $class
	 *
	 * @return string
	 */
	public static function create_title_link( array $args ){
		$args = wp_parse_args( $args, array(
			'class'	=> '',
			'url'	=> '',
			'title' => '',
			'text'	=> '',
			'open_link_in_new_tab' => ''
		) );
		if( $args['class'] ){
			$class_attr = 'class="' . esc_attr( $args['class'] ) . '"';
		} else {
			$class_attr = '';
		}
		if( $args['title'] ){
			$title_attr = 'title="' . esc_attr( $args['title'] ) . '"';
		} else {
			$title_attr = '';
		}

		return sprintf(
			'<a href="%1$s" %2$s %3$s %4$s>%5$s</a>',
			$args['url'], $class_attr,
			$title_attr, $args['open_link_in_new_tab'], esc_html( $args['text'] )
		);
	}

	public static function create_term_link( int $id, string $name, string $open_in_new_tab = '', string $class = ''  ): string {
		$url = get_term_link( $id );
		return self::create_link($url, $name, $open_in_new_tab, $class);
	}

	public static function create_author_link($url, $author_label, $open_in_new_tab = '', $class = ''){
		$link = self::create_link($url, $author_label, $open_in_new_tab, $class);
		return '<span class="netsposts-author-label">' . __( 'Author', 'netsposts' ) . '</span> ' . $link;
	}

	public static function create_span($text, $class = '', $style = ''){
		if( $class ){
			$class_attr = ' class="' . $class . '"';
		} else {
			$class_attr = '';
		}
		if( $style ){
			$style_attr = ' style="' . $style . '"';
		} else {
			$style_attr = '';
		}
		return '<span' . $class_attr . $style_attr . '>' . $text . '</span>';
	}
}