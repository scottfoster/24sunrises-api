<?php

require __DIR__ . '/vendor/autoload.php';

require 'models/database.php';
require 'models/sunrises.php';

use Models\Database;
use Models\Sunrises;

new Database();

$empties = Sunrises::where(['user_profile_url' => NULL, 'source' => 'unsplash'])->get()->toArray();
foreach($empties as $empty)
{
    $key = 'h6uacJ-94jt_jS4fzJlfeLrjzrV37RxwZ_oA-xtbr9s';
    $client = new \GuzzleHttp\Client();

    $response = $client->request('GET', 'https://api.unsplash.com/photos/'.$empty['image_id'].'?client_id=' . $key);
    $photo_data = json_decode($response->getBody());

    $sunrise = Sunrises::where(['image_id' => $empty['image_id']]);
    $sunrise->update([
        'user_image' => $photo_data->user->profile_image->small,
        'user_profile_url' => $photo_data->user->links->html
    ]);

    echo 'updated ' . $empty['image_id'] . PHP_EOL;
}