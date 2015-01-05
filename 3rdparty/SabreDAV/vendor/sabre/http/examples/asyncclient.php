<?php

/**
 * This example demonstrates the ability for clients to work asynchronously.
 *
 * By default up to 10 requests will be executed in paralel. HTTP connections
 * are re-used and DNS is cached, all thanks to the power of curl.
 *
 * @copyright Copyright (C) 2009-2014 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */

use
    Sabre\HTTP\Request,
    Sabre\HTTP\Client;


// Find the autoloader
$paths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/vendor/autoload.php',

];

foreach($paths as $path) {
    if (file_exists($path)) {
        include $path;
        break;
    }
}

// This is the request we're repeating a 1000 times.
$request = new Request('GET', 'http://localhost/');
$client = new Client();

for($i = 0; $i < 1000; $i++) {

    echo "$i sending\n";
    $client->sendAsync(
        $request,

        // This is the 'success' callback
        function($response) use ($i) {
            echo "$i -> " . $response->getStatus() . "\n";
        },

        // This is the 'error' callback. It is called for general connection
        // problems (such as not being able to connect to a host, dns errors,
        // etc.) and also cases where a response was returned, but it had a
        // status code of 400 or higher.
        function($error) use ($i) {

            if ($error['status'] === Client::STATUS_CURLERROR) {
                // Curl errors
                echo "$i -> curl error: " . $error['curl_errmsg'] . "\n";
            } else {
                // HTTP errors
                echo "$i -> " . $error['response']->getStatus() . "\n";
            }
        }
    );
}

// After everything is done, we call 'wait'. This causes the client to wait for
// all outstanding http requests to complete.
$client->wait();
