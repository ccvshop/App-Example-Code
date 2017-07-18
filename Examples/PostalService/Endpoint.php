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

    $oObject = json_decode($sIncomingData);

    #Check if the merchant submitted the label creation form.
    #We could of course check if this order has been submitted in the past and directly show the label.
    if ($oObject instanceof \stdClass && isset($oObject->form_data) && !empty($oObject->form_data->submit)) {
        $oResponse = new \stdClass();
        #Present 'Save As' Dialog for the merchant.
        if ($oObject->form_data->direct_download == '1') {
            $oResponse->view                       = 'success-direct-download';
            $oResponse->data                       = [];
            $oResponse->data['package_label_1']    = 'https://demo.securearea.eu/Examples/PostalService/Download.php?file=specimen_label.png';
            $oResponse->data['attachment_label_1'] = 'https://demo.securearea.eu/Examples/PostalService/Download.php?file=specimen_label.png';
            $oResponse->data['attachment_label_1'] = str_replace('https://demo.securearea.eu', Config::APP_URI, $oResponse->data['attachment_label_1']);
        } else {
            $oResponse->view                    = 'success';
            $oResponse->data                    = [];
            $oResponse->data['package_label_1'] = 'https://demo.securearea.eu/Examples/PostalService/Download.php?file=specimen_label.png';
        }

        $oResponse->data['package_label_1'] = str_replace('https://demo.securearea.eu', Config::APP_URI, $oResponse->data['package_label_1']);
    } else {
        #Show inital start form.
        $oResponse       = new \stdClass();
        $oResponse->view = 'onload';
    }
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
