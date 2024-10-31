<?php


namespace NetworkPosts\Components\Settings;


class NetsPostsNetworkSettings {

	const DENY_EXCERPT_TAGS_OPTION = 'netsposts_deny_excerpt_tags';

	public static function is_excerpt_tags_denied( int $blog_id ): bool {
		$blogs = self::get_restricted_excerpt_tag_blogs();
		return is_array( $blogs ) && in_array( $blog_id, $blogs );
	}

	public static function get_restricted_excerpt_tag_blogs(): array {
		$blogs_option = get_site_option( self::DENY_EXCERPT_TAGS_OPTION, null );
		if( !$blogs_option ){
			$blogs = get_sites( array( 'fields' => 'ids', 'public' => 1 ) );
			self::set_denied_excerpt_tag_blogs( $blogs );
			return $blogs;
		}
		if( is_serialized( $blogs_option ) ) {
			return unserialize( $blogs_option );
		}
		return [];
	}

	/**
	 * @param int[] $blogs
	 */
	public static function set_denied_excerpt_tag_blogs( array $blogs ): void {
		if( empty( $blogs ) ){
			delete_site_option( self::DENY_EXCERPT_TAGS_OPTION );
		}
		else{
			update_site_option( self::DENY_EXCERPT_TAGS_OPTION, serialize( $blogs ) );
		}
	}

	public static function allow_excerpt_tags_for_blog( int $blog_id ): void {
		$blogs = self::get_restricted_excerpt_tag_blogs();
		$index = array_search( $blog_id , $blogs);
		if($index !== false) {
			unset( $blogs[$index] );
			$blogs = array_values( $blogs );
			self::set_denied_excerpt_tag_blogs( $blogs );
		}
	}

	public static function deny_excerpt_tags_for_blog( $blog_id ): void {
		$blogs = self::get_restricted_excerpt_tag_blogs();
		if( !in_array( $blog_id, $blogs ) ){
			$blogs[] = $blog_id;
			self::set_denied_excerpt_tag_blogs( $blogs );
		}
	}

}