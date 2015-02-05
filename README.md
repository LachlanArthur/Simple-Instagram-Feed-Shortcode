# Simple Instagram Feed Shortcode

A Wordpress shortcode that outputs a user's latest images.

- Requests are cached as transients for 1 hour.
- Template function is overrideable for each shortcode instance.
- Uses http://instagram.com/developer/endpoints/users/#get_users_media_recent

## Example
### Shortcode

	[simple-instagram-feed user="000000000" token="000000000.0000000.00000000000000000000000000000000" count="20" template_func="my_bootstrap_instagram_output"]

- `user`: The numerical ID of the user whose images are being requested
- `token`: The client-side auth token you generated
- `count`: Number of images to get. Default is 10.
- `template_func`: Name of custom function to use for image output.
Default output is `<img />` tags wrapped in `<a>`s.

### Template function

```php
<?php
function my_bootstrap_instagram_output($pics) {
	if (count($pics) > 0) { ?>
		<div id="instagram-box" class="row">
			<?php foreach ($pics as $pic) { ?>
				<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
					<a href="<?php echo esc_url($pic->link) ?>" title="<?php echo esc_attr($pic->caption->text) ?>">
						<img src="<?php echo esc_url($pic->images->thumbnail->url) ?>" class="img-responsive" />
					</a>
				</div>
			<?php } ?>
		</div>
	<?php }
}
?>
```

## Notes

- Doesn't support `min_timestamp`, `max_timestamp`, `min_id` or `max_id`.