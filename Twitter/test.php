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

$user = 'emilysimms7';
$twitterInfo = array();

$twitter = new TwitterAPIExchange($settings);

$followerIDs = getIDs($twitter,$user);
$twitterInfo['follower_count'] = count($followerIDs);
//echo "FOLLOWER COUNT: " .  count($followerIDs);
//echo "</br></br>";
$twitterInfo['follower_ids'] = $followerIDs;
// echo "FOLLOWER LIST: " . json_encode($followerIDs);
// echo "</br></br>";

//echo "LOCATIONS: </br></br>";
$followers = array();
for($i=0;$i<14;$i++)
{
	if(is_null($followerIDs[$i]))
	{
		break;
	}
	$url = 'https://api.twitter.com/1.1/users/show.json';
	$getfield = '?user_id=' . $followerIDs[$i];
	$requestMethod = 'GET';
	$decoded = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());
	$location = $decoded->location;

	if($location == null)
	{
		$location = "No Location Listed";
	}
	
	// echo "<b>ID: " . $followerIDs[$i] . "</b></br>";
	// echo $location . "</br>";

	// getIDs($twitter,$user,$followerIDs[$i]) . "</br></br>";
	$followers[] = array(
			'id' => $followerIDs[$i],
			'location' => $location,
			'following' => getIDs($twitter,$user,$followerIDs[$i])
		);
}

$twitterInfo['followers'] = $followers;

echo json_encode($twitterInfo);

function getIDs($twitter,$twitterSN,$twitterID=null)
{
	$whichType = "followers";
	$getfieldPiece = '?screen_name=' . $twitterSN;
	if(!is_null($twitterID))
	{
		$whichType = "friends";
		$getfieldPiece = '?user_id=' . $twitterID;
	}

	$url = 'https://api.twitter.com/1.1/'. $whichType .'/ids.json';
	$getfield = $getfieldPiece; 
	$requestMethod = 'GET';

	$decoded = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());

	if(!is_null($decoded->errors))
	{
		return $decoded->errors[0]->message;
	}

	$ids = $decoded->ids;

	$idsForCount = $ids;

	// CODE TO CURSOR THROUGH ALL FOLLOWERS
	$next_cursor = $decoded->next_cursor;

	while($next_cursor != 0)
	{
		$url = 'https://api.twitter.com/1.1/'.$whichType.'/ids.json';
		$getfield = $getfieldPiece . '&cursor=' . $next_cursor; //**** NAME HERE ****
		$requestMethod = 'GET';

		$decoded = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());
		$moreids = $decoded->ids;

		$idsForCount = array_merge($idsForCount,$moreids);

		$next_cursor = $decoded->next_cursor;
	}

	return $idsForCount;
}


