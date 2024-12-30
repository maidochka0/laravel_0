<?php

define('CRON_TOKEN', '');

$date = date('Y-m-d');
$token = CRON_TOKEN;
$url = "http://193.233.114.253:9001/daily-update/?dateFrom=$date&key=$token";

$response = file_get_contents($url);

if ($response === false) {
    echo 'Error: Unable to fetch URL';
} else {
    echo $response;
}