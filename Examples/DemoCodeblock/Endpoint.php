<?php

namespace AppConnector;

use AppConnector\Data\Data_Credential;
use AppConnector\Exceptions\InvalidHashException;
use AppConnector\Http\Hash;
use AppConnector\Log\Log;

try {
    require_once('../../Config.php');
    require_once('../../AppConnector.php');

    $aRequestHeaders = apache_request_headers();
    $sIncomingData   = @file_get_contents('php://input');

    Log::writeStartCall(__FILE__);
    Log::write('Endpoint', 'INPUT_BODY', $sIncomingData);

    #Validate if the data we received is correct and authenticated.
    $sApiPublic  = $aRequestHeaders[\AppConnector\Http\Hash::Header_Public];
    $oCredential = Data_Credential::getOneByPublicKey($sApiPublic);

    #Validate if the data we received is correct and authenticated.
    $oIncomingHash = new \AppConnector\Http\Hash($oCredential->getApiSecret());
    $bValid        = $oIncomingHash->addData(Config::APP_URI . $_SERVER['REQUEST_URI'])->addData($sIncomingData)->isValid($aRequestHeaders[Hash::Header_Hash]);

    if ($bValid === false) {
        throw new InvalidHashException();
    }

    $oObject                  = json_decode($sIncomingData);
    $oObject                  = $oObject->payload;

    $oResponse                 = new \stdClass();
    $oResponse->view           = 'onload';
    $oResponse->data           = [];
    $oResponse->data['text'] = '';
    $oResponse->data['enabled'] = '';
    $oResponse->data['input_text'] = '';
    $oResponse->data['input_textarea'] = '';
    $oResponse->data['submit'] = '';
    $oResponse->data['table'] = [
        ['header 1', 'header 2', 'header 3', 'header 4'],
        ['row 1', 'row 2', 'row 3', 'row 4'],
        ['row 1', 'row 2', 'row 3', 'row 4'],
         ['row 1', 'row 2', 'row 3', 'row 4'],
    ];
    $oResponse->data['info'] = "Multi line <br /> info message ";
    $oResponse->data['error'] = 'Error message';

    $sResponse = json_encode($oResponse);
    Log::write('Endpoint', 'OUTPUT_BODY', $sResponse);

    #Generate output hash, so the webshop can verify it's integrity and authenticate it.
    $oHash = new \AppConnector\Http\Hash($oCredential->getApiSecret());
    $sHash = $oHash->addData(Config::APP_URI . $_SERVER['REQUEST_URI'])->addData($sResponse)->hash();

    header('HTTP/1.1 200 OK', true, 200);
    header('x-hash: ' . $sHash);

    Log::writeEndCall(__FILE__);

    #Returns data to the webshop.
    echo $sResponse;
    die();
} catch (\Exception $oEx) {

    Log::write('Endpoint', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
    Log::writeEndCall(__FILE__);

    header('HTTP/1.1 500 Internal Server Error', true, 500);
    echo $oEx->getMessage();
    die();
}
