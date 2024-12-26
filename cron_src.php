<?php

$url = 'http://193.233.114.253:9001/daily-update';

$response = file_get_contents($url);

if ($response === false) {
    echo 'Error: Unable to fetch URL';
} else {
    echo $response;
}