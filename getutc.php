<?php

/*
serverless bref:local -f getutc --data='{"queryStringParameters": {"latitude":"24.555059","longitude":"-80.1918"} }'
*/

require 'models/database.php';
require 'models/offsets.php';

use Models\Database;
use Models\Offsets;

new Database();

return function ($event)
{

    $latitude = (float)$event['queryStringParameters']['latitude'];
    $longitude = (float)$event['queryStringParameters']['longitude'];

    $latitude = round($latitude, 2);
    $longitude = round($longitude, 2);

    $offset = Offsets::where('latitude', $latitude)->where('longitude', $longitude)->value('offset');
    if(!$offset)
    {
        $time = file_get_contents('https://vip.timezonedb.com/v2.1/get-time-zone?key=SQ90W55WY9V6&format=json&by=position&lat='.$latitude.'&lng='.$longitude);
        $time = json_decode($time);

        $timezone = new DateTimeZone($time->zoneName);
        $time = new DateTime('now', $timezone);
        $offset = round($timezone->getOffset($time)/3600);
        if($offset > 0){ $offset = '+'.$offset; }

        $offsets = new Offsets();
        $offsets->latitude = $latitude;
        $offsets->longitude = $longitude;
        $offsets->offset = $offset;
        $offsets->save();
    }

    return json_encode(['offset'=>$offset]);
};