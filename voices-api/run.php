<?php

require_once('src/VoicesClass.php');

// simple curl to get the content type of the clean image url
function simple_curl($url)
{
  $ch = curl_init();
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt ($ch, CURLOPT_URL, $url);
  curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
  curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_NOBODY, true);
  $content = curl_exec ($ch);
  $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
  curl_close ($ch);
  return $contentType;
}

$voices = new Voices('fRDAJDdZ5MARxRMFivBn5O7cMOuyugN3hbzjUjYo', array(
  'debug' => false
  ));

$programs = $voices->get_programs();

$talents = array();

foreach ($programs as $program) {
  $talents[] = $voices->get_talents($program->program_id);
}

$items = array();
$counter = 0;

function clean_image_url($avatar_url)
{
  $parts = pathinfo($avatar_url);
  $extension = preg_replace('/\_+([0-9]+)/i', '', $parts['extension']);
  $image = str_replace('_av.', '.', $avatar_url);
  // '/\.jp+([eg|g]+)?\_+([0-9]+)/i'
  $image = preg_replace('/\.jp+([eg|g]+)?\_+([0-9]+)/i', '.'.$extension, $image);
  return $image;
}

function get_audio_url($player_url)
{
  $html = file_get_contents($player_url);
  // get all URLs
  preg_match_all('%\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))%s', $html, $matches);
  // clean the URLs removing the end js junk
  $clean = preg_replace('/\"\,\"\/([a-z])+.*/i', '', $matches[1][3]);
  $clean = preg_replace('/\.mp3\?(.*)/', '.mp3', $clean);
  return $clean;
}

foreach ($talents as $talent) {
  foreach ($talent as $person) {
    // no demo
    if(empty($person->demo->url)) {
      continue;
    } else {
      $image = clean_image_url($person->avatar);
      // $type = simple_curl($image);
      // if an image wasnt returned, the process didn't work
      // if ($type == 'image/jpeg') {
        $items[$counter] = array(
          'id' => $person->talent_id,
          'url' => $person->talent_url,
          'name' => $person->first_name,
          'gender' => $person->gender,
          'player' => $person->demo->url,
          'audio' => get_audio_url($person->demo->url),
          'image' => clean_image_url($person->avatar),
          'original_image' => $person->avatar
          );
        $counter++;
      // }
    }
  }
}

// // // cache the data
// file_put_contents('cache.php', serialize($items));
// echo 'Cached results to cache.php';
file_put_contents('cache.json', json_encode($items));
echo 'Cached results to cache.json';
// load from file cache
// $items = unserialize(file_get_contents('cache.php'));

// print_r($items);