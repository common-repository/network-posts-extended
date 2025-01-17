<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 20.11.2018
 * Time: 12:52
 */
namespace NetworkPosts\Components;

use NetworkPosts\Components\Resizer\NetsPostsThumbnailBlogSettings;
use NetworkPosts\Components\resizer\NetsPostsThumbnailSizeSettings;

class NetsPostsThumbnailManager
{
    private static $instance;
    static $sizes;

    private function __construct(){}

    private static function init_thumbnail_sizes()
    {
        $allowed_blogs = NetsPostsThumbnailBlogSettings::get_allowed_blogs();
        $global_blogs = NetsPostsThumbnailBlogSettings::get_globals();
        $blogs = array_intersect($allowed_blogs, $global_blogs);

        $blog_id = get_current_blog_id();

        // append current blog id if it isn't global and has been removed
        if (!in_array($blog_id, $blogs) &&
            NetsPostsThumbnailBlogSettings::is_allowed_for_blog($blog_id)) {
            $blogs[] = $blog_id;
        }
        self::$sizes = self::get_thumbnail_sizes($blogs);
        self::register_sizes();
    }

    private static function get_thumbnail_sizes($blog_ids)
    {
        global $_wp_additional_image_sizes;
        $sizes = array();
        foreach ($blog_ids as $id) {
            switch_to_blog($id);

             foreach (get_intermediate_image_sizes() as $_size) {
                if (in_array($_size, array('thumbnail', 'medium', 'medium_large', 'large'))) {
                    $sizes[$_size]['width'] = get_option("{$_size}_size_w");

                    $sizes[$_size]['height'] = get_option("{$_size}_size_h");

                    $sizes[$_size]['crop'] = (bool)get_option("{$_size}_crop");
                } elseif (isset($_wp_additional_image_sizes[$_size])) {
                    $sizes[$_size] = array(
                        'width' => $_wp_additional_image_sizes[$_size]['width'],
                        'height' => $_wp_additional_image_sizes[$_size]['height'],
                        'crop' => $_wp_additional_image_sizes[$_size]['crop'],
                    );
                }
            }
            $blog_custom_sizes = NetsPostsThumbnailSizeSettings::get_blog_image_sizes();
            foreach ($blog_custom_sizes as $size_id => $db_size){
                $size = $db_size['data'];
                $size['crop'] = $db_size['crop'];
                if( isset( $db_size['crop_x'] ) ) {
	                $size['crop_x'] = $db_size['crop_x'];
                } else {
                	$size['crop_x'] = 'center';
                }
                if( isset( $db_size['crop_y'] ) ) {
	                $size['crop_y'] = $db_size['crop_y'];
                } else {
                	$size['crop_y'] = 'center';
                }
                $sizes[$size['alias']] = $size;
            }
            restore_current_blog();
        }

        return $sizes;
    }

    /**
     * @param $args array
     * @required $post_id int
     * @required $size_name string - size alias name
     * $args[image_class] string
     * $args[column] int - decide whether posts are divided to columns or not
     * are in one place
     * $args[compress_images] boolean
     * @return string - returns img html string
     */

    public static function get_thumbnail( $post_id, $size_name, array $args = [] ) {
        if(self::is_initialized()) {
            $thumb_id = has_post_thumbnail($post_id);
            if (!$thumb_id) {
	            return false;
            }

            if (isset($args['image_class'])) {
                $image_class = esc_html($args['image_class']);
            } else {
                $image_class = '';
            }
            $attrs = array('class' => $image_class, 'srcset' => '');

            $use_compressed_images = isset($args['compress_images']) && $args['compress_images'];

            if ($use_compressed_images) {
                $img = get_the_post_thumbnail($post_id, $size_name, $attrs);
            } else {
                $img = get_the_post_thumbnail($post_id, $size_name, $attrs);
                $img = preg_replace('/(\bsizes\=.*?\")[\s\/]/', "", $img);
                $img = preg_replace('/(\bsrcset\=.*?\\")[\s\/]/', "", $img);
            }
            return $img;
        }
        return false;
    }

    private static function register_sizes(){
        foreach (self::$sizes as $name => $size){
            if($size['crop']) {
                $crop = array();
                if(isset($size['crop_x'])){
                    $crop[] = $size['crop_x'];
                }
                if(isset($size['crop_y'])){
                    $crop[] = $size['crop_y'];
                }
                if(empty($crop)){
                    add_image_size($name, $size['width'], $size['height'], true);
                }
                else{
                    add_image_size($name, $size['width'], $size['height'], $crop);
                }
            }
            else{
                add_image_size($name, $size['width'], $size['height']);
            }
        }
    }

    static function get_estore_product_thumbnail( $image_url, $alt, $size = 'thumbnail', $image_class = '' ) {
        if(self::is_initialized()) {

            if (!empty($image_url)) {
                $img = '<img src="' . $image_url . '" alt="' . $alt . '" ';

                if ($image_class) {
                    $img .= 'class="' . $image_class;
                }

                $img .= '"';

                if (array_key_exists($size, self::$sizes)) {
                    $img .= 'width="' . self::$sizes[$size]['width'] . 'px" ';

                    $img .= 'height="' . self::$sizes[$size]['height'] . 'px"';
                }

                $img .= '/>';

                return $img;
            }
        }
        return false;
    }

    static function initialize(){
        if (!self::$instance) {
            self::$instance = new NetsPostsThumbnailManager();
            self::init_thumbnail_sizes();
        }
    }

    private static function is_initialized(){
        return isset(self::$sizes);
    }
}