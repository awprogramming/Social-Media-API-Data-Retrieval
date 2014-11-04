<?php
ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');
/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$settings = array(
'oauth_access_token' => "2859830771-kJyoCwQQUxL6hio3JwL7X9GL6vXju0MPpWFEZmb",
'oauth_access_token_secret' => "Sq9vRbR00kp6lrIBLz74hBW0uV8yGf4DAi2oU2PB9HmeE",
'consumer_key' => "Mj8MqCTPDQAlCNqndnbcwjNvp",
'consumer_secret' => "SlROjgVpdrfkLUvm04LNxP7jTmI0EzTHxcoZxfowxE9CmdTzBh"
);

$twitter = new TwitterAPIExchange($settings);

/** Perform a GET request and echo the response **/
/** Note: Set the GET field BEFORE calling buildOauth(); **/
$url = 'https://api.twitter.com/1.1/followers/ids.json';
$getfield = '?screen_name=Gorelick92&count=10'; //**** NAME HERE ****
$requestMethod = 'GET';


//echo $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();

$decoded = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());

$ids = $decoded->ids;

$next_cursor = $decoded->next_cursor;

while($next_cursor != 0)
{
	$url = 'https://api.twitter.com/1.1/followers/ids.json';
	$getfield = '?screen_name=Gorelick92&cursor=' . $next_cursor; //**** NAME HERE ****
	$requestMethod = 'GET';

	$decoded = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());
	$moreids = $decoded->ids;

	$ids = array_merge($ids,$moreids);

	$next_cursor = $decoded->next_cursor;
}

//$ids has all ids!


