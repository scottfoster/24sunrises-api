<?php

/*
serverless bref:local -f getutc --data='{"queryStringParameters": {"latitude":"24.555059","longitude":"-80.1918"} }'
*/

require 'models/database.php';
require 'models/images.php';

use Models\Database;
use Models\Images;

new Database();

return function ($event)
{

    $image = new Images();
    $image->image = json_decode($event['body'])->image;
    $image->save();

    echo 'done';

    return 'done';
    // return json_encode(['offset'=>$offset]);
};