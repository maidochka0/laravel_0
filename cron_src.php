<?php

$url = 'http://localhost:9001/daily-update';

$response = file_get_contents($url);

if ($response === false) {
    'Error: Unable to fetch URL';
} else {
    echo $response;
}