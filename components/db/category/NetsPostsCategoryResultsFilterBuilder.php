<?php


namespace NetworkPosts\DB\Category;


use NetworkPosts\db\NetsPostsDbHelper;

class NetsPostsCategoryResultsFilterBuilder {

	private $blog_id = 1;
	private $base_prefix;

	private $taxonomy_types = array();
	private $temp_table = '';
	private $taxonomy_count = 0;

	private $taxonomy_table;
	private $term_relationships_table;

	public function __construct( string $base_prefix, int $blog_id = 1 ) {
		$this->base_prefix = $base_prefix;
		$this->set_blog_id( $blog_id );
	}

	public function set_blog_id( int $id ): void {
		$this->blog_id = $id;
		$this->taxonomy_table = NetsPostsDbHelper::make_table_name( $this->base_prefix, 'term_taxonomy', $this->blog_id );
		$this->term_relationships_table = NetsPostsDbHelper::make_table_name( $this->base_prefix, 'term_relationships', $this->blog_id );
	}

	public function set_taxonomy_count( int $total ): void {
		$this->taxonomy_count = $total;
	}

	/**
	 * @param string[] $types
	 */
	public function set_taxonomy_types( array $types ): void {
		$this->taxonomy_types = $types;
	}

	public function build( string $temp_results_table ): string {
		$this->temp_table = $temp_results_table;
		$query = $this->build_base_query();
		$query .= ' WHERE ' . $this->build_where_condition();
		$query .= ' GROUP BY ' . $this->build_group_by();
		$query .= ' HAVING ' . $this->build_having_condition();
		return $query;

	}

	protected function build_base_query(): string {
		$query = 'SELECT ' . $this->temp_table . '.* FROM ' . $this->temp_table;
		$query .= ' ' . $this->join_tables();
		return $query;
	}

	protected function join_tables(): string {
		$tables = array();
		$tables[] = $this->join_taxonomy_relationships_table();
		$tables[] = $this->join_taxonomy_table();
		$query = implode( ' ', $tables );
		return $query;
	}

	protected function join_taxonomy_relationships_table(): string {
		$query = 'INNER JOIN %1$s ON %2$s.post_id = %1$s.object_id';

		return sprintf(
			$query,
			$this->term_relationships_table,
			$this->temp_table
		);
	}

	protected function join_taxonomy_table(): string {
		$query = 'INNER JOIN %1$s ON %2$s.term_taxonomy_id = %1$s.term_taxonomy_id';

		return sprintf( $query, $this->taxonomy_table, $this->term_relationships_table );
	}

	protected function build_where_condition(): string {
		$taxonomy_list = array_map( function( $item ){ return "'$item'"; }, $this->taxonomy_types );
		return $this->taxonomy_table . '.taxonomy IN (' . implode( ',', $taxonomy_list ) . ')';
	}

	protected function build_group_by(): string {
		return $this->term_relationships_table . '.object_id, name, slug, taxonomy';
	}

	protected function build_having_condition(): string {
		return 'COUNT(' . $this->term_relationships_table . '.object_id) = ' . $this->taxonomy_count;
	}
}