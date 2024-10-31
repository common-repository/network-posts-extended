<?php


namespace NetworkPosts\Components\db;


use NetworkPosts\db\NetsPostsDbHelper;

class NetsPostsMetaQueryBuilder {

	private $blogs;
	private $prefix;
	private $current_blog_id = 1;

	protected $primary_acf_field = '';

	protected $acf_order = '';

	protected $acf_date = '';
	protected $before_acf_date = '';
	protected $after_acf_date = '';

	public function __construct( string $db_prefix ) {
		$this->prefix = $db_prefix;
	}

	public function include_blogs( array $blogs ): void {
		$this->blogs = $blogs;
	}

	/**
	 * @param string $primary_acf_field
	 */
	public function set_primary_acf_field( string $primary_acf_field ): void {
		$this->primary_acf_field = $primary_acf_field;
	}

	/**
	 * @param string $acf_date
	 */
	public function filter_acf_date( string $acf_date ): void {
		$this->acf_date = $this->format_date( $acf_date );
	}

	private function format_date( string $date ): string {
		$date = str_replace( '-', '', $date );
		$date = str_replace( '/', '', $date );

		return $date;
	}

	/**
	 * @param string $before_acf_date
	 */
	public function filter_before_acf_date( string $before_acf_date ): void {
		$this->before_acf_date = $this->format_date( $before_acf_date );
	}

	/**
	 * @param string $after_acf_date
	 */
	public function filter_after_acf_date( string $after_acf_date ): void {
		$this->after_acf_date = $this->format_date( $after_acf_date );
	}

	public function order_by_acf( string $order ): void {
		$this->acf_order = $order;
	}

	public function get_primary_acf__field(): string {
		return $this->primary_acf_field;
	}

	public function is_acf_needed(): bool {
		return ! empty( $this->primary_acf_field );
	}

	public function has_filters(): bool {
		return $this->acf_date || $this->before_acf_date || $this->after_acf_date;
	}

	public function has_order(): bool {
		return ! empty( $this->acf_order );
	}

	public function set_current_blog( int $blog_id ): void {
		$this->current_blog_id = $blog_id;
	}


	public function get_columns(): array {
		return array(
			'meta_key',
			'meta_value' => $this->primary_acf_field
		);
	}

	public function build_join( string $posts_table, string $post_id_column ): string {
		$meta_table = $this->get_post_meta_table();
		return " INNER JOIN $meta_table ON ($meta_table.post_id = $posts_table.$post_id_column)";
	}

	protected function get_post_meta_table(): string {
		return NetsPostsDbHelper::make_table_name( $this->prefix, 'postmeta', $this->current_blog_id );
	}

	public function build_where_condition(): string {
		$condition = array();

		$condition[] = $this->build_acf_date_meta_query();
		if ( $this->acf_date ) {
			$condition[] = $this->build_filter_acf_date();
		}
		if ( $this->before_acf_date ) {
			$condition[] = $this->build_filter_before_acf_date();
		} elseif ( $this->after_acf_date ) {
			$condition[] = $this->build_filter_after_acf_date();
		}

		if ( $condition ) {
			return join( ' AND ', $condition );
		}

		return '';
	}

	protected function build_acf_date_meta_query(): string {
		$meta_table = $this->get_post_meta_table();
		return '(' . $meta_table . '.meta_key="' . $this->primary_acf_field . '" AND ' . $meta_table . '.meta_value <> "")';
	}

	protected function build_filter_acf_date(): string {
		$meta_table = $this->get_post_meta_table();
		$date       = esc_sql( $this->acf_date );

		return "($meta_table.meta_value = $date)";
	}

	protected function build_filter_before_acf_date(): string {
		$meta_table = $this->get_post_meta_table();
		$date       = esc_sql( $this->before_acf_date );

		return "($meta_table.meta_value < $date)";
	}

	protected function build_filter_after_acf_date(): string {
		$meta_table = $this->get_post_meta_table();
		$date       = esc_sql( $this->after_acf_date );

		return "($meta_table.meta_value >= $date)";
	}

	public function build_order_by(): string {
		$order = $this->acf_order;
		$field_name = $this->primary_acf_field;
		return "ORDER BY $field_name $order";
	}
}