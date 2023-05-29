<?php

/*
serverless bref:local -f getutc --data='{"queryStringParameters": {"latitude":"24.555059","longitude":"-80.1918"} }'
*/

require 'models/database.php';
require 'models/uploads.php';

use Models\Database;
use Models\Uploads;

new Database();

return function ($event)
{

    $email = filter_var($event['queryStringParameters']['email'], FILTER_SANITIZE_EMAIL);
    $image = filter_var($event['queryStringParameters']['image'], FILTER_SANITIZE_STRING);

    $upload = new Uploads();
    $upload->email = $email;
    $upload->image = $image;

    $upload->save();

    return json_encode(['upload'=>true]);
};