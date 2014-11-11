<?php
ini_set('display_errors', 1);
$channelname = $_GET['channel'];

$youtubeInfo = array();

$youtubeInfo['channel_name'] = $channelname;


$option = array(
        'part' => 'statistics',
        'forUsername' => $channelname, 
        'key' => 'AIzaSyDFaBlX4JoUqIGeRCLDmLLMfOTXd4Md7aw'
    );
$url = "https://www.googleapis.com/youtube/v3/channels?".http_build_query($option, 'a', '&');
$curl = curl_init($url);

curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  

$json_response = curl_exec($curl);

curl_close($curl);
$responseObj = json_decode($json_response);
$exists = true;

if($responseObj->pageInfo->totalResults>0)
{
  $channelId = $responseObj->items[0]->id;
  $youtubeInfo['channel_id'] = $channelId;
  $youtubeInfo['channel_stats'] = $responseObj->items[0]->statistics;

  $option = array(
        'part' => 'id',
        'channelId' => $channelId,
        'maxResults' => 3,
        'order' => 'date', 
        'key' => 'AIzaSyDFaBlX4JoUqIGeRCLDmLLMfOTXd4Md7aw'
    );
  
  $url = "https://www.googleapis.com/youtube/v3/search?".http_build_query($option, 'a', '&');
  $curl = curl_init($url);

  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  

  $json_response = curl_exec($curl);
  curl_close($curl);
  $responseObj = json_decode($json_response);

  $vids = "";
  for($i = 0; $i < 3; $i++)
  {
    if(!is_null($responseObj->items[$i]))
    {
      if($i>0)
      {
        $vids = $vids . ",";
      }
      $vids = $vids . $responseObj->items[$i]->id->videoId;
    }
    else
    {
      break;
    }
  }
  if($vids=="")
  {
    $youtubeInfo['videos']="No videos currently on channel.";
  }
  else
  {
  $option = array(
          'part' => 'snippet,statistics',
          'id' => $vids,
          'key' => 'AIzaSyDFaBlX4JoUqIGeRCLDmLLMfOTXd4Md7aw'
      );
  $url = "https://www.googleapis.com/youtube/v3/videos?".http_build_query($option, 'a', '&');
  $curl = curl_init($url);

  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  

  $json_response = curl_exec($curl);
  curl_close($curl);
  $responseObj = json_decode($json_response);

  $numResults = $responseObj->pageInfo->totalResults;
  $videos = array();
  for($i = 0; $i < $numResults; $i++)
  {
    $videos[] = array(
        'id'=>$responseObj->items[$i]->id,
        'videoInfo'=>$responseObj->items[$i]->snippet,
        'videoStats'=>$responseObj->items[$i]->statistics
      );
  }
  $youtubeInfo['videos'] = $videos;
  }
}
else
{
  $youtubeInfo['err'] = "Channel not found.";
}

echo json_encode($youtubeInfo);

?>









