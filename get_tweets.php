<?php

require_once('twitter_proxy.php');

/**
 * Parameter documentation borrowed from twitter_proxy.php
 *
 *	@param	string	$oauth_access_token			OAuth Access Token			('Access token' on https://apps.twitter.com)
 *	@param	string	$oauth_access_token_secret	OAuth Access Token Secret	('Access token secret' on https://apps.twitter.com)
 *	@param	string	$consumer_key				OAuth Access Token			('API key' on https://apps.twitter.com)
 *	@param	string	$consumer_secret			OAuth Access Token			('API secret' on https://apps.twitter.com)
 *	@param	string	$user_id					User id (http://gettwitterid.com/)
 *	@param	string	$screen_name				Twitter handle
 *	@param	string	$count						The number of tweets to pull out
 */

// Twitter OAuth Config options
$oauth_access_token = 'your-token-here';
$oauth_access_token_secret = 'your-token-secret';
$consumer_key = 'your-api-key';
$consumer_secret = 'your-api-secret';
$user_id = '78884300';
$screen_name = 'parallax';
$count = 5;

$twitter_url = 'statuses/user_timeline.json?user_id=' . $user_id . '&screen_name=' . $screen_name . '&count=' . $count;

// Create a Twitter Proxy object from our twitter_proxy.php class
$twitter_proxy = new TwitterProxy($oauth_access_token, $oauth_access_token_secret, $consumer_key, $consumer_secret, $user_id, $screen_name, $count);

// Invoke the get method to retrieve results via a cURL request
$tweets = $twitter_proxy->get($twitter_url);

echo $tweets;

?>