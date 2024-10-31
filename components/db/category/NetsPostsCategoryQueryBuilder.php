<?php


namespace NetworkPosts\DB\Category;


use NetworkPosts\db\NetsPostsDbHelper;

class NetsPostsCategoryQueryBuilder {
	private $blog_id = 1;
	private $base_prefix;
	private $terms_table;
	private $relationships_table;
	private $taxonomy_table;

	private $mode;
	private $taxonomy = array();
	private $taxonomy_types = array();

	public function __construct( string $base_prefix, int $blog_id = 1 ) {
		$this->base_prefix = $base_prefix;
		$this->set_blog_id( $blog_id );
	}

	public function set_blog_id( int $id ): void {
		$this->blog_id = $id;
		$this->terms_table = NetsPostsDbHelper::make_table_name( $this->base_prefix, 'terms', $this->blog_id );
		$this->taxonomy_table = NetsPostsDbHelper::make_table_name( $this->base_prefix, 'term_taxonomy', $this->blog_id );
		$this->relationships_table = NetsPostsDbHelper::make_table_name( $this->base_prefix, 'term_relationships', $this->blog_id );
	}

	/**
	 * @param string[] $taxonomy
	 */
	public function set_taxonomy( array $taxonomy ): void {
		$this->taxonomy = $taxonomy;
	}

	/**
	 * @param string[] $types
	 */
	public function set_taxonomy_types( array $types ): void {
		$this->taxonomy_types = $types;
	}

	public function build( int $mode = CategoryInclusionMode::INCLUDE_ANY ): string {
		$this->mode = $mode;
		$columns = array(
			'object_id as post_id',
			'name',
			'slug',
			'taxonomy',
		);
		$base_query = $this->build_base_query( $columns );
		$where      = $this->build_where_condition();
		return      $base_query . ' ' . $where;
	}

	protected function build_base_query( array $columns ): string {
		$tables = $this->join_tables();

		$base_select_query = 'SELECT %1$s, %2$d as blog_id FROM %3$s %4$s';

		$column_str = join( ',', $columns );
		$select = sprintf(
			$base_select_query,
			$column_str,
			$this->blog_id,
			$this->terms_table,
			$tables
		);
		return $select;
	}

	protected function join_tables(): string {
		$query = 'INNER JOIN %2$s ON %1$s.term_id = %2$s.term_id
    INNER JOIN %3$s ON %2$s.term_taxonomy_id = %3$s.term_taxonomy_id';


		return sprintf(
			$query,
			$this->terms_table,
			$this->taxonomy_table,
			$this->relationships_table
		);
	}

	protected function build_where_condition(): string {
		$conditions = array();
		if( $this->taxonomy_types ){
			$conditions[] = $this->build_taxonomy_types_filter();
		}
		if( $this->taxonomy ){
			$condition = $this->build_inclusion();

			if( $this->mode !== CategoryInclusionMode::INCLUDE_ANY ) {
				$condition .= ' ';
				$condition .= $this->build_strict_inclusion();
			}

			$conditions[] = $condition;
		}
		if( $conditions ){
			return 'WHERE ' . implode( ' AND ', $conditions );
		}
		return '';
	}

	protected function build_taxonomy_types_filter() {
		$taxonomy_types = array_map( function( $type ){ return "'$type'"; }, $this->taxonomy_types );
		$types = join( ',', $taxonomy_types );
		return '(taxonomy IN (' . $types . '))';
	}

	protected function build_inclusion(): string {
		$query = '(';
		$parts = array();
		foreach ( $this->taxonomy as $taxonomy ){
			$parts[] = "(LOWER(slug) LIKE '%$taxonomy%')";
		}
		$query .= join( ' OR ', $parts ) . ')';
		return $query;
	}

	protected function build_strict_inclusion(): string {
		$category_count = count( $this->taxonomy );
		$relationship_table = $this->relationships_table;
		return "GROUP BY $relationship_table.object_id HAVING COUNT(*) = $category_count;";
	}

}

