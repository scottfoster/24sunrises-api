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

    $key = 'h6uacJ-94jt_jS4fzJlfeLrjzrV37RxwZ_oA-xtbr9s';
    $client = new \GuzzleHttp\Client();

    foreach(['sunrise', 'sunrises', 'goodmorning'] as $query)
    {
        $response = $client->request('GET', 'https://api.unsplash.com/search/photos?query='.$query.'&per_page=100&order_by=latest&client_id=' . $key);
        $photos = json_decode($response->getBody());

        foreach($photos->results as $photo)
        {

            $response = $client->request('GET', 'https://api.unsplash.com/photos/'.$photo->id.'?client_id=' . $key);
            $photo_data = json_decode($response->getBody());

            if(
                $photo_data->location->position->latitude != 0 &&
                $photo_data->location->position->longitude != 0 &&
                $photo_data->views > 1 &&
                // !in_array($photo_data['owner']['path_alias'], $usernames) &&
                Sunrises::where('image_id', $photo_data->id)->doesntExist()
            )
            {

                $latitude = $photo_data->location->position->latitude;
                $longitude = $photo_data->location->position->longitude;

                $timezonedb = file_get_contents('https://vip.timezonedb.com/v2.1/get-time-zone?key=SQ90W55WY9V6&format=json&by=position&lat='.$latitude.'&lng='.$longitude);
                $timezone = json_decode($timezonedb);

                $taken = substr($photo_data->created_at, 0, -1);

                $datetime = new DateTime($taken, new DateTimeZone($timezone->zoneName));
                $offset = $datetime->format('p');
                $offset = explode(':',$datetime->format('p'))[0];
                $offset = (string)((int)($offset));
                if(($offset == '') || $offset == '+'){ $offset = 0; }
                if($offset > 0){ $offset = '+' . $offset; }
                $datetime->setTimezone(new DateTimeZone('UTC'));

                echo $photo_data->urls->regular . '|' . $offset . '|' . $datetime->format('Y-m-d H:i:sP') . '|' . $latitude . '|' . $longitude . PHP_EOL;

                $location = $timezone->cityName . ', ' . $timezone->countryName;
                if($timezone->countryCode == 'US')
                {
                    $location = $timezone->cityName . ', ' . $timezone->regionName . ' ' . $timezone->countryCode;
                }

                $sunrise = new Sunrises();
                $sunrise->image_id = $photo_data->id;
                $sunrise->image_path = $photo_data->urls->regular;
                $sunrise->username = $photo_data->user->username;
                $sunrise->user_image = $photo_data->user->profile_image->small;
                $sunrise->user_profile_url = $photo_data->user->links->html;
                $sunrise->taken_at = $datetime->format('Y-m-d H:i:sP');
                $sunrise->offset = $offset;
                $sunrise->location = $location;
                $sunrise->latitude = $latitude;
                $sunrise->longitude = $longitude;
                $sunrise->timezone = $timezone->zoneName;
                $sunrise->points = $photo_data->views;
                $sunrise->source = 'unsplash';
                $sunrise->save();

            }
            else
            {
                $sunrise = Sunrises::where(['image_id' => $photo_data->id]);
                if($sunrise->exists())
                {
                    $sunrise->update(['points' => $photo_data->views]);
                    echo 'updated ' . $photo_data->id . ' with ' . $photo_data->views . PHP_EOL;
                }
            }
        }
    }
};

/*
https://api.unsplash.com/search/photos?query=sunrise&order_by=latest&client_id=h6uacJ-94jt_jS4fzJlfeLrjzrV37RxwZ_oA-xtbr9s
https://api.unsplash.com/photos/9G8K_nycv7s?query=sunrise&order_by=latest&client_id=h6uacJ-94jt_jS4fzJlfeLrjzrV37RxwZ_oA-xtbr9s
*/