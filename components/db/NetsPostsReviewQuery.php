<?php


namespace NetworkPosts\Components\db;


class NetsPostsReviewQuery {

	static $blogs_installed = array();
	/**
	 * @var \wpdb
	 */
	private $db;

	public function __construct( \wpdb $db ) {
		$this->db = $db;
	}

	public function get_post_avg_rating( $blog_id,  $id ) {
		$table_name = self::get_table_name( $blog_id, $this->db->base_prefix );
		$query = 'SELECT ROUND(AVG(rating),1) AS rating FROM ' . $table_name . ' WHERE post_id=' . intval( $id );
		$record = $this->db->get_results( $query, ARRAY_A );
		return intval( $record[0]['rating'] );
	}

	private static function get_table_name( $blog_id, $base_prefix ) {
		$table_prefix = $base_prefix;
		if( $blog_id > 1 ){
			$table_prefix .= $blog_id . '_';
		}
		return $table_prefix . 'reviews';
	}

	public function is_installed( $blog_id ) {
		if( isset( self::$blogs_installed[ $blog_id ] ) ) {
			return self::$blogs_installed[ $blog_id ];
		}

		$table = self::get_table_name( $blog_id, $this->db->base_prefix );
		$rows = $this->db->get_results( 'SHOW TABLES LIKE "' . $table . '";' );
		self::$blogs_installed[ $blog_id ] = ! empty( $rows );

		return self::$blogs_installed[ $blog_id ];
	}
}