<?php

namespace AppConnector\Examples\PSP;

use AppConnector\Data\Data_Credential;
use AppConnector\Entities\Credential;
use AppConnector\Exceptions\InvalidTransactionId;

class TransactionFactory
{
    /** @var Credential */
    protected $credential = null;

    public function __construct()
    {
    }

    public function getStatus($sTransactionId = '')
    {
        $this->verifyHash();

        $oTransaction = \AppConnector\Data\Data_Transaction::getOneByTransactionId($sTransactionId);
        if ($oTransaction->getCustomerId() !== $this->credential->getCustomerId()) {
            throw new InvalidTransactionId();
        }

        $sResponse = \AppConnector\Json\JsonSerializer::serialize($oTransaction->toStdClass());

        $oHash = new \AppConnector\Http\Hash($this->credential->getApiSecret());
        $sHash = $oHash->addData(\AppConnector\Config::APP_URI . $_SERVER['REQUEST_URI'])->addData($sResponse)->hash();

        header('HTTP/1.1 200 OK', true, 200);
        header('x-hash: ' . $sHash);

        return $sResponse;
    }

    /**
     *
     * @return string
     * @throws \AppConnector\Exceptions\InvalidHashException
     */
    public function create()
    {
        $sIncomingData = @file_get_contents('php://input');
        \AppConnector\Log\Log::write('TransactionFactory', 'INPUT_BODY', $sIncomingData);
        $this->verifyHash($sIncomingData);

        $oPostedData  = \AppConnector\Json\JsonSerializer::deSerialize(@file_get_contents('php://input'));
        $oTransaction = new \AppConnector\Entities\Transaction($oPostedData);
        $this->doCreditCheck($oTransaction);

        $oTransaction->setCustomerId($this->credential->getCustomerId());
        $oTransaction->setPayUrl(\AppConnector\Config::APP_URI . '/Examples/PSP/PaymentSimulator.php?transaction_id=' . $oTransaction->getTransactionId());

        \AppConnector\Data\Data_Transaction::insert($oTransaction);

        $sResponse = \AppConnector\Json\JsonSerializer::serialize($oTransaction->toStdClass());

        \AppConnector\Log\Log::write('TransactionFactory', 'OUTPUT_BODY', $sResponse);
        $oHash = new \AppConnector\Http\Hash($this->credential->getApiSecret());
        $sHash = $oHash->addData(\AppConnector\Config::APP_URI . $_SERVER['REQUEST_URI'])->addData($sResponse)->hash();

        header('HTTP/1.1 200 OK', true, 200);
        header('x-hash: ' . $sHash);

        return $sResponse;
    }

    /**
     * @param string $sIncomingData
     *
     * @throws \AppConnector\Exceptions\InvalidHashException
     */
    protected function verifyHash($sIncomingData = '')
    {
        $aRequestHeaders  = apache_request_headers();
        $sApiPublic       = $aRequestHeaders[\AppConnector\Http\Hash::Header_Public];
        $this->credential = Data_Credential::getOneByPublicKey($sApiPublic);

        #Validate if the data we received is correct and authenticated.
        $oIncomingHash = new \AppConnector\Http\Hash($this->credential->getApiSecret());

        $oIncomingHash->addData(\AppConnector\Config::APP_URI . $_SERVER['REQUEST_URI']);
        if (!empty($sIncomingData)) {
            $oIncomingHash->addData($sIncomingData);
        }

        $bValid = $oIncomingHash->isValid($aRequestHeaders[\AppConnector\Http\Hash::Header_Hash]);

        if ($bValid === false) {
            throw new \AppConnector\Exceptions\InvalidHashException();
        }
    }

    /**
     * @param \AppConnector\Entities\Transaction $oTransaction
     */
    protected function doCreditCheck(\AppConnector\Entities\Transaction &$oTransaction)
    {
        switch ($oTransaction->getMethod()) {
            case 'afterpay':
                \AppConnector\Log\Log::write('TransactionFactory', 'DO_CREDIT_CHECK', 'Age is ' . $oTransaction->getAge());
                if ($oTransaction->getAge() >= 18) {
                    $oTransaction->setStatus('SUCCESS');
                } else {
                    $oTransaction->setStatus('FAILED')->setError('Consumer does not meet the age requirement.');
                }
                break;
            default:
                break;
        }

        return;
    }
}