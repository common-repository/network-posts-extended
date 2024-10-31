=== Network Posts Extended ===
Contributors: johnzenausa,superfrontender
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VBR3DEUQ5XVMU
Tags: network global posts, network posts, global posts, multisite posts, shared posts, display multisite posts
Requires at least: 5.7.1
Tested up to: 6.6.1
Stable tag: 7.7.1
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The plugin is designed to share posts, pages, and custom post types from across entire WordPress multisite network on any given page for any subdomain and the main blog.
== Description ==

<p>The plugin is designed to list posts, pages, and custom post types from across entire WordPress multisite network on any given page for any subdomain and the main blog.</p><p>If you would like to list all posts in a WordPress multisite installation on any other blog in the network this plugin will do that for you. Let's say you want to list the posts from all blogs or selected blogs on the main site you may do so with pagination or limit the amount of posts. You may also list the main blog in a multisite installation on any sub blog. You may list posts from blog1 and blog2 on blog3 or the main blog. Any combination is possible. This makes a perfect WordPress Multisite Posts listing plugin. You may also filter the listing anyway you desire by title keyword or category. Even custom categories.</p>

== Frequently Asked Questions ==

= Why is the plugin is only pulling in posts from main blog only? =

There are two answers to this question. 1) You have include_blog=&#39;1&#39; inside the shortcode. Simply remove this. 2) The other blogs are not setup as public. When you go in to network admin area and visite sites > (any subsite) > edit you will see a list of four checkboxes. Make sure the one marked public is checked. If a site is marked as private, spam, etc... anything other than public this plugin will not show it (as it shouldn't for security reasons).

= Should I network activate the plugin? =

You may network activate the plugin so it is available on all sites or activate individually. When network activated there will be a new menu for the plugin under settings &#62; Network Posts Thumbnails which will allow you to give certain permissions to blog owners when it comes to the thumbnail sizes. You may allow to create new sizes just on their blog or across the entire network which will affect everybody. I recommend only to allow it for their blog only. This allows you to also include it as a custom feature if you want to charge for this capability.

= May I only include an x amount of posts that I choose?  =

Yes, use include_post= and put in your posts in comma separated format surrounded by double quotes. Example include_post=&#39;5,78,896&#39;.

= My title is too long and looks ugly, anyway I can shorten it?  =

You may shorten it using the argument title_length=&#39;10&#39; will rounded it off to the last complete word before it reaches 10 characters.

= I would like to just show an X amount of random posts on the home page. Is it possible?  =

Use the following arguments: random=&#39;true&#39; and list=&#39;10&#39; will show ten different posts randomly whenever the page is loaded. If you add list=&#34;15&#34; it will show fifteen different posts randomly.

= May I order my posts in specific order by date or title?  =
Yes you may give specific ordering of your posts or pages via alphabetical order (by title), by date or page or post ID specific order.

= Does this plugin list pages from woocommerce?  =

Yes it now does as of version 0.1.4. You may list via page/post id or via taxonomy=&#39;custom woocommerce category&#39;. Woocommerce default directory/taxonomy is product show you would just use the argument taxonomy=&#39;Product&#39; which is the title of the directory. (Not case sensitive)
<strong>Note:</strong> Also works with Tips and Tricks eStore plugin.

= Will this plugin also include the prices from the products I create with the Woocommerce and eStore plugins?  =

Yes it will including the following argument: include_price=&#39;woocommerce&#39; or include_price=&#39;estore&#39;. If for some reason you have both plugins installed you would use include_price=&#39;estore|woocommerce&#39; if you want to list them both.

= Why when I use the following argument wrap_start=&#34;&lt;div style=&#34;color:blue;&#34;&gt;&#34; and wrap_end=&#34;&lt;/div&gt;&#34; the text does not change color? =

That is because since double quotes are used after the = sign they must be changed to single quotes and use double quotes in the html. For example you would have to have wrap_start=&#39;&lt;div style=&#34;color:blue;&#34;&gt;&#39;. <strong>Notice the double quotes in the html</strong> Do not forget to change the closing argument to wrap_end=&#39;&lt;/div&gt;&#39;

= Does this plugin work with custom post types. That is post_type=&#39;custom-post-type&#39;? =

