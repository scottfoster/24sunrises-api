<?php

require __DIR__ . '/vendor/autoload.php';


require 'models/database.php';
require 'models/sunrises.php';

use Models\Database;
use Models\Sunrises;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

new Database();

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

    $min_date = strtotime('-10 day');
    $photos = $phpFlickr->photos()->search([
        'tags' => 'sunrise',
        'min_taken_date' => $min_date,
        'has_geo' => 1,
        'per_page' => 500,
    ]);

    $usernames = [];

    $client = new Aws\S3\S3Client([
        'region'  => 'us-east-1',
        'version' => 'latest',
        'credentials' => [
            'key'    => "AKIA3PWTTOA6HJW42676",
            'secret' => "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",
        ],
    ]);

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
                $views > 1 &&
                !in_array($photoData['owner']['path_alias'], $usernames) &&
                Sunrises::where('image_id', $photoData['id'])->doesntExist()
            )
            {
                // one per username
                $usernames[] = $photoData['owner']['path_alias'];

                // convert to utc
                $timezonedb = file_get_contents('https://vip.timezonedb.com/v2.1/get-time-zone?key=SQ90W55WY9V6&format=json&by=position&lat='.$lat.'&lng='.$lng);
                $timezone = json_decode($timezonedb);
                $datetime = new DateTime($taken, new DateTimeZone($timezone->zoneName));
                $offset = str_replace(['0',':'],'',$datetime->format('p'));
                if(($offset == '') || $offset == '+'){ $offset = 0; }
                $datetime->setTimezone(new DateTimeZone('UTC'));

                echo $url . '|' . $offset . '|' . $datetime->format('Y-m-d H:i:sP') . '|' . $lat . '|' . $lng . PHP_EOL;

                $location = $timezone->cityName . ', ' . $timezone->countryName;
                if($timezone->countryCode == 'US')
                {
                    $location = $timezone->cityName . ', ' . $timezone->regionName . ' ' . $timezone->countryCode;
                }

                $id = $photoData['id'];
                $imagepath = 'photos/'.$id.'.jpg';
                file_put_contents('/tmp/' . $id, file_get_contents($url));

                $result = $client->putObject([
                    'Bucket' => '24sunrises-data',
                    'SourceFile' => '/tmp/' . $id,
                    'Key' => $imagepath,
                    'ContentType' => 'image/jpeg'
                ]);

                $sunrise = new Sunrises();
                $sunrise->image_id = $photoData['id'];
                $sunrise->image_path = $imagepath;
                $sunrise->username = $photoData['owner']['username'];
                $sunrise->taken_at = $datetime->format('Y-m-d H:i:sP');
                $sunrise->offset = $offset;
                $sunrise->location = $location;

                $sunrise->save();

            }

        } catch (Exception $e) {
            echo 'ERROR!! ' . $e->getMessage() . PHP_EOL;
        }

    }

};