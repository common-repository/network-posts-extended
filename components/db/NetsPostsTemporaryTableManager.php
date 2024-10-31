<?php


namespace NetworkPosts\Components\DB;


class NetsPostsTemporaryTableManager {

	private const TEMP_RESULTS_TABLE = "network_posts_results";

	/** @var \WPDB */
	private $db;
	private $temp_tables = array();

	public function __construct( \WPDB $db ) {
		$this->db = $db;
	}

	/**
	 * @param string $query
	 *
	 * @return string Returns temporary table name
	 * @throws \Exception
	 */
	public function create_new( string $query ): string {
		$table = $this->generate_table_name();
		$result = $this->db->query( "CREATE TEMPORARY TABLE $table $query" );
		if( $result ) {
			$this->temp_tables[] = $table;
			return $table;
		}
		throw new \Exception( $this->db->last_error );
	}

	protected function generate_table_name(): string {
		$table = $this->db->prefix . self::TEMP_RESULTS_TABLE;
		$table .= random_int(1, 1000);
		return $table;
	}

	public function create_empty( array $schema ): string {
		$columns = array();
		foreach( $schema as $column => $props ){
			$columns[] = $column . ' ' . $props;
		}
		$table_schema_query = '(' . implode( ',', $columns ) . ')';
		return $this->create_new( $table_schema_query );
	}

	/**
	 * @param string $table
	 * @param array $column_names
	 * @param string $query
	 *
	 * @throws \Exception Throws exception in case of bad query or incomparable data schema
	 */
	public function insert_data( string $table, array $column_names, string $query ): void {
		$columns = '(' . implode( ',', $column_names ) . ')';
		$result = $this->db->query( 'INSERT INTO ' . $table . ' ' . $columns . ' ' . $query );
		if( ! $result && $this->db->last_error ) throw new \Exception( $this->db->last_error );
	}

	/**
	 * @param string $source_table
	 * @param string $target_table
	 * @param array $column_names
	 *
	 * @throws \Exception
	 */
	public function copy_from_table( string $source_table, string $target_table,
		array $column_names = array() ): void {
		$query = 'INSERT INTO ' . $target_table;
		if( $column_names ){
			$columns = implode( ',', $column_names );
			$query .= '(' . $columns . ')';
			$query .= ' SELECT ' . $columns;
		} else {
			$query .= ' SELECT *';
		}
		$query .= ' FROM ' . $source_table;
		$result = $this->db->query( $query );
		if( ! $result && $this->db->last_error ) throw new \Exception( $this->db->last_error );
	}

	/**
	 * We need to destroy all temporary tables to prevent collisions
	 * because there can be multiple shortcodes during 1 session
	 */
	public function destroy() {
		foreach ( $this->temp_tables as $table ){
			$this->drop_table( $table );
		}
	}

	protected function drop_table( string $table ): void {
		$this->db->query( 'DROP TEMPORARY TABLE IF EXISTS ' . $table );
	}
}