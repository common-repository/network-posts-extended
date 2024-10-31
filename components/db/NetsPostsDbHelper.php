<?php


namespace NetworkPosts\db;


class NetsPostsDbHelper {
	public static function make_table_name( string $prefix, string $table, int $blog_id ): string {
		if( $blog_id === 1 ){
			$full_table_name = $prefix . $table;
		} else {
			$full_table_name = $prefix . $blog_id . '_' . $table;
		}
		return $full_table_name;
	}

	public static function build_select_query( string $table_name, string $columns, int $blog_id ): string {
		return "SELECT $columns, '$blog_id' as blog_id FROM $table_name";
	}

	public static function union_queries( array $queries ): string {
		return join( ' UNION ', $queries );
	}

	public static function build_temporary_table_query( string $table_name, string $query ): string {
		return "CREATE TEMPORARY TABLE $table_name AS $query";
	}
}