# MP Views WordPress plugin.

How to work with the plugin:

1. Install and activate the plugin in the WordPress site admin panel.
2. Place the following code on the post archive page. For example, in **archive.php** or **content.php**:

```php
<?php
if ( shortcode_exists( 'mpviews_counter' ) ) {
	echo do_shortcode( '[mpviews_counter]' ); 
}
?>
```
3. For the counter to work correctly with caching plugins add the name of the cookie to the exceptions:

`mp_views_viewed_pages`