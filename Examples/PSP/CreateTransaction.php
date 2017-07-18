<?php
/**
 *
 * @author  Adriaan Meijer
 * @version 1.0    - Initiele opzet
 *
 */
try {

    require_once('../../Config.php');
    require_once('../../Data/Data_Core.php');
    require_once('../../Data/Data_Credential.php');
    require_once('../../Data/Data_WebHook.php');
    require_once('../../Data/Data_Transaction.php');
    require_once('../../Entities/Credential.php');
    require_once('../../Entities/WebHook.php');
    require_once('../../Entities/Transaction.php');
    require_once('../../Entities/Transaction/Address.php');
    require_once('../../Examples/PSP/TransactionFactory.php');
    require_once('../../Exceptions/InvalidApiResponse.php');
    require_once('../../Exceptions/InvalidCredentialException.php');
    require_once('../../Exceptions/InvalidHashException.php');
    require_once('../../Exceptions/InvalidJsonException.php');
    require_once('../../Exceptions/InvalidTransactionId.php');
    require_once('../../Json/JsonSerializer.php');
    require_once('../../Http/WebRequest.php');
    require_once('../../Http/Hash.php');
    require_once('../../Log/Log.php');

    \AppConnector\Log\Log::writeStartCall(__FILE__);
    $oTransactionFactory = new \AppConnector\Examples\PSP\TransactionFactory();
    $sResponse           = $oTransactionFactory->create();

    echo $sResponse;
    \AppConnector\Log\Log::writeEndCall(__FILE__);

    die();
} catch (\Exception $oEx) {

    \AppConnector\Log\Log::write('Endpoint', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
    \AppConnector\Log\Log::writeEndCall(__FILE__);

    header('HTTP/1.1 500 Internal Server Error', true, 500);
    $oOutput         = new \stdClass();
    $oOutput->status = 'FAILED';
    $oOutput->error  = $oEx->getMessage();

    $sResponse = \AppConnector\Json\JsonSerializer::serialize($oOutput);
    $oHash     = new \AppConnector\Http\Hash();
    $sHash     = $oHash->addData(\AppConnector\Config::APP_URI . $_SERVER['REQUEST_URI'])->addData($sResponse)->hash();

    echo $sResponse;
    die();
}
