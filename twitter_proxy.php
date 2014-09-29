<?php

/**
 * Similar to Facebook Apps, you'll need to create a Twitter app first: https://apps.twitter.com/
 * 
 * Code below from http://stackoverflow.com/questions/12916539/simplest-php-example-retrieving-user-timeline-with-twitter-api-version-1-1 by Rivers 
 * with a few modfications by Mike Rogers to support variables in the URL nicely
 */

class TwitterProxy {

	/**
	 * The tokens, keys and secrets from the app you created at https://dev.twitter.com/apps
	 */
	private $config = [
		'use_whitelist' => true, // If you want to only allow some requests to use this script.
		'base_url' => 'https://api.twitter.com/1.1/'
	];
	
	/**
	 * Only allow certain requests to twitter. Stop randoms using your server as a proxy.
	 */
	private $whitelist = [];

	/**
	 *	@param	string	$oauth_access_token			OAuth Access Token			('Access token' on https://apps.twitter.com)
	 *	@param	string	$oauth_access_token_secret	OAuth Access Token Secret	('Access token secret' on https://apps.twitter.com)
	 *	@param	string	$consumer_key				Consumer key				('API key' on https://apps.twitter.com)
	 *	@param	string	$consumer_secret			Consumer secret				('API secret' on https://apps.twitter.com)
	 *	@param	string	$user_id					User id (http://gettwitterid.com/)
	 *	@param	string	$screen_name				Twitter handle
	 *	@param	string	$count						The number of tweets to pull out
	 */
	public function __construct($oauth_access_token, $oauth_access_token_secret, $consumer_key, $consumer_secret, $user_id, $screen_name, $count = 5) {

		$this->config = array_merge($this->config, compact('oauth_access_token', 'oauth_access_token_secret', 'consumer_key', 'consumer_secret', 'user_id', 'screen_name', 'count'));

		$this->whitelist['statuses/user_timeline.json?user_id=' . $this->config['user_id'] . '&screen_name=' . $this->config['screen_name'] . '&count=' . $this->config['count']] = true;
	}

	private function buildBaseString($baseURI, $method, $params) {
		$r = [];
		ksort($params);
		foreach($params as $key=>$value){
			$r[] = "$key=" . rawurlencode($value);
		}

		return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
	}

	private function buildAuthorizationHeader($oauth) {
		$r = 'Authorization: OAuth ';
		$values = [];
		foreach($oauth as $key => $value) {
			$values[] = "$key=\"" . rawurlencode($value) . "\"";
		}
		$r .= implode(', ', $values);

		return $r;
	}
	
	public function get($url) {
		if (! isset($url)){
			die('No URL set');
		}		
		 
		if ($this->config['use_whitelist'] && ! isset($this->whitelist[$url])){
			die('URL is not authorised');
		}
		 
		// Figure out the URL parameters
		$url_parts = parse_url($url);
		parse_str($url_parts['query'], $url_arguments);
		 
		$full_url = $this->config['base_url'] . $url; // URL with the query on it
		$base_url = $this->config['base_url'] . $url_parts['path']; // URL without the query
		 
		// Set up the OAuth Authorization array
		$oauth = [
			'oauth_consumer_key' => $this->config['consumer_key'],
			'oauth_nonce' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_token' => $this->config['oauth_access_token'],
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0'
		];

		$base_info = $this->buildBaseString($base_url, 'GET', array_merge($oauth, $url_arguments));
		
		$composite_key = rawurlencode($this->config['consumer_secret']) . '&' . rawurlencode($this->config['oauth_access_token_secret']);

		$oauth['oauth_signature'] = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
		 
		// Make Requests
		$header = [
			$this->buildAuthorizationHeader($oauth), 
			'Expect:'
		];
		$options = [
			CURLOPT_HTTPHEADER => $header,
			//CURLOPT_POSTFIELDS => $postfields,
			CURLOPT_HEADER => false,
			CURLOPT_URL => $full_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		];
		 
		$feed = curl_init();
		curl_setopt_array($feed, $options);
		$result = curl_exec($feed);
		$info = curl_getinfo($feed);
		curl_close($feed);
		 
		// Send suitable headers to the end user.
		if (isset($info['content_type']) && isset($info['size_download'])){
			header('Content-Type: ' . $info['content_type']);
			header('Content-Length: ' . $info['size_download']);
		}

		return $result;
	}
}