<?php

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

date_default_timezone_set('Etc/UTC');

require __DIR__ . '/vendor/autoload.php';

return function ($event) {


$faker = Faker\Factory::create();
$faker->addProvider(new Faker\Provider\en_US\Address($faker));

$now = time();
$i = 0;
$data = [];

$tz[] = ['name' => 'UTC-11', 'cities' => 'American Samoa', 'Jarvis Island', 'lat' => 0, 'lng' => '-178.9'];
$tz[] = ['name' => 'UTC-10', 'cities' => 'Cook Islands, French Polynesia', 'lat' => 0, 'lng' => '-142.5'];
$tz[] = ['name' => 'UTC-9', 'cities' => 'Anchorage, Gambier Islands', 'lat' => 0, 'lng' => '-127.5'];
$tz[] = ['name' => 'UTC-8', 'cities' => 'Los Angeles, Vancouver, Tijuana', 'lat' => 0, 'lng' => '-112.5'];
$tz[] = ['name' => 'UTC-7', 'cities' => 'Denver, Edmonton, Ciudad Juárez', 'lat' => 0, 'lng' => '-97.5'];
$tz[] = ['name' => 'UTC-6', 'cities' => 'Mexico City, Chicago, Guatemala City', 'lat' => 0, 'lng' => '-82.5'];
$tz[] = ['name' => 'UTC-5', 'cities' => 'New York, Toronto, Havana', 'lat' => 0, 'lng' => '-67.5'];
$tz[] = ['name' => 'UTC-4', 'cities' => 'Santiago, Santo Domingo, Manaus', 'lat' => 0, 'lng' => '-52.5'];
$tz[] = ['name' => 'UTC-3', 'cities' => 'São Paulo, Buenos Aires, Montevideo', 'lat' => 0, 'lng' => '-37.5'];
$tz[] = ['name' => 'UTC-2', 'cities' => 'South Georgia, Fernando de Noronha', 'lat' => 0, 'lng' => '-22.5'];
$tz[] = ['name' => 'UTC-1', 'cities' => 'Cape Verde, Denmark, Greenland', 'lat' => 0, 'lng' => '-7.5'];
$tz[] = ['name' => 'UTC', 'cities' => 'London, Dublin, Lisbon', 'lat' => 0, 'lng' => '7.5'];
$tz[] = ['name' => 'UTC+1', 'cities' => 'Berlin, Rome, Paris', 'lat' => 0, 'lng' => '22.5'];
$tz[] = ['name' => 'UTC+2', 'cities' => 'Cairo, Johannesburg, Khartoum', 'lat' => 0, 'lng' => '37.5'];
$tz[] = ['name' => 'UTC+3', 'cities' => 'Moscow, Istanbul, Riyadh', 'lat' => 0, 'lng' => '52.5'];
$tz[] = ['name' => 'UTC+4', 'cities' => 'Dubai, Baku, Tbilisi', 'lat' => 0, 'lng' => '67.5'];
$tz[] = ['name' => 'UTC+5', 'cities' => 'Karachi, Tashkent, Yekaterinburg', 'lat' => 0, 'lng' => '82.5'];
$tz[] = ['name' => 'UTC+6', 'cities' => 'Dhaka, Almaty, Omsk', 'lat' => 0, 'lng' => '97.5'];
$tz[] = ['name' => 'UTC+7', 'cities' => 'Jakarta, Surabaya, Medan', 'lat' => 0, 'lng' => '112.5'];
$tz[] = ['name' => 'UTC+8', 'cities' => 'Shanghai, Taipei, Kuala Lumpur', 'lat' => 0, 'lng' => '127.5'];
$tz[] = ['name' => 'UTC+9', 'cities' => 'Tokyo, Seoul, Pyongyang', 'lat' => 0, 'lng' => '142.5'];
$tz[] = ['name' => 'UTC+10', 'cities' => 'Sydney, Port Moresby, Vladivostok', 'lat' => 0, 'lng' => '157.5'];
$tz[] = ['name' => 'UTC+11', 'cities' => 'Nouméa', 'lat' => 0, 'lng' => '172.5'];
$tz[] = ['name' => 'UTC+12', 'cities' => 'Auckland, Suva', 'lat' => 0, 'lng' => '180'];

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
    $data[$k] =
        [
            'title' => $timezone['cities'] . ' (' . $timezone['name'] . ')',
            'data' => [
                [
                    "key" => "1",
                    "location" => $faker->city() . ", " . $faker->stateAbbr(),
                    "image" => "https://picsum.photos/seed/".rand(1,999999)."/2000/2000",
                    "time" => date('c', $faker->dateTimeBetween('-1 week', '-5 minutes')->getTimestamp()),
                    "author" => '@'.$faker->word(),
                ],
                [
                    "key" => "2",
                    "location" => $faker->city() . ", " . $faker->stateAbbr(),
                    "image" => "https://picsum.photos/seed/".rand(1,999999)."/2000/2000",
                    "time" => date('c', $faker->dateTimeBetween('-1 week', '-5 minutes')->getTimestamp()),
                    "author" => '@'.$faker->word(),
                ],
                [
                    "key" => "3",
                    "location" => $faker->city() . ", " . $faker->stateAbbr(),
                    "image" => "https://picsum.photos/seed/".rand(1,999999)."/2000/2000",
                    "time" => date('c', $faker->dateTimeBetween('-1 week', '-5 minutes')->getTimestamp()),
                    "author" => '@'.$faker->word(),
                ],
                [
                    "key" => "4",
                    "location" => $faker->city() . ", " . $faker->stateAbbr(),
                    "image" => "https://picsum.photos/seed/".rand(1,999999)."/2000/2000",
                    "time" => date('c', $faker->dateTimeBetween('-1 week', '-5 minutes')->getTimestamp()),
                    "author" => '@'.$faker->word(),
                ],

            ],
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
	    'key'    => "AKIA2CS4X5BZYHTBZDUE",
	    'secret' => "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",
    ],
]);

$result = $client->putObject([
    'Bucket' => 'imfoster.com',
    'Key' => '24sunrises-data.json',
    'Body' => json_encode($data),
    'ContentType' => 'application/json'
]);

    return 'timezones done';
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