<?php


namespace NetworkPosts\Components\Settings;


use NetworkPosts\Components\Resizer\NetsPostsResizerSettingsPage;

class NetsPostsNetworkSettingsPage {
	public function save_options( array $request ) {
		if ( isset( $request['submit'] ) ) {
			if ( ! isset( $request['_wpnonce'] ) ) {
				return;
			}
			if ( ! wp_verify_nonce( $request['_wpnonce'] ) ) {
				return;
			}
			if ( isset( $request['denied_tags'] ) ) {
				NetsPostsNetworkSettings::set_denied_excerpt_tag_blogs( $request['denied_tags'] );
			} else {
				NetsPostsNetworkSettings::set_denied_excerpt_tag_blogs( [] );
			}
		}
	}



	public static function get_blog_list( $denied ): array {
		$blogs = get_sites();
		$rows  = array_map( function ( $item ) use ( $denied ) {
			$site = get_blog_details( [ 'blog_id' => $item->blog_id ] );

			return [
				'id'         => $item->blog_id,
				'blogname'   => $site->blogname,
				'denied_tags' => in_array( $item->blog_id, $denied ) ? 'checked' : '',
			];
		}, $blogs );

		return $rows;
	}

	public static function add_settings_page(): void {
		add_submenu_page( 'settings.php', 'Network Posts Global Settings', 'Network Posts Global Settings', '', 'netsposts_multisite', array(
			NetsPostsNetworkSettingsPage::class,
			'load'
		) );
	}

	public static function load(): void {
		$settings = new NetsPostsNetworkSettingsPage();
		$settings->save_options( $_POST );
		$settings->print_content();
	}

	public function print_content(): void {
		$denied = NetsPostsNetworkSettings::get_restricted_excerpt_tag_blogs();
		self::build_page( $denied );
	}

	public static function build_page( $denied ): void {
		$rows          = self::get_blog_list( $denied );
		$data['rows']  = $rows;
		$data['nonce'] = wp_create_nonce();
		include_once NETSPOSTS_VIEW_PATH . '/network_settings.php';
	}
}