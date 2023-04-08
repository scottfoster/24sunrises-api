<?php

require __DIR__ . '/vendor/autoload.php';

require 'models/database.php';
require 'models/sunrises.php';

use Models\Database;
use Models\Sunrises;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

new Database();

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

$empties = Sunrises::where(['user_profile_url' => NULL, 'source' => 'flickr'])->get()->toArray();
foreach($empties as $empty)
{

    $photo_data = $phpFlickr->photos()->getInfo($empty['image_id']);
    $photo_owner = $phpFlickr->people()->getInfo($photo_data['owner']['nsid']);

    $base_image_path = 'https://24sunrises-data.s3.amazonaws.com/';
    $id = $photo_data['owner']['nsid'];
    $userimagepath = 'photos/owners/'.$id.'.jpg';

    $sunrise = Sunrises::where(['image_id' => $photo_data['id']]);
    $sunrise->update([
        'user_image' => $base_image_path . $userimagepath,
        'user_profile_url' => $photo_owner['person']['profileurl']
    ]);
    // $sunrise->update(['user_profile_url' => $photo_owner['person']['profileurl']]);

    echo 'updated ' . $photo_data['id'] . PHP_EOL;
}