Yes it now works with custom post types.

= Can I show full post from any blog on any site?  =

Yes you can by using the following argument full_text=&#39;true&#39;

= I have custom image sizes I have already created and uploaded. How can I use them with your plugin without having to go through the process of re-creating image sizes with your plugin?  =

You can use them directly as a featured image or you can install the plugin https://wordpress.org/plugins/featured-image-from-url/ and put in the link directly to the images. This plugin will automatically switch to the one listed here. Don&#39;t forget to change size=&#39;H,W&#39; to the dimensions of the featured image.

= The default layout is quite ugly. How do I improve it?  =

Using css I have made this plugin very flexible. It now contains two default layouts. Their names are &#34;default&#34; and &#34;inline&#34;. You may choose either one by using use_layout=&#39;default&#39; or use_layout=&#39;inline&#39;.

= Can I use shortcode attributes in dynamically created url?  =

Yes you may now use the shortcode attributes in a url. Example: http://localhost/wordpress/home-page/?column=infinite&include_blog=1,2,3&taxonomy=wordpress-develop,second-posts

= Where do I put the shortcodes?  =

Paste the shortcode on any page, post or custom post type using the <strong><em>Text</em></strong> not the <strong><em>Visual</em></strong> area of the posts editor field.

= How do I use this plugin in widgets?  =

Use the default WordPress text widget and post the code in there under the <strong><em>Text</em></strong> not <strong><em>Visual</em></strong> code area so it will not mess with the shortcode. This widget is now automatically shortcode ready. No need for a special widget or plugin to activate shortcodes in widgets.

= Is it possible to include custom post type meta information?  =

As long as you use the <a href="https://wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields Plugin</a> it will be possible to do so. Read the readme.txt file for arguments to add to the shortcode.

= Can I offset posts by skipping the three most recent posts and choose which category I'd like to offset?  =

Yes you now can with the following two arguments. 1) taxonomy_offset_names='' and 2) taxonomy_offsets=''. So for example if you wanted to offset three different categories it would look like so: [netsposts taxonomy_offset_names='books,flowers,sports' taxonomy_offsets='5,4,10'] then books would be offset by 5, flowers 4, and sports 10. To offset by tags include the following argument: taxonomy_offset_type='' so it will now work with tags. As of now the only argument it accepts are category, or tag, or any. So if you want to offset by certain tag you would add taxonomy_offset_type='tag'. Default it offsets by post type when argument is not used.

= I have my my multisite installed in a subfolder and the permalinks sometimes adds a /blog/ to the permalink. How can I remove this?  =

Add the following argument: remove_blog_prefix=&#39;true&#39;.

= Will this plugin work on a non-multisite/single site installation?  =

No it will not but you may get the single site version here: [Single Site Posts Extended](https://agaveplugins.com/plugins/)

= May I use an ACF custom field to order my posts? I want to order by peoples last name =

You may do so using the following argument: order_by_acf=&#39;field_name asc/desc&#39; but only use asc or desc depending on the direction you would like. It works both numerically and alphabetically.

= How do I create a title for the particular list and make it link to any page I like? =

You do so with the following parameters. 1) main_title='List of Blogs About Bikes' main_title_link='https://mysite.com/my-bike-page/'.

== Screenshots ==

1. Using argument use_layout='inline'.
2. Showing custom thumbnail size by using size='custom-thumbnail-size'
3. Can be displayed in multiple columns with some css editing.
4. Image resizing. You may set up custom image sizes and regenerate thumbnails.

== Changelog ==

== 7.7.1 ==
Added two parameters. hide_post_date_meta_info and (these both must be used together) wrap_post_date_start & wrap_post_date_end so you may customize the post date. For example wrap_post_date_start='<div class="post-date-wrapper">' wrap_post_date_end='</div>'. If you want to hide the post date information altogether you can set hide_post_date_meta_info='false'. Default is 'true'.

== 7.7.0 ==
Just confirmed compatibility with the latest version of WordPress.

== 7.6.9 ==
Add three new attributes wrap_source='true/false'. Default is true but when set to false all items will be at same level for better arranging of items via css. show_author_avatar='true/false'. Default is false. Shows the members default avatar. author_avatar_size='x' where x is the size in pixels. So if you enter 16 the avatar will be sixteen pixels wide and sixteen pixels high.

