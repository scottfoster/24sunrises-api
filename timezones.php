<?php

/*

serverless invoke --aws-profile 24sunrises -f flickr
serverless deploy --aws-profile 24sunrises
serverless bref:local -f timezones

*/

require __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('Etc/UTC');
require 'models/database.php';
require 'models/sunrises.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Carbon\Carbon;
use Models\Database;
use Models\Sunrises;

new Database();

return function ($event) {

$now = time();
$i = 0;
$data = [];

$base_image_path = 'https://24sunrises-data.s3.amazonaws.com/';

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
    $time = file_get_contents('https://vip.timezonedb.com/v2.1/get-time-zone?key=SQ90W55WY9V6&format=json&by=position&lat='.$timezone['lat'].'&lng='.$timezone['lng']);
    $time = json_decode($time);

    $sun = date_sun_info($time->timestamp, $timezone['lat'], $timezone['lng']);
    $sunrise = gmdate('U', $sun['sunrise']);

    $timezone['local_time'] = $time->formatted;
    $timezone['sunrise_ago'] = $now - $sunrise;
    $timezone['sunrise_friendly'] = gmdate('H:m:s\Z', $sun['sunrise']);

    $sort[$i++] = $timezone['sunrise_ago'];
}

$currentkey = 0;
$currentvalue = 999999;
foreach($sort as $k => $v)
{
    if($v < 0){ continue; }
    if($v < $currentvalue)
    {
        $currentkey = $k;
        $currentvalue = $v;
    }
}

$newsort = array_merge(
    range($currentkey, count($sort)),
    range(0, $currentkey-1)
);

$tz = orderarray($tz, $newsort);
$tz = array_values($tz);

foreach($tz as $k => $timezone)
{

    $sunrises = Sunrises::where('offset', $timezone['offset'])->orderBy('taken_at', 'desc')->take(10)->get()->toArray();

    $sunrise_data = [];
    foreach($sunrises as $k2 => $sunrise)
    {

        $time = Carbon::parse($sunrise['taken_at'])->diffForHumans(['short' => true]);
        $sunrise_data[] = [
            "key" => $k2,
            "location" => $sunrise['location'],
            "image" => $sunrise['image_path'],
            "time" => $time,
            "username" => $sunrise['username'],
            "user_image" => $sunrise['user_image'],
            "points" => shorten($sunrise['points'])
        ];
    }

    $data[$k] =
        [
            'title' => $timezone['cities'] . ' (' . $timezone['name'] . ')',
            'data' => $sunrise_data,
            'size' => 'normal'
        ];

    if($k == 1)
    {
        $data[$k]['subheading'] = 'Past Sunrises';
    }

    if($k == 0)
    {
        $data[$k]['size'] = 'large';
        $data[$k]['subheading'] = 'Good Morning ' . $timezone['cities'] . ' (' . $timezone['name'] . ')!';
    }

    if($k > 4){ $data[$k]['size'] = 'small'; }

}

    $client = new Aws\S3\S3Client([
        'region'  => 'us-east-1',
        'version' => 'latest',
        'credentials' => [
            'key'    => "AKIA3PWTTOA6HJW42676",
            'secret' => "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",
        ],
    ]);

    $result = $client->putObject([
        'Bucket' => '24sunrises-data',
        'Key' => 'sunrises-new.json',
        'Body' => json_encode($data),
        'ContentType' => 'application/json'
    ]);

    echo 'timezones done - ' . $result['@metadata']['statusCode'];
    return 'timezones done - ' . $result['@metadata']['statusCode'];
};


function orderarray($arrayToOrder, $keys) {
    $ordered = [];
    foreach ($keys as $key) {
        if (isset($arrayToOrder[$key])) {
             $ordered[$key] = $arrayToOrder[$key];
        }
    }
    return $ordered;
}

function shorten($number){
    $suffix = ["", "k", "m", "b"];
    $precision = 1;
    for($i = 0; $i < count($suffix); $i++){
        $divide = $number / pow(1000, $i);
        if($divide < 1000){
            return round($divide, $precision).$suffix[$i];
            break;
        }
    }
}