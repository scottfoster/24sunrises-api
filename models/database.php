<?php
namespace Models;
use Illuminate\Database\Capsule\Manager as Capsule;

class Database {
    public function __construct()
    {
        $capsule = new Capsule;
        $capsule->addConnection([
            "driver" => "mysql",
            "host" =>"sunrises.csgpwdvw158j.us-east-1.rds.amazonaws.com",
            "database" => "sunrises",
            "username" => "sunrises",
            "password" => "lgoQunJ54VORg7ZHBDPq",
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);
        $capsule->bootEloquent();
    }
}