<?php

require_once 'vendor/autoload.php';
require 'bootstrap.php';

return function ($event)
{
    $apiKey = 'f3863ebcf7b9d871044b59f04ce1035c';
    $apiSecret = 'b08cd3e1b34bba18';

    $accessToken = "72157720877637964-1067c9ded40cbdeb";
    $accessTokenSecret = "5aba20bd289175db";

    $token = new \OAuth\OAuth1\Token\StdOAuth1Token();
    $token->setAccessToken($accessToken);
    $token->setAccessTokenSecret($accessTokenSecret);
    $storage = new \OAuth\Common\Storage\Memory();
    $storage->storeAccessToken('Flickr', $token);
    $phpFlickr = new \Samwilson\PhpFlickr\PhpFlickr($apiKey, $apiSecret);
    $phpFlickr->setOauthStorage($storage);

    $min_date = strtotime('-4 day');
    $photos = $phpFlickr->photos()->search([
        'tags' => 'sunrise',
        'min_taken_date' => $min_date,
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

            $url = 'https://live.staticflickr.com/'.$photoData['server'].'/'.$photoData['id'].'_'.$photoData['secret'].'_b.jpg';
            $lat = $photoData['location']['latitude'];
            $lng = $photoData['location']['longitude'];
            $views = $photoData['views'];
            $taken = $photoData['dates']['taken'];

            if(
                $photo['ispublic'] == 1 &&
                $views > 10 &&
                !in_array($photoData['owner']['path_alias'], $usernames))
            {

                // one per username
                $usernames[] = $photoData['owner']['path_alias'];

                // convert to utc
                $timezonedb = file_get_contents('https://vip.timezonedb.com/v2.1/get-time-zone?key=SQ90W55WY9V6&format=json&by=position&lat='.$lat.'&lng='.$lng);
                $timezone = json_decode($timezonedb)->zoneName;
                $datetime = new DateTime($taken, new DateTimeZone($timezone));
                $offset = str_replace(['0',':'],'',$datetime->format('p'));
                $datetime->setTimezone(new DateTimeZone('UTC'));

                echo $url . '|' . $offset . '|' . $datetime->format('Y-m-d H:i:sP') . '|' . $lat . '|' . $lng . PHP_EOL;

            }

        } catch (Exception $e) {
            // echo 'Bad ' . $e->getMessage() . PHP_EOL;
        }

    }

};