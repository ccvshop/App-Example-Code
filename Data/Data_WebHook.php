<?

namespace AppConnector\Data;

use AppConnector\Entities\WebHook;
use AppConnector\Json\JsonSerializer;
use AppConnector\Log\Log;

/**
 * Class Data_WebHook
 * Handles all data manipulations for WebHooks
 *
 * @package AppConnector\Data
 * @author  Adriaan Meijer
 * @date    2014-10-13
 * @version 1.0    - First draft
 *          1.1    - Added logging
 */
class Data_WebHook extends Data_Core
{
    const DATA_FILE = 'Data/data.webhook.txt';

    /**
     * Inserts 1 row containing a WebHook into the data file
     *
     * @static
     *
     * @param WebHook $oWebHook
     *
     * @return bool
     */
    public static function insert(WebHook $oWebHook)
    {

        $oData              = new \stdClass();
        $oData->id          = static::getLastId() + 1;
        $oData->customer_id = $oWebHook->getCustomerId();
        $oData->event       = $oWebHook->getEvent();
        $oData->address     = $oWebHook->getAddress();
        $oData->key         = $oWebHook->getKey();
        fwrite(static::openFileToWrite(), JsonSerializer::serialize($oData) . "\r\n");
        Log::write('Data_WebHook::Insert', 'INPUT', 'Row written on ' . $oData->id);
        return true;
    }

    /**
     * Deletes 1 row containing a WebHook based on ID
     *
     * @static
     *
     * @param WebHook $oWebHook
     */
    public static function delete(WebHook $oWebHook)
    {
        $rFile = static::openFileToRead();
        $aData = [];
        while (($sLine = fgets($rFile)) !== false) {
            $oData = new WebHook(JsonSerializer::deSerialize($sLine));
            if ($oData->getId() !== $oWebHook->getId()) {
                $sLine = str_replace(["\n", "\r"], '', $sLine);
                $sLine = trim($sLine);
                if (!empty($sLine)) {
                    $aData[] = $sLine;
                }
            }
        }
        #Write empty line at file end
        $aData[] = null;

        file_put_contents(static::DATA_FILE, implode("\r\n", $aData));
        Log::write('Data_WebHook::Delete', 'INPUT', 'Row deleted on ' . $oWebHook->getId());
    }

    /**
     * Returns all WebHook associated to a CustomerId
     *
     * @static
     *
     * @param integer $iCustomerId
     *
     * @return array
     */
    public static function getAllByCustomerId($iCustomerId = 0)
    {
        $rFile   = static::openFileToRead();
        $aResult = [];
        while (($sLine = fgets($rFile)) !== false) {
            $oWebHook = new WebHook(JsonSerializer::deSerialize($sLine));
            if ($oWebHook->getCustomerId() === $iCustomerId) {
                $aResult[] = $oWebHook;
            }
        }

        Log::write('Data_WebHook::GetAllByCustomerId', 'INPUT', count($aResult) . ' Rows found for ' . $iCustomerId);

        return $aResult;
    }

    /**
     * Returns the last used ID in the data file.
     *
     * @static
     * @return int
     * @throws \AppConnector\Exceptions\InvalidJsonException
     */
    private static function getLastId()
    {
        $rFile = static::openFileToRead();
        $iId   = 0;
        while (($sLine = fgets($rFile)) !== false) {
            $oWebHook = new WebHook(JsonSerializer::deSerialize($sLine));
            if ($oWebHook->getId() > $iId) {
                $iId = $oWebHook->getId();
            }
        }

        return $iId;
    }
}