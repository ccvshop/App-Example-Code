<?php
/**
 * Step 1. Handshake Endpoint
 * The Handshake is the first step in installing the application. The webshop send the initial credentials to the Handshake Endpoint.
 * When the Handshake is successful and this page returns a HTTP 200 OK, the user will be forwarded to the Install Endpoint (step 2).
 */

namespace AppConnector;

use AppConnector\Log\Log;

try {
    require_once('AppConnector.php');

    Log::writeStartCall(__FILE__);
    Log::write('Handshake', 'INPUT', @file_get_contents('php://input'));

    $oAppConnector = new AppConnector();
    $oAppConnector->processCredentials();

    Log::write('Handshake', 'OUTPUT', 'HTTP/1.1 200 OK');
    Log::writeEndCall(__FILE__);

    header('HTTP/1.1 200 OK', true, 200);
    die('OK');
} catch (\Exception $oEx) {

    Log::write('Handshake', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
    Log::writeEndCall(__FILE__);

    header('HTTP/1.1 500 Internal Server Error', true, 500);
    echo $oEx->getMessage();
    die();
}

