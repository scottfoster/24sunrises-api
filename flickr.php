<?php

require_once 'vendor/autoload.php';

$apiKey = 'f3863ebcf7b9d871044b59f04ce1035c';
$apiSecret = 'b08cd3e1b34bba18';

$accessToken = "72157720877637964-1067c9ded40cbdeb";
$accessTokenSecret = "5aba20bd289175db";

// Add your access token to the storage.
$token = new \OAuth\OAuth1\Token\StdOAuth1Token();
$token->setAccessToken($accessToken);
$token->setAccessTokenSecret($accessTokenSecret);
$storage = new \OAuth\Common\Storage\Memory();
$storage->storeAccessToken('Flickr', $token);

// Create PhpFlickr.
$phpFlickr = new \Samwilson\PhpFlickr\PhpFlickr($apiKey, $apiSecret);

// Give PhpFlickr the storage containing the access token.
$phpFlickr->setOauthStorage($storage);

$min = strtotime('-3 day');
$photos = $phpFlickr->photos()->search([
    'tags' => 'sunrise',
    'min_taken_date' => $min,
    'has_geo' => 1,
    'per_page' => 500,
]);

$usernames = [];

foreach($photos['photo'] as $photo)
{
    try
    {
        $photoData = $phpFlickr->photos()->getInfo($photo['id'], $photo['secret']);
        $photoExif = $phpFlickr->photos()->getExif($photo['id'], $photo['secret']);

        $url = $photoData['urls']['url'][0]['_content'];
        $lat = $photoData['location']['latitude'];
        $lng = $photoData['location']['longitude'];
        $views = $photoData['views'];
        $taken = $photoData['dates']['taken'];

        if($photo['ispublic'] == 1 && $views > 10 && !in_array($photoData['owner']['path_alias'], $usernames))
        {

            // one per username
            $usernames[] = $photoData['owner']['path_alias'];

            echo $url . '|' . $views . '|' . $taken . '|' . $lat . '|' . $lng . PHP_EOL;

            /*
            $datetime = new DateTime();
            $timezone = new DateTimeZone('Europe/Bucharest');
            $datetime->setTimezone($timezone);
            echo $datetime->format('F d, Y H:i');

            $given->setTimezone(new DateTimeZone("UTC"));
            echo $given->format("Y-m-d H:i:s e") . "\n"; // 2014-12-12 07:18:00 UTC

            */
            /*
            echo '<pre>';
            print_r($photoExif);
            echo '</pre>';
            */
        }

    } catch (Exception $e) {
        // echo 'Bad ' . $e->getMessage() . PHP_EOL;
    }

}

die;