== 7.6.6 ==
Added new attributes main_title= which acts like the older title= but includes wrap_custom_title_start/end so you may wrap the title with additional HTML. Plus the ability to give the title a custom link using main_title_link='custom-url'.

== 7.6.4 ==
Now works with plugin Pages with category and tag. Used to throw a for each error when listing by taxonomy from pages.

== 7.6.3 ==
Fixed PHP8 error and also added attribute where you may now open links in a new window. img_link_open_new_window='false' is the default. Set it to true to activate it.

== 7.6.2 ==
Fixed bug where it wasn't listing ACF fields in a consistent order.

== 7.6.1 ==
Fixed bug where if show_ratings is set to true and star rating plugin not installed causes php errors for empty tables.

== 7.6.0 ==
Added to new parameters. 1) main_title='custom-title' and 2) main_title_link='https://mysite.com/custom-link' so you may now add a custom title to each list and have it link to any page, post or website you desire.

== 7.5.8 ==
Fixed but where page_has_no_child='true' wasn't working. Moved pagination links to their own div so will remain on bottom of post lists.

== 7.3.9 ==
Fixed error where it now works with both the free and paid version of Advanced Custom Fields through JSON call up.

== 7.3.8 ==
Works with ACF fields loaded by json.

== List of Arguments ==

