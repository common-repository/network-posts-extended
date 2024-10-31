<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28.03.2019
 * Time: 10:55
 */

namespace NetworkPosts\DB\Category;

use NetworkPosts\Components\DB\NetsPostsTemporaryTableManager;
use NetworkPosts\db\NetsPostsDbHelper;
use WPDB;

class NetsPostsCategoryQuery {

	const COLUMNS = array(
		'post_id' => 'BIGINT UNSIGNED',
		'name'    => 'VARCHAR(200)',
		'slug'    => 'VARCHAR(200)',
		'taxonomy'=> 'VARCHAR(32)',
		'blog_id' => 'SMALLINT',
	);

	/**
	 * @var WPDB
	 */
	private $db;
	private $blogs = array();

	private $taxonomy_offsets = array();
	private $inclusion_mode;

	private $tmp_tables_mgr;
	private $category_query_builder;
	private $category_filter_builder;

	public function __construct( WPDB $db ) {
		$this->db = $db;
		$this->tmp_tables_mgr = new NetsPostsTemporaryTableManager( $db );
		$this->category_query_builder = new NetsPostsCategoryQueryBuilder( $db->base_prefix );
		$this->category_filter_builder = new NetsPostsCategoryResultsFilterBuilder( $db->base_prefix );
	}

	public function include_blogs( array $blogs ): void {
		$this->blogs = $blogs;
	}

	protected function set_taxonomy( array $taxonomy ): void {
		$taxonomy = array_unique( $taxonomy );
		$this->category_query_builder->set_taxonomy( $taxonomy );
		$this->category_filter_builder->set_taxonomy_count( count( $taxonomy ) );
	}

	public function set_taxonomy_type( array $taxonomy ) {
		$types = array();
		foreach( $taxonomy as $type ) {
			$type = strtolower( $type );
			switch ( $type ) {
				case 'tag':
					$taxonomy_type_value = 'post_tag';
					break;
				case 'category':
					$taxonomy_type_value = 'category';
					break;
				case 'product_tag':
					$taxonomy_type_value = 'product_tag';
					break;
				case 'product_category':
					$taxonomy_type_value = 'product_cat';
					break;
				default:
					$taxonomy_type_value = $type;
			}
			$types[] = $taxonomy_type_value;
		}
		$this->category_query_builder->set_taxonomy_types( $types );
		$this->category_filter_builder->set_taxonomy_types( $types );
	}

	public function clear_taxonomy_types(): void {
		$this->category_query_builder->set_taxonomy_types( array() );
		$this->category_filter_builder->set_taxonomy_types( array() );
	}
	public function offset_taxonomy( $taxonomy, int $offset ) {
		$this->taxonomy_offsets[$taxonomy] = $offset;
	}


	/**
	 * @param $taxonomy
	 * @param int $mode
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function get_query_table( $taxonomy, $mode = CategoryInclusionMode::INCLUDE_ANY ): string {
		$this->set_taxonomy( $taxonomy );
		$this->inclusion_mode = $mode;

		$columns = self::COLUMNS;
		$column_names = array_keys( $columns );
		$table = $this->tmp_tables_mgr->create_empty( $columns );
		foreach ( $this->blogs as $blog ) {
			$this->category_query_builder->set_blog_id( $blog );
			$query = $this->category_query_builder->build( $this->inclusion_mode );
			if( $mode === CategoryInclusionMode::INCLUDE_ONLY ){
				$temp_table = $this->tmp_tables_mgr->create_new( $query );
				$this->category_filter_builder->set_blog_id( $blog );
				$filtered_query = $this->category_filter_builder->build( $temp_table );
				$filtered_temp_table = $this->tmp_tables_mgr->create_new( $filtered_query );
				$this->tmp_tables_mgr->copy_from_table( $filtered_temp_table, $table, $column_names );
			} else {
				$this->tmp_tables_mgr->insert_data( $table, $column_names, $query );
			}
		}
		if( $this->taxonomy_offsets ){
			$table = $this->offset_taxonomy_rows( $table );
		}
		return $table;
	}

	public function drop_temp_tables(): void {
		$this->tmp_tables_mgr->destroy();
	}

	private function offset_taxonomy_rows( string $table ): string {
		$taxonomies = array_keys( $this->taxonomy_offsets );
		$taxonomies = array_map( function( $name ){
			return "'" . $name . "'";
		}, $taxonomies );
		$taxonomy_list_string = '(' . join( ',', $taxonomies ) . ')';
		$other_posts_query = "SELECT * FROM $table WHERE slug NOT IN $taxonomy_list_string LIMIT 0, 10000";
		$this->db->query( $other_posts_query );

		$result_table = $this->tmp_tables_mgr->create_empty( self::COLUMNS );
		$query_template = "INSERT INTO $result_table %s";
		foreach ( $this->taxonomy_offsets as $taxonomy => $offset ){
			$part_query = "SELECT * FROM $table WHERE slug = '$taxonomy' LIMIT $offset, 10000" ;
			$query  = sprintf( $query_template, $part_query );
			$this->db->query( $query );
		}
		return $result_table;
	}
}
