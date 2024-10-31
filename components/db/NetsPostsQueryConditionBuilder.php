<?php


namespace NetworkPosts\Components\db;


use NetworkPosts\db\NetsPostsDbHelper;

class NetsPostsQueryConditionBuilder {

	protected $blogs = array();
	protected $prefix = '';
	protected $current_blog = 1;

	protected $included_posts = array();
	protected $excluded_posts = array();
	protected $offset = 0;
	protected $post_type = array( 'post', 'product' );
	protected $days = false;
	protected $after_days = false;
	protected $author = false;
	protected $excluded_author = false;
	protected $title_keywords = array();
	protected $hide_protected = false;

	public function __construct( string $db_prefix ) {
		$this->prefix = $db_prefix;
	}

	public function set_current_blog( int $blog ): void {
		$this->current_blog = $blog;
	}

	public function include_blogs( array $blogs ): void {
		$this->blogs = $blogs;
	}

	public function get_blogs(): array {
		return $this->blogs;
	}

	public function exclude_blog( $blog_id ): void {
		if ( ! empty( $this->blogs ) ) {
			$index = array_search( $blog_id, $this->blogs );
			if ( $index !== false ) {
				unset( $this->blogs[ $index ] );
				$this->blogs = array_values( $this->blogs );
			}
		}
	}

	public function include_blog_posts( int $blog_id, array $included_posts ): void {
		if ( isset( $this->included_posts[ $blog_id ] ) ) {
			$this->included_posts[ $blog_id ] = array_merge( $this->included_posts[ $blog_id ], $included_posts );
		} else {
			$this->included_posts[ $blog_id ] = $included_posts;
		}
	}

	public function get_included_posts( int $blog_id ): array {
		return $this->included_posts[ $blog_id ];
	}

	public function exclude_posts( int $blog_id, array $excluded_posts ): void {
		if ( isset( $this->excluded_posts[ $blog_id ] ) ) {
			$this->excluded_posts[ $blog_id ] = array_merge( $this->excluded_posts[ $blog_id ], $excluded_posts );
		} else {
			$this->excluded_posts[ $blog_id ] = $excluded_posts;
		}
	}

	public function get_excluded_posts(): array {
		return $this->excluded_posts;
	}

	/**
	 * @param int $offset
	 */
	public function set_offset( int $offset ): void {
		$this->offset = $offset;
	}

	public function get_post_type(): array {
		if( $this->post_type ) {
			return $this->post_type;
		}
		return false;
	}

	/**
	 * @param mixed $post_type
	 */
	public function set_post_type( $post_type ): void {
		if ( $post_type == 'any' ) {
			$this->post_type = false;
		} else {
			$this->post_type = $post_type;
		}
	}

	public function set_author_id( int $author ): void {
		$this->author = $author;
	}

	public function exclude_author_id( int $author ): void {
		$this->excluded_author = $author;
	}

	/**
	 * @param string $days
	 */
	public function set_days( string $days ): void {
		$this->days = $days;
	}

	public function filter_after_days( int $after_days ) {
		$this->after_days = $after_days;
	}

	/**
	 * @param array $title_keywords
	 */
	public function set_title_keywords( array $title_keywords ): void {
		$this->title_keywords = $title_keywords;
	}

	public function exclude_protected(): void {
		$this->hide_protected = true;
	}

	public function build(): string {
		$condition = array();
		if ( $this->post_type && empty( $this->included_posts ) ) {
			$condition[] = $this->build_post_type();
		}
		if ( $this->days ) {
			$condition[] = $this->build_days_filter_query();
		}
		if ( $this->after_days ) {
			$condition[] = $this->build_after_days_filter_query();
		}
		if( $this->author ) {
			$condition[] = $this->build_author_inclusion();
		}
		if( $this->excluded_author ){
			$condition[] = $this->build_author_exclusion();
		}
		if ( ! empty( $this->included_posts ) ) {
			$condition[] = $this->build_posts_inclusion();
		}
		if ( ! empty( $this->excluded_posts ) ) {
			$condition[] = $this->build_posts_exclusion();
		}
		if ( $this->title_keywords ) {
			$condition[] = $this->build_title_filter();
		}
		if( $this->hide_protected ){
			$condition[] = $this->filter_protected_posts();
		}
		$condition[] = $this->build_post_status();

		return join( ' AND ', $condition );
	}

	protected function get_posts_table(): string {
		return NetsPostsDbHelper::make_table_name( $this->prefix, 'posts', $this->current_blog );
	}

	protected function build_post_type(): string {
		$post_types = array_map( function ( $type ) {
			return "post_type='" . esc_sql( $type ) . "'";
		}, $this->post_type );

		return '(' . join( ' OR ', $post_types ) . ')';

	}

	protected function build_days_filter_query(): string {
		$days = intval( $this->days );
		return "(post_date >= DATE_SUB(CURRENT_DATE(), INTERVAL $days DAY))";
	}

	protected function build_after_days_filter_query(): string {
		$days = $this->after_days;
		return "(post_date < DATE_SUB(CURRENT_DATE(), INTERVAL $days DAY))";
	}

	protected function build_author_inclusion(): string {
		return "(post_author = {$this->author})";
	}

	protected function build_author_exclusion(): string {
		return "(post_author != {$this->excluded_author})";
	}

	protected function build_posts_inclusion(): string {
		$conditions = array();
		$posts_view = $this->get_posts_table();
		foreach ( $this->included_posts as $blog_id => $posts ) {
			$conditions[] = $posts_view . '.ID IN (' . join( ',', $posts ) . ')';
		}

		return '(' . join( ' OR ', $conditions ) . ')';
	}


	protected function build_posts_exclusion(): string {
		$conditions = array();
		$posts_view = $this->get_posts_table();
		foreach ( $this->excluded_posts as $blog_id => $posts ) {
			$conditions[] = $posts_view . '.ID NOT IN (' . join( ',', $posts ) . ')';
		}

		return '(' . join( ' OR ', $conditions ) . ')';
	}

	protected function build_title_filter(): string {
		$query_parts = array_map( function ( $keyword ) {
			$keyword = esc_sql( $keyword );

			return "LOWER(post_title) LIKE '%$keyword%'";
		}, $this->title_keywords );

		return '(' . join( ' OR ', $query_parts ) . ')';
	}

	protected function build_post_status(): string {
		return '(post_status="publish")';
	}

	protected function filter_protected_posts(): string {
		$posts_table = $this->get_posts_table();
		return "($posts_table.post_password='')";
	}
}