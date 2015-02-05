<?php

function la_simple_instagram_feed_init() {
	add_shortcode('instagram-simple-feed', 'la_simple_instagram_feed');
}
add_action('init', 'la_simple_instagram_feed_init');

function la_simple_instagram_feed($atts) {
	$defaults = array(
		'user' => '',
		'token' => '',
		'count' => '10',
		'template_func' => 'la_simple_instagram_feed_template'
	);
	$atts = shortcode_atts($defaults, $atts);
	
	if (function_exists($atts['template_func'])) {
		$func = $atts['template_func'];
	} else {
		$func = $defaults['template_func'];
	}
	
	if ($atts['user']  == '') return '<!-- Simple Instagram Feed Error: Invalid User -->';
	if ($atts['token'] == '') return '<!-- Simple Instagram Feed Error: Invalid Token -->';
	
	$transient_name = __FUNCTION__ . '_' . $atts['user'] . '_' . $atts['count'];
	$pics = json_decode(get_transient($transient_name));
	if (!$pics) {
		$url = sprintf(
			'https://api.instagram.com/v1/users/%1$s/media/recent?',
			urlencode($atts['user'])
		).http_build_query(
			'access_token' => urlencode($atts['token']),
			'count'        => urlencode($atts['count']),
		);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = json_decode(curl_exec($ch));
		curl_close($ch);
		$pics = $data->data;
	}
	
	if (is_array($pics)) {
		// Save the data, keep for an hour. This gives a max usage of 24 requests per day.
		set_transient($transient_name, json_encode($pics), HOUR_IN_SECONDS);
		return $func($pics);
	} else if (isset($data->meta)) {
		return "<!-- Simple Instagram Feed Error: {$data->meta->error_message} -->";
	} else {
		return '<!-- Simple Instagram Feed Error: Unknown error -->';
	}
}

function la_simple_instagram_feed_template($pics) {
	foreach ($pics as $pic) { ?>
		<a href="<?php echo esc_url($pic->link) ?>" title="<?php echo esc_attr($pic->caption->text) ?>">
			<img src="<?php echo esc_url($pic->images->thumbnail->url) ?>" />
		</a>
	<?php }
}