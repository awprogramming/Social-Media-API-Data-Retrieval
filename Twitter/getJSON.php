<?php
//ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');
/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$settings = array(
'oauth_access_token' => "2859830771-kJyoCwQQUxL6hio3JwL7X9GL6vXju0MPpWFEZmb",
'oauth_access_token_secret' => "Sq9vRbR00kp6lrIBLz74hBW0uV8yGf4DAi2oU2PB9HmeE",
'consumer_key' => "Mj8MqCTPDQAlCNqndnbcwjNvp",
'consumer_secret' => "SlROjgVpdrfkLUvm04LNxP7jTmI0EzTHxcoZxfowxE9CmdTzBh"
);

$user = $_GET['id'];
//$user = '';
$twitterInfo = array();
$twitterInfo['user_screenname'] = $user;
$twitter = new TwitterAPIExchange($settings);

$followerIDs = getIDs($twitter,$user);
$twitterInfo['follower_count'] = count($followerIDs);
$twitterInfo['follower_ids'] = $followerIDs;

if($followerIDs=="Rate limit exceeded")
{
	$twitterInfo['follower_count'] = $followerIDs;
	$followers = $followerIDs;
}
else{
$followers = array();
for($i=0;$i<15;$i++)
{
	$rand = mt_rand(0,count($followerIDs));
	$url = 'https://api.twitter.com/1.1/users/show.json';
	$getfield = '?user_id=' . $followerIDs[$rand];
	$requestMethod = 'GET';
	$decoded = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());
	if($decoded->protected)
	{
		$protected = 'Protected Account, No access';
		$followers[] = array(
			'id' => $followerIDs[$rand],
			'location' => $location,
			'following' => $protected
		);
	}
	else{
		$location = $decoded->location;

		if($location == null)
		{
			$location = "No Location Listed";
		}

		$followers[] = array(
				'id' => $followerIDs[$rand],
				'location' => $location,
				'following' => getIDs($twitter,$user,$followerIDs[$rand])
			);
	}
}
}

$twitterInfo['followers'] = $followers;

$file = 'twitter.txt';
// Open the file to get existing content
$current = file_get_contents($file);
$current .= json_encode($twitterInfo) . PHP_EOL . PHP_EOL;
// Write the contents back to the file
file_put_contents($file, $current);

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

	if(isset($decoded->errors))
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
		$getfield = $getfieldPiece . '&cursor=' . $next_cursor;
		$requestMethod = 'GET';

		$decoded = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());
		if(isset($decoded->errors[0]))
		{
			break;
		}
		$moreids = $decoded->ids;
		$idsForCount = array_merge($idsForCount,$moreids);

		$next_cursor = $decoded->next_cursor;
	}

	return $idsForCount;
}


