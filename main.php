<?php
require "RtmpClient.class.php";
require "debug.php";
$client = new RtmpClient();
$client->connect("localhost","myApp");
$result = $client->call("myMethod");
var_dump($result);

