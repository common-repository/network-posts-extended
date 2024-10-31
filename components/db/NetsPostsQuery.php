<?php


namespace NetworkPosts\Components\db;

use NetworkPosts\db\NetsPostsDbHelper;
use WPDB;

class NetsPostsQuery {
	private static $TEMP_TABLE_NAME = 'network_posts_results';

	/**
	 * @var WPDB
	 */
	protected $db;
	protected $meta_query;
	protected $condition_builder;

	protected $blogs = array();

	protected $included_posts = array();
	protected $excluded_posts = array();
	protected $limit = 999;
	protected $offset = 0;
	protected $meta_keys = array();
	protected $order_by = false;
	protected $sort_type = 'DESC';
	protected $is_random = false;

	protected $load_only_ids = false;
	protected $posts_without_children = false;
	/**
	 * @var string
	 */
	private $include_posts_table;
	/**
	 * @var string
	 */
	private $exclude_posts_table;
	private $current_blog_id = 1;

	public function __construct( \WPDB $db ) {
		$this->set_db( $db );
		$this->condition_builder = new NetsPostsQueryConditionBuilder( $db->base_prefix );
		$this->meta_query = new NetsPostsMetaQueryBuilder( $db->base_prefix );
	}

	public function include_blogs( array $blogs ): void {
		$this->condition_builder->include_blogs( $blogs );
		$this->meta_query->include_blogs( $blogs );
	}

	public function exclude_blog( $blog_id ): void {
		$this->condition_builder->exclude_blog( $blog_id );
	}

	public function include_blog_posts( int $blog_id, array $included_posts ): void {
		$this->condition_builder->include_blog_posts( $blog_id, $included_posts );
	}

	public function get_included_posts( int $blog_id ): array {
		return $this->condition_builder->get_included_posts( $blog_id );
	}

	public function exclude_posts( int $blog_id, array $excluded_posts ): void {
		$this->condition_builder->exclude_posts( $blog_id, $excluded_posts );
	}

	public function get_excluded_posts(): array {
		return $this->condition_builder->get_excluded_posts();
	}

	public function include_posts_from_table( string $table ) {
		$this->include_posts_table = $table;
	}

	public function exclude_posts_from_table( string $table ) {
		$this->exclude_posts_table = $table;
	}

	/**
	 * @param int $limit
	 */
	public function set_limit( int $limit ): void {
		$this->limit = $limit;
	}

	/**
	 * @param int $offset
	 */
	public function set_offset( int $offset ): void {
		$this->condition_builder->set_offset( $offset );
	}


	/**
	 * @param mixed $post_type
	 */
	public function set_post_type( $post_type ): void {
		$this->condition_builder->set_post_type( $post_type );
	}

	/**
	 * @param string $days
	 */
	public function set_days( string $days ): void {
		$this->condition_builder->set_days( $days );
	}

	public function filter_after_days( int $after_days ) {
		$this->condition_builder->filter_after_days( $after_days );
	}

	/**
	 * @param string $order_by
	 */
	public function set_order_by( string $order_by ): void {
		$this->order_by = $order_by;
	}

	/**
	 * @param string $type
	 */
	public function set_sort_type( string $type ): void {
		$this->sort_type = $type;
	}

	/**
	 * @param array $title_keywords
	 */
	public function set_title_keywords( array $title_keywords ): void {
		$this->condition_builder->set_title_keywords( $title_keywords );
	}

	public function set_author_id( int $author ): void {
		$this->condition_builder->set_author_id( $author );
	}

	public function exclude_author_id( int $author ): void {
		$this->condition_builder->exclude_author_id( $author );
	}

	public function set_random(): void {
		$this->is_random = true;
	}


	/**
	 * @param string $acf_date_filter_field
	 */
	public function set_acf_date_filter_field( string $acf_date_filter_field ): void {
		$this->meta_query->set_primary_acf_field( $acf_date_filter_field );
	}

	/**
	 * @param string $acf_date
	 */
	public function filter_acf_date( string $acf_date ): void {
		$this->meta_query->filter_acf_date( $acf_date );
	}

	/**
	 * @param string $date
	 */
	public function filter_before_acf_date( string $date ): void {
		$this->meta_query->filter_before_acf_date( $date );
	}

	/**
	 * @param string $after_acf_date
	 */
	public function filter_after_acf_date( string $after_acf_date ): void {
		$this->meta_query->filter_after_acf_date( $after_acf_date );
	}

	public function set_primary_acf_field( string $field ): void {
		$this->meta_query->set_primary_acf_field( $field );
	}

	public function order_by_acf( string $order ): void {
		$this->meta_query->order_by_acf( $order );
	}

	public function get_acf_date_field(): string {
		return $this->meta_query->get_primary_acf__field();
	}

	public function without_children() {
		$this->posts_without_children = true;
	}


	public function get_posts(): array {
		$results = $this->get_rows();

		if ( $results ) {
			if ( $this->posts_without_children ) {
				$ids        = array_map( function ( $record ) {
					return $record['ID'];
				}, $results );
				$blogs = $this->condition_builder->get_blogs();
				foreach( $blogs as $blog ) {
					$this->current_blog_id = $blog;
					$parent_ids = $this->get_parents( $ids );

					$results    = array_filter( $results, function ( $post ) use ( $parent_ids ) {
						return ! array_has_value( $post['ID'], $parent_ids );
					} );
					$results    = array_values( $results );
				}
			}
		}

		return $results;
	}

