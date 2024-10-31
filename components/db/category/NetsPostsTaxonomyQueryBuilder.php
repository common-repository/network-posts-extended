<?php


namespace NetworkPosts\DB\Category;


abstract class NetsPostsTaxonomyQueryBuilder {
	private $blog_id = 1;
	private $base_prefix;
	private $taxonomy_types = array();

	public function __construct( string $base_prefix, int $blog_id = 1 ) {
		$this->base_prefix = $base_prefix;
		$this->blog_id     = $blog_id;
	}

	public function set_blog_id( int $id ): void {
		$this->blog_id = $id;
	}

	/**
	 * @param string[] $types
	 */
	public function set_taxonomy_types( array $types ): void {
		$this->taxonomy_types = $types;
	}
}