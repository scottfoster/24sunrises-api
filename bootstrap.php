<?php

require "vendor/autoload.php";
use Illuminate\Database\Capsule\Manager as Capsule;
$capsule = new Capsule;
$capsule->addConnection([
   "driver" => "mysql",
   "host" =>"sunrises.csgpwdvw158j.us-east-1.rds.amazonaws.com",
   "database" => "sunrises",
   "username" => "sunrises",
   "password" => "lgoQunJ54VORg7ZHBDPq"
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();