This list of arguments have been moved to [https://agaveplugins.com/npe-tutorial](https://agaveplugins.com/tutorials/plugins/multisite/network-posts-extended/)

*Shortcode Examples:*

1. [netsposts post_type='page'] - Will only show a list of pages from all sites.
1. [netsposts post_type='books' - Will only show posts in the custom post type of **books**.
1. [netsposts include_blog='3,11' - Will only show posts from the sites with the **ID** of 3 and 11. No other posts will be shown.

*Key Features:*

* Shows posts with excerpts from content or manual excerpt field.
* Can limit length of excerpt by letters or words.
* Has the option to show full content of posts.

== List of Arguments ==
= Network Posts Extended Shortcodes and Arguments =

&#91;netsposts include_blog=&#39;1,2,5&#39; days=&#39;30&#39; taxonomy=&#39;news&#39; titles_only=&#39;false&#39; show_author=&#39;true&#39; thumbnail=&#39;true&#39; size=&#39;90,90&#39; image_class=&#39;alignleft&#39; auto_excerpt=&#39;true&#39; excerpt_length=&#39;150&#39; show_author=&#39;true&#39; paginate=&#39;true&#39; list=&#39;5&#39;&#93;

Shortcodes go in text menu on posts editor pages. You may also add them to your sidebar via the WordPress built in text widget. Also paste under text menu.

= Including and/or Excluding pages, posts, categories, and blogs =

include_post &#150; list of posts/pages that you want to include &#40;example: include_post=&#39;5&#39; or include_post=&#39;5,8,153&#39; for multiple posts.

exclude_post &#150; list of posts/pages that you want to exclude &#40;example: exclude_post=&#39;5&#39; &#150; exclude_post=&#39;5,8,153&#39;

include_blog &#150; list of blogs, with the posts which will be displayed &#150; include_blog=&#39;5,8&#39; &#40;default all blogs&#41;

exclude_blog &#150; list of excluded blogs &#40;default none&#41; &#40;works only if include_blogs argument is not present&#41;

filter_excerpt &#150; The plugin will now process and show shortcodes in the excerpt field. Just set filter_excerpt=&#39;true&#39; &#40; Default: false &#41;

taxonomy &#150; list of categories to include in list. Default is all categories. Example: taxonomy=&#39;books&#39; or taxonomy=&#39;digital-books,product,news&#39; for multiple categories. Use slug of category for the taxonomy name.
taxonomy_type can have 3 values - category, tag, all. Example: taxonomy_type=&#39;tag&#39; taxonomy=&#39;records&#39;. That way if there is a category and a tag both named &#39;records&#39; you may choose which to select. To include both just separate with commas. Example: taxonomy_type=&#39;tag,category&#39;. You can only select woocommerce taxonomy_type if post_type=&#39;product&#39; which is a custom woocomerce post type.
If post_type is &#39;product&#39; taxonomy_type can be &#39;product_tag&#39;, &#39;product_cat&#39; or both.

= Miscellaneous show/hide arguments +

days &#150; how old in days the post can be &#40;default 0&#39; &#150; no limit&#41; Example: days=&#39;10&#39; will only show the posts/pages which have been created within the last ten days.

titles_only &#150; if true shows titles only &#40;default false&#41; Example: titles_only=&#39;true&#39; will only show the titles. Not the image or excerpt.

show_author &#150; if true shows a posts author &#40;default false&#41; Example: show_author=&#39;true&#39;

To hide the source area just add the following argument to the shortcode &#150; hide_source=&#39;true&#39; will hide all the meta_info like author, date created, etc...
hide_excerpt=&#39;true&#39; will hide the text, that is the whole excerpt field.

show_categories= &#150; If set to true then it will show a list of categories along with the meta data.

show_tags= If set to true then will show a list of tags the same way as the categories above.

filter_by_title_keywords= Will only show the posts if the title contains certain keyword(s). For example filter_by_title_keywords=&#39;red,blue,orange&#39;. This will only show posts that contain all the words in the title.

must_include_categories= The arguments are true/false (default: false). Here is a little explanation of how this works. You only want to display posts that are in all of the following categories blue, green, orange. So you set up taxonomy='blue,green,orange'. If must_include_categories='true' then only posts that are on all three categories will be displayed. For example Post A and Post B are in all three categories and Post C is only in categoryies blue and green. Then only Posts A and B will be displayed not Post C. If the argument is not set to true then all posts that are in any of the three categories will be displayed. That is Posts A, B and C will all be displayed. Doesn't matter if they are in all three categories or just one of the three categories.

= Thumbnails =

thumbnail &#150; if true shows thumbnails &#40;default false&#41; Example: thumbnail=&#39;true&#39;

size &#150; Displays custom thumbnail size. So if you have a specific thumbnail size you'd like to show enter the name here. Example size=&#39;custom-thumbnail-name&#39;

image_class &#150; CSS class for image &#40;default post&#150;thumbnail&#41; Example: image_class=&#39;custom-image-class&#39;

= Excerpts =

auto_excerpt &#150; if true an excerpt will be taken from post content, if false a post excerpt &#40;shows the short description in the excerpt box. Note you will need to use a plugin to show this box when creating pages instead of posts&#41; will be used &#40;default false&#41;.

excerpt_length &#150; the length of excerpt &#40;in characters&#41; &#40;auto_excerpt should be true&#41;&#40;default 400&#39;&#41; Example: excerpt_length=&#39;500&#39; will show the first 500 characters.

manual_excerpt_length &#150; You can set the length of the manual excerpt. For example if someone has 500 words in the manual excerpt field it may be trimmed down to 400 like so: manual_excerpt_length=&#39;400&#39; &#40;defaul 9999&#41;

= Listing Designs =

post_type &#150; type of posts &#40;default post&#41; Example: post_type=&#39;page&#39; will show pages not posts in the list. To show posts either don&#39;t include this argument &#40;since posts are default&#41; or use post_type=&#39;post&#39;. Now works with custom post types. So you may add post_type=&#39;mycustomposttype&#39;.

full_text &#150; full text instead of excerpt &#40;default false&#41; Example: full_text=&#39;true&#39; will show entire text in content field in list.

= Showing Date =

date_format &#150; format of the post date &#40;default n/j/Y&#41;. This will show January 2 1963 for example. If you would like to show the date first just use: date_format=&#39;j/n/Y&#39;.

= Custom HTML =

wrap_start, wrap_end &#150; you can wrap the posts for example: wrap_start=&#39;&lt;div style=&#34;font&#150;weight:bold;vertical&#150;align:middle;&#34; class=&#34;myclass&#34;&gt;&#39; wrap_end=&#39;&lt;/div&gt;&#39;

wrap_title_start,wrap_title_end &#150; wrap_image_start,wrap_image_end &#150; wrap_text_start,wrap_text_end wrap_excerpt_container_start=&#39;&#39;,wrap_excerpt_container_end=&#39;&#39; . Use the same way as wrap_start,wrap_end above. But will only wrap given argument.

page_title_style &#150; style for the page title &#40;default: none&#41; Example: page_title_style=&#39;italic&#39; will make the title italic. For bold you may use: page_title_style=&#39;bold&#39; for italic and bold use: page_title_style=&#39;italic,bold&#39;

Miscellaneous List Arguments &#150; Pagination Links and Order Post/Page by Properties

end_size &#150; how many numbers on either the start and the end list edges &#40;used for pagination&#41; Example: end_size=&#39;3&#39; will show the first and last three pages as links in numerical form.

mid_size &#150; how many numbers to either side of current page, but not including current page &#40;used for pagination&#41;

order_post_by &#150; Sort in ascending &#40;default value&#41; and descending order via the following arguments &#150; Ascending: order_post_by=&#39;alphabetical_order&#39; order_post_by=&#39;date_order&#39; order_post_by=&#39;page_order&#39; and descending: order_post_by=&#39;alphabetical_order desc&#39; order_post_by=&#39;date_order desc&#39; order_post_by=&#39;page_order desc&#39; &#40;note: descending must be surrounded by single or double quotes because of the empty space after page_order

Pagination &#150; When list is to have multiple pages

paginate &#150; if true the result will be paginated &#40;default false&#41; Example: paginate=&#39;true&#39; will break the list in to multiple pages by the list argument.

list &#150; how many posts per page &#40;default 10&#41; Example: list=&#39;20&#39; will show the last 20 posts or pages. If paginate=&#39;true&#39; is used above then will break the list in to pages showing 20 posts or pages on each page.

prev_next &#150; Whether to include the previous and next links in the list or not &#40;used for pagination. Default: true&#41;

prev &#150; the previous page link text. Works only if prev_next argument is set to true. &#40;Default: &#xab; Previous&#41;

next &#150; The next page text. Works only if prev_next argument is set to true. Example: next=&#39;New Posts&#39; will replace the default &#150; Next &#150; with &#150; New Posts. &#40;Default: Next &#xbb;&#41;

random &#150; Set to true to show posts randomly. To show an x amount of posts randomly make sure the list argument is set to the amount you want. &#40;Default: set to false&#41;

= Custom Arguments =

= Titles =

title &#150; custom title &#40;default: none&#41; Example: title=&#39;Joe&#39;s Favorite Bicycles&#39;

title_color &#150; Color of the title text. Example: title_color=&#39;red&#39; or title=&#39;color:#ff0000&#39; both will give you a color of red. &#40;Default black&#41;

title_length &#150; Cuts off the title at X amount of characters so won&#39;t make long wrap around which looks ugly. The length is in characters including spaces and symbols &#40;Default 999&#41;

include_link_title &#150; This will now make all titles clickable &#40;default false&#41;. If you want the titles to also link to the post or page set this argument to true. Example: include_link_title=&#39;true&#39;

exclude_link_title &#150; This will exclude certain posts/pages from the title being clickable. For example if you don&#39;t want the title to link to posts 8,45,47 you would use: exclude_link_title_posts=&#39;8,45,47&#39;

show_title= Adds the ability to hide titles. For example show_title=&#39;false&#39; will not show the title. Default is true.

= Custom Column Designs =

column &#150; number of columns &#40;default: 1&#41;

column_width &#150; Width of column in pixels. Example column_width=&#39;250&#39;. &#40;Default: 200&#41;

post_height &#150; Sets the default height for all posts. Recommended for 2 column mode. For example if manual_excerpt_length=&#39;400&#39; or excerpt_length=&#39;400&#39; and you want posts with less of an excerpt to have same dimensions use this feature. post_height=&#39;300&#39; will give a standard height of 300 pixels. So if post has less characters of text will still keep square shape so titles line up nicely.

meta_info &#150; Example: meta_info=&#39;false&#39; &#40;Default &#39;true&#39;&#41;

meta_length &#150; Example: meta_length=&#39;75%&#39; &#40;Default 100%&#41;

menu_name &#150; name of the menu &#40;should be created in Appearance > Menu&#41;&#40;default: The one created in Appearance > Menu&#41;

menu_class &#150; CSS class for the menu. Example menu_class=&#39;menu-class&#39;. Separate multiple classes with commas.

remove_blog_prefix=&#39;true&#39; will remove the /blog if multisite is installed in a subdomain or subfolder. Default is false.

container_class &#150; the CSS class that is applied to the menu container

use_shortcode_in_excerpt &#150; If set to true any shortcodes that appear in the excerpt will be processed. Default: false. Note: You must activate the ability to have your site be able to process shortcodes in excerpt field.

show_categories &#150; If set to true will also include a link to the category archive page. show_categories=&#39;true&#39; default is false.

Added ability to call all categories that contain certain keyword or part as in the following example:If you type &#39;test-%&#39; it will find test-1, test-2, test-3 etc.I.e. it will find categories that starts with &#39;test-&#39;. if you type &#39;%test&#39; it will find categories that ends with &#39;test&#39; word.

link_open_new_window=&#39;true&#39; &#40;default false&#41; for all posts and link_open_new_window=&#39;1,2,3,4,5&#39; for posts with given ids.

Taxonomy offsets: 1) taxonomy_offset_names='' and 2) taxonomy_offsets=''. So for example if you wanted to offset three different categories it would look like so: [netsposts taxonomy_offset_names='books,flowers,sports' taxonomy_offsets='5,4,10'] then books would be offset by 5, flowers 4, and sports 10. Offsets means it will skip the X most recent posts.

<strong>Using Custom Image Sizes:</strong>

Under settings > Network Posts Ext menu you will see a box to add a custom image size. You may name it anything you like and use the default alias name or your own. Wants created you must include the following in your shortcode:thumbnail=&#39;true&#39; size=&#39;Name of Alias&#39;. For example if what is listed in the alias box is 600x400 then it would be size=&#39;600x400&#39; so your thumbnails will have a size of 600px wide and 400px high. You may change the diplayed size of these images using custom css.For example you may create a class called img-size-300x200 and change the displayed image size thus: .img-size-300x200 &#x7b; height:300px;width:200px; &#x7d;. Then add this to the shortcode: image_class=&#39;img-size-300x200&#39;. To use default thumbnail size use size=&#39;parent theme&#39; or to use any of the parent theme's default sizes use size=&#39;h,w&#39;<br>
align_image=&#39;right&#39;,align_image=&#39;left&#39;.<br>
use_layout=&#39;default&#39;,use_layout=&#39;inline&#39;<br><br>

Advanced Custom Fields - To add the field with the meta info just use the argument include_acf_fields='name-of-custom-field-slug'. It will appear with the label name followed by the input (default false). If you'd like to include the label just add the argument hide_acf_label=&#39;false&#39;.
Note: All fields are surrounded by &lt;span&gt;&lt;&#47;span&gt; tags. If you'd like to change them you will have to add a bit of coding. Here's some examples.<br><br>

<strong>Network Activated:</strong> Now you can network activate and have full control in the admin menu under settings which site owners can resize images. You may have them resize images just on their own site or globally or not allow them to resize images at all. Works great sold as a premium feature.<br>

*New Arguments*
show_title You may now hide the title by adding the argument show_title=&#39;false&#39; Default is true.

must_include_categories - shows posts that contains all the categories in the list. Example [netsposts must_include_categories=&#39;read,green,orange&#39;] So if the post isn't listed in all three categories it won't show.

filter_by_title_keywords - display posts that contains one ore more of provided words Example if filter_by_title_keywords=&#39;off-roading&#39; then only posts with the word off-roading anywhere in the title will display.

show_tags If show_tags=&#39;true&#39; then will show a linked list of tags. *Note:* The links will go to the archive directory of given tag. Not the post.

show_categories Same arguments as show_tags except categories will be displayed.

show_rating Works with my Post Reviews plugin to show the posts star rating along with the title. Default false.

page_has_no_child - Will only show posts that have no child pages. This is great for archive pages.

show_category_icon= If set to true shows an open folder next to each category listing. Default false.

show_tag_icon= If set to true shows a price tag in pinkish red next to each tag listing. Default false.

*All Below Arguments Work With ACF (Advanced Custom Fields) Plugin*
order_post_by_acf_date (acf_date_field_name) If you have an events directory and have an ACF field with an event date will list the pages by that order. The nearest coming event listed on top.

acf_date_format (n,j,y) If you ACF plugin you can re-order the way the date is displayed using the following guidelines. <a href="https://www.php.net/manual/en/function.date.php" target="_blank">https://www.php.net/manual/en/function.date.php</a>

exclude_all_past_events (field_name) If you have an events setup with an ACF field named events_date then if you have exclude_all_past_events=&#39;events_date&#39; then all events posts with an events_date before today (current date) then they will automatically be removed from the list.

show_past_events (field_name) Works like exclude_all_past_events except it will only display events posts from the past. That is events_date is prior to today.

include_acf_field (field_name) Displays the following fields along with the other content. Like if you have a link field it will display here.

show_after_date (field_name::date) You have a post with a field name of coupon_date and would not like it to display until after the coupon becomes active like December 15th 2020 then you would list it as shown. show_after_date=&#39;coupon_date::12/15/2020&#39;

show_before_date (field_name::date) Same as above but will only show before the given date. So you can set a coupon expiration date.

show_for_today (field_name) Shows only for today. So whatever date is selected in the field_name then it has to be today to be displayed.

add_link_to_acf (true/false) default false Will contain a permalink to the post for all included acf fields.

*End ACF Plugin Dependent Arguments*
domain_mapping (site_url / home_url) If you have domain mapping set up on your site you may choose which format the hyperlinks will display. That is if you have domain_mapping=&#39;site_url&#39; the domain mapped link or the internal sub domain or sub folder link set up. Example if your site that was mapped is https://mywonderfullsite.com then the link will look like https://mywonderfullsite.com/post-name (or however you set up your permalinks). Otherwise if you chose domain_mapping=&#39;home_url&#39; it will display as: https://mywonderfullsite.mainblogsite.com/post-name.

read_more_text (any text you put here will replace the read more text) default is read more... To have it say continue reading just use read_more_text=&#39;continue reading...&#39;

1) Added arabic language support.
2) Created 'show_custom_taxonomies'
3) Created 'show_custom_taxonomy_icon'. - Shows green icon before each custom taxonomy
4) Checked "excerpt length" functionality. Plugin already contains both versions for letters and words: 
excerpt_length/excerpt_letters_length and manual_excerpt_length/manual_excerpt_letters_length.
5) Added functionality to trim titles by words and letters: title_length/title_length_characters.
6) Plugin already inserted category css classes in form of "category-category_slug" in div.netsposts-content. 
7) Made it to insert "tags" css classes in form of "tag-tag_slug".