	protected function get_rows(): array {

		$table = null;
		$blogs = $this->condition_builder->get_blogs();
		foreach ( $blogs as $blog ) {
			$this->current_blog_id = $blog;
			$this->meta_query->set_current_blog( $blog );
			$this->condition_builder->set_current_blog( $blog );
			$query = $this->build_query();
			if( $table ){
				$this->insert_temp_data( $table, $query );
			} else {
				$table = $this->create_temporary_table( $query );
			}
		}
		if( $table ) {
			if( $this->load_only_ids && $this->order_by ) {
				$query = 'SELECT ID, blog_id FROM ' . $table . ' GROUP BY ID, blog_id';
			} else {
				$query = 'SELECT * FROM ' . $table . ' GROUP BY ID, blog_id';
			}
			if ( $this->meta_query->has_order() ) {
				$query .= ' ' . $this->order_posts_by_acf();
			} else if ( $this->is_random ) {
				$query .= ' ' . $this->build_random_order();
			} elseif ( $this->order_by ) {
				$query .= ' ' . $this->build_order_by();
			}
			$query .= ' ' . $this->build_limit();

			$results = $this->db->get_results( $query, ARRAY_A );
			$this->drop_temp_table( $table );
			return $results;
		}
		return array();
	}

	public function get_ids(): array {
		$this->load_only_ids = true;
		$results             = $this->get_rows();

		if ( $results ) {
			if ( $this->posts_without_children ) {
				$blogs = $this->condition_builder->get_blogs();
				foreach ( $blogs as $blog ) {
					$this->current_blog_id = $blog;
					$parent_ids            = $this->get_parents( $results );
					$results               = array_filter( $results, function ( $id ) use ( $parent_ids ) {
						return ! array_has_value( $id, $parent_ids );
					} );
				}
			}
		}

		return $results;
	}

	protected function set_db( \WPDB $db ): void {
		$this->db = $db;
	}

	protected function build_query(): string {
		$posts_table = $this->get_posts_table();
		$blog_id     = $this->current_blog_id;
		$collate = 'utf8';
		if ( $this->load_only_ids ) {
			$columns = array( 'ID', "$blog_id as blog_id" );
			if( $this->order_by ){
				$columns[] = $this->order_by;
			}
		} else {
			$columns = array(
				'ID',
				"$blog_id as blog_id",
				'post_title',
				'post_excerpt',
				'post_content',
				'post_author',
				'post_date',
				'post_type',
			);
		}
		$join_tables = '';
		if ( $this->include_posts_table ) {
			$include_table = $this->include_posts_table;
			$join_tables   .= " INNER JOIN $include_table ON ($include_table.post_id = $posts_table.ID AND $include_table.blog_id = $blog_id)";
		}
		if ( $this->exclude_posts_table ) {
			$exclude_table = $this->exclude_posts_table;
			$join_tables   .= " OUTER JOIN $exclude_table ON ($exclude_table.post_id = $posts_table.ID AND $exclude_table.blog_id = $blog_id)";
		}
		if ( $this->meta_query->is_acf_needed() ) {

			$acf_columns = $this->meta_query->get_columns();
			foreach ( $acf_columns as $key => $value ){
				if( is_int( $key ) ) {
					$columns[] = $value;
				} else {
					$columns[] = $key . ' as `' . $value . '`';
				}
			}
			//$columns = array_merge( $columns, $acf_columns );
			$join_tables .= $this->meta_query->build_join( $posts_table, 'ID' );
		}

		$columns_string = join( ',', $columns );
		$query = "SELECT $columns_string FROM ${posts_table} ${join_tables}";
		$where = $this->build_where_condition();
		if ( $where ) {
			$query .= ' WHERE ' . $where;
		}
		return $query;
	}

	protected function get_posts_table(): string {
		return NetsPostsDbHelper::make_table_name( $this->db->base_prefix, 'posts', $this->current_blog_id );
	}

	protected function build_where_condition(): string {
		$condition = $this->condition_builder->build();
		if ( $this->meta_query->is_acf_needed() ) {
			if( $condition ){
				$condition .= ' AND ';
			}
			$condition .= $this->meta_query->build_where_condition();
		}
		return $condition;
	}

	protected function build_random_order(): string {
		return 'ORDER BY RAND()';
	}

	protected function build_order_by(): string {
		return 'ORDER BY ' . $this->order_by . ' ' . $this->sort_type;
	}

	protected function build_limit(): string {
		return 'LIMIT ' . $this->offset . ', ' . $this->limit;
	}

	protected function order_posts_by_acf(): string {
		return $this->meta_query->build_order_by();
	}

	protected function create_temporary_table( string $query ): string {
		$table_name         = $this->db->prefix . self::$TEMP_TABLE_NAME;
		$create_table_query = NetsPostsDbHelper::build_temporary_table_query( $table_name, $query );
		$this->db->query( $create_table_query );
		return $table_name;
	}

	protected function insert_temp_data( string $table, string $query ) {
		$insert_query = "INSERT INTO $table $query";
		$this->db->query( $insert_query );
	}

	protected function drop_temp_table( string $table ): void {
		$this->db->query( 'DROP TABLE IF EXISTS ' . $table );
	}

	/**
	 * @param int[] $post_ids
	 *
	 * @return int[]
	 */
	protected function get_parents( array $post_ids ): array {
		$query = "SELECT post_parent FROM " . $this->get_posts_table() . ' WHERE ';
		$query .= 'post_parent IN (' . join( ',', $post_ids ) . ')';
		$post_type = $this->condition_builder->get_post_type();
		if( ! empty( $post_type ) ) {
			$post_type = array_map( function( $type ){ return '"' . $type . '"'; }, $post_type );
			$query .= ' AND post_type IN (' . implode( ',', $post_type ) . ')';
		}
		$parents = $this->db->get_results( $query, ARRAY_A );

		$ids = array_map( function ( $post ) {
			return $post['post_parent'];
		}, $parents );
		return array_unique( $ids );
	}

	public function exclude_protected_posts() {
		$this->condition_builder->exclude_protected();
	}
}