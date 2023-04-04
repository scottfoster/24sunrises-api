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

    $base_image_path = 'https://24sunrises-data.s3.amazonaws.com/';

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

    $client = new Aws\S3\S3Client([
        'region'  => 'us-east-1',
        'version' => 'latest',
        'credentials' => [
            'key'    => "AKIA3PWTTOA6HJW42676",
            'secret' => "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",
        ],
    ]);

    $tz[] = ['name' => 'UTC-11', 'offset' => '-11', 'cities' => 'American Samoa', 'Jarvis Island', 'lat' => 0, 'lng' => '-178.9'];
    $tz[] = ['name' => 'UTC-10', 'offset' => '-10', 'cities' => 'Cook Islands, French Polynesia', 'lat' => 0, 'lng' => '-142.5'];
    $tz[] = ['name' => 'UTC-9', 'offset' => '-9', 'cities' => 'Anchorage, Gambier Islands', 'lat' => 0, 'lng' => '-127.5'];
    $tz[] = ['name' => 'UTC-8', 'offset' => '-8', 'cities' => 'Los Angeles, Vancouver, Tijuana', 'lat' => 0, 'lng' => '-112.5'];
    $tz[] = ['name' => 'UTC-7', 'offset' => '-7', 'cities' => 'Denver, Edmonton, Ciudad Juárez', 'lat' => 0, 'lng' => '-97.5'];
    $tz[] = ['name' => 'UTC-6', 'offset' => '-6', 'cities' => 'Mexico City, Chicago, Guatemala City', 'lat' => 0, 'lng' => '-82.5'];
    $tz[] = ['name' => 'UTC-5', 'offset' => '-5', 'cities' => 'New York, Toronto, Havana', 'lat' => 0, 'lng' => '-67.5'];
    $tz[] = ['name' => 'UTC-4', 'offset' => '-4', 'cities' => 'Santiago, Santo Domingo, Manaus', 'lat' => 0, 'lng' => '-52.5'];
    $tz[] = ['name' => 'UTC-3', 'offset' => '-3', 'cities' => 'São Paulo, Buenos Aires, Montevideo', 'lat' => 0, 'lng' => '-37.5'];
    $tz[] = ['name' => 'UTC-2', 'offset' => '-2', 'cities' => 'South Georgia, Fernando de Noronha', 'lat' => 0, 'lng' => '-22.5'];
    $tz[] = ['name' => 'UTC-1', 'offset' => '-1', 'cities' => 'Cape Verde, Denmark, Greenland', 'lat' => 0, 'lng' => '-7.5'];
    $tz[] = ['name' => 'UTC', 'offset' => '0', 'cities' => 'London, Dublin, Lisbon', 'lat' => 0, 'lng' => '7.5'];
    $tz[] = ['name' => 'UTC+1', 'offset' => '+1', 'cities' => 'Berlin, Rome, Paris', 'lat' => 0, 'lng' => '22.5'];
    $tz[] = ['name' => 'UTC+2', 'offset' => '+2', 'cities' => 'Cairo, Johannesburg, Khartoum', 'lat' => 0, 'lng' => '37.5'];
    $tz[] = ['name' => 'UTC+3', 'offset' => '+3', 'cities' => 'Moscow, Istanbul, Riyadh', 'lat' => 0, 'lng' => '52.5'];
    $tz[] = ['name' => 'UTC+4', 'offset' => '+4', 'cities' => 'Dubai, Baku, Tbilisi', 'lat' => 0, 'lng' => '67.5'];
    $tz[] = ['name' => 'UTC+5', 'offset' => '+5', 'cities' => 'Karachi, Tashkent, Yekaterinburg', 'lat' => 0, 'lng' => '82.5'];
    $tz[] = ['name' => 'UTC+6', 'offset' => '+6', 'cities' => 'Dhaka, Almaty, Omsk', 'lat' => 0, 'lng' => '97.5'];
    $tz[] = ['name' => 'UTC+7', 'offset' => '+7', 'cities' => 'Jakarta, Surabaya, Medan', 'lat' => 0, 'lng' => '112.5'];
    $tz[] = ['name' => 'UTC+8', 'offset' => '+8', 'cities' => 'Shanghai, Taipei, Kuala Lumpur', 'lat' => 0, 'lng' => '127.5'];
    $tz[] = ['name' => 'UTC+9', 'offset' => '+9', 'cities' => 'Tokyo, Seoul, Pyongyang', 'lat' => 0, 'lng' => '142.5'];
    $tz[] = ['name' => 'UTC+10', 'offset' => '+10', 'cities' => 'Sydney, Port Moresby, Vladivostok', 'lat' => 0, 'lng' => '157.5'];
    $tz[] = ['name' => 'UTC+11', 'offset' => '+11', 'cities' => 'Nouméa', 'lat' => 0, 'lng' => '172.5'];
    $tz[] = ['name' => 'UTC+12', 'offset' => '+12', 'cities' => 'Auckland, Suva', 'lat' => 0, 'lng' => '180'];

    foreach($tz as &$timezone)
    {

        $photos = $phpFlickr->photos()->search([
            'tags' => 'sunrise', 'sunrises', 'morning', 'goodmorning',
            'has_geo' => 1,
            'per_page' => 100,
            // 'lat' => $timezone['lat'],
            'lon' => $timezone['lng'],
            'privacy_filter' => 1,
            'safe_search' => 1,
            'content_type' => 1,
            'min_taken_date' => strtotime('-6 hours'),
        ]);

        echo 'count photos to parse - ' . count($photos['photo']) . ' - ' . $timezone['name'] . PHP_EOL;

        $usernames = [];

        foreach($photos['photo'] as $photo)
        {
            try
            {

                $photo_data = $phpFlickr->photos()->getInfo($photo['id'], $photo['secret']);

                $url = 'https://live.staticflickr.com/'.$photo_data['server'].'/'.$photo_data['id'].'_'.$photo_data['secret'].'_b.jpg';
                $lat = $photo_data['location']['latitude'];
                $lng = $photo_data['location']['longitude'];
                $views = $photo_data['views'];
                $taken = $photo_data['dates']['taken'];

                if(
                    $photo['ispublic'] == 1 &&
                    $views > 5 &&
                    // !in_array($photo_data['owner']['path_alias'], $usernames) &&
                    Sunrises::where('image_id', $photo_data['id'])->doesntExist()
                )
                {
                    // one per username
                    $usernames[] = $photo_data['owner']['path_alias'];

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

                    $id = $photo_data['id'];
                    $imagepath = 'photos/'.$id.'.jpg';
                    file_put_contents('/tmp/' . $id, file_get_contents($url));

                    $result = $client->putObject([
                        'Bucket' => '24sunrises-data',
                        'SourceFile' => '/tmp/' . $id,
                        'Key' => $imagepath,
                        'ContentType' => 'image/jpeg'
                    ]);

                    $user_image = 'http://farm'.$photo_data['owner']['iconfarm'].'.staticflickr.com/'.$photo_data['owner']['iconserver'].'/buddyicons/'.$photo_data['owner']['nsid'].'.jpg';
                    $id = $photo_data['owner']['nsid'];
                    $userimagepath = 'photos/owners/'.$id.'.jpg';
                    file_put_contents('/tmp/' . $id, file_get_contents($user_image));

                    $result = $client->putObject([
                        'Bucket' => '24sunrises-data',
                        'SourceFile' => '/tmp/' . $id,
                        'Key' => $userimagepath,
                        'ContentType' => 'image/jpeg'
                    ]);

                    $sunrise = new Sunrises();
                    $sunrise->image_id = $photo_data['id'];
                    $sunrise->image_path = $base_image_path . $imagepath;
                    $sunrise->username = $photo_data['owner']['username'];
                    $sunrise->user_image = $base_image_path . $userimagepath;
                    $sunrise->taken_at = $datetime->format('Y-m-d H:i:sP');
                    $sunrise->offset = $offset;
                    $sunrise->location = $location;
                    $sunrise->latitude = $lat;
                    $sunrise->longitude = $lng;
                    $sunrise->points = $photo_data['views'];
                    $sunrise->save();

                }

            } catch (Exception $e) {
                echo 'ERROR!! ' . $e->getMessage() . PHP_EOL;
            }

        }

    }

};