New attributes (added version 7.1.7):
1. load_posts_dynamically='(true/false)' - allows to load posts without refreshing page. Default false.
2. posts_preloader_icon='(url to icon)' - Set custom icon url. This icon is displayed while posts are loading if "load_posts_dynamically" is true.
3. show_preloader_icon='(true/false)' to hide default spinning icon. Default true.
4. default_thumbnail='(url to default image)' - Must be the full url to the image you want to use.

# Network Posts Extended v 7.1.9
Attributes: 1. shortcode_id - set HTML id for root posts element.
Modified: Made it to support multiple shortcodes with one or more of them that load posts without page refreshing. Set shortcode_id for those shortcodes that loads posts via Ajax. That is if load_posts_dynamically='true' then you must also add the argument shortcode_id='custom-shortcode-id'.<br>This is how I recomend the ID's should be named on a per page basis. Let's say you have four instances of the shortcode on the same page. I would give the first shortcode and ID of "npe-1" thus the shortcode would be shortcode_id='npe-1' then the second shortcode would have an id as such shortcode_id='npe-2' etc.<br>
**Note:** Each shortcode must have a unique id for this to work. Plus html5 requires that an ID name be only used once per page. That is if you have and ID called *myfirstid* that can't be used again on the same page.<br>**Note:** All ID's must begin with a letter. shortcode_id='a12345' is okay while shortcode_id='12345' is **not** okay.

For a complete tutorial please visit: [Agave Premium Plugins NPE Tutorial](https://agaveplugins.com/?p=30)
