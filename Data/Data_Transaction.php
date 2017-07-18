<?php

namespace AppConnector\Data;

use AppConnector\Entities\Transaction;
use AppConnector\Json\JsonSerializer;
use AppConnector\Log\Log;

/**
 *
 * @author  Adriaan Meijer
 * @version 1.0    - Initiele opzet
 *
 */
class Data_Transaction extends Data_Core
{
    const DATA_FILE = 'Data/data.transaction.txt';

    /**
     * Inserts 1 row containing a Credential into the data file
     *
     * @static
     *
     * @param Transaction $oTransaction
     *
     * @return bool
     * @throws \AppConnector\Exceptions\InvalidJsonException
     * @throws \Exception
     */
    public static function insert(Transaction $oTransaction)
    {
        fwrite(static::openFileToWrite(), JsonSerializer::serialize($oTransaction->toArray()) . "\r\n");
        Log::write('Data_Credential::Insert', 'INPUT', 'Row written on ' . $oTransaction->getTransactionId());

        return true;
    }

    /**
     * Updates 1 row containing a Credential based on the Public Key
     *
     * @static
     *
     * @param Transaction $oTransaction
     *
     * @return bool
     */
    public static function update(Transaction $oTransaction)
    {
        $rFile = static::openFileToRead();
        $aData = [];
        while (($sLine = fgets($rFile)) !== false) {
            $oData = JsonSerializer::deSerialize($sLine);
            if ($oData->transaction_id === $oTransaction->getTransactionId()) {
                $oData->status = $oTransaction->getStatus();
            }

            $aData[] = JsonSerializer::serialize($oData);
        }
        #Write empty line at file end
        $aData[] = null;

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . static::DATA_FILE, implode("\r\n", $aData));
        Log::write('Data_Credential::Update', 'INPUT', 'Row updated on ' . $oTransaction->getTransactionId());

        return true;
    }

    /**
     * Deletes 1 row containing a WebHook based on the Public Key
     * @static
     *
     * @param \AppConnector\Entities\Transaction $oTransaction
     *
     * @return bool
     */
    public static function delete(Transaction $oTransaction)
    {
        $rFile = static::openFileToRead();
        $aData = [];

        while (($sLine = fgets($rFile)) !== false) {
            $oObject = new Transaction(JsonSerializer::deSerialize($sLine));
            if ($oObject->getTransactionId() !== $oTransaction->getTransactionId()) {
                $sLine = str_replace(["\n", "\r"], '', $sLine);
                $sLine = trim($sLine);
                if (!empty($sLine)) {
                    $aData[] = $sLine;
                }
            }
        }
        #Write empty line at file end
        $aData[] = null;

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . static::DATA_FILE, implode("\r\n", $aData));
        Log::write('Data_Credential::Delete', 'INPUT', 'Row updated on ' . $oTransaction->getTransactionId());

        return true;
    }

    /**
     * @static
     *
     * @param string $sTransactionId
     *
     * @return \AppConnector\Entities\Transaction
     * @throws \AppConnector\Exceptions\InvalidTransactionId
     */
    public static function getOneByTransactionId($sTransactionId = '')
    {
        $rFile = static::openFileToRead();
        while (($sLine = fgets($rFile)) !== false) {
            $oObject = new Transaction(JsonSerializer::deSerialize($sLine));

            if ($oObject->getTransactionId() === $sTransactionId) {
                Log::write('Data_Credential::GetOneByPublicKey', 'INPUT', 'Row found for ' . $sTransactionId);
                return $oObject;
            }
        }
        throw new \AppConnector\Exceptions\InvalidTransactionId();
    }
}