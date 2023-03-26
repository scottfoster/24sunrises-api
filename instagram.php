<?php

require __DIR__ . '/vendor/autoload.php';
use Phpfastcache\Helper\Psr16Adapter;

return function ($event) {

    $username = 'sc.ott4410';
    $password = 'sc.ott4410sc.ott4410';
    $instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), $username, $password, new Psr16Adapter('Files'));
    $instagram->login();

    $medias = $instagram->getCurrentTopMediasByTagName('sunrise');

    foreach($medias as $media)
    {
        echo 'media';
        echo '<pre>';
        print_r($media);
        echo '</pre>';

        echo '<pre>';
        print_r($instagram->getMediaByCode($media->getShortCode()));
        echo '</pre>';

    }

    return 'moo';

};