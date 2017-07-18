<?php

/**
 * Step 3. UnInstall Endpoint
 * Whenever the user uninstalls the app in his webshop, the UnInstall Endpoint will be called.
 * This will give you the option to process the uninstall.
 */

namespace AppConnector;

use AppConnector\Log\Log;

try {
    require_once('AppConnector.php');

    Log::writeStartCall(__FILE__);
    Log::write('UnInstall', 'INPUT', @file_get_contents('php://input'));

    $oAppConnector = new AppConnector();
    $oAppConnector->unInstall();

    Log::write('Handshake', 'OUTPUT', 'HTTP/1.1 200 OK');
    Log::writeEndCall(__FILE__);

    header('HTTP/1.1 200 OK', true, 200);
    die('OK');
} catch (\Exception $oEx) {
    Log::write('UnInstall', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
    Log::writeEndCall(__FILE__);

    header('HTTP/1.1 500 Internal Server Error', true, 500);
    echo $oEx->getMessage();
    die();
}
