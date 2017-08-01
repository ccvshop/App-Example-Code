<?

namespace AppConnector\Data;

use AppConnector\Entities\Credential;
use AppConnector\Exceptions\InvalidCredentialException;
use AppConnector\Json\JsonSerializer;
use AppConnector\Log\Log;

/**
 * Class Data_Credential_JSON
 * Handles all data manipulations for Credentials
 *
 * @package AppConnector\Data
 * @author  Adriaan Meijer
 * @date    2014-10-13
 * @version 1.0    - First draft
 *          1.1    - Added logging
 *            1.2       - Nick Postma: Added database credential storage
 *            1.3       - Nick Postma: Implemented strategy pattern for Data_Credential (Classe renamed to Data_Credential_JSON) and now uses oCredential->ToArray()
 *
 */
class Data_Credential_JSON extends Data_Core implements IData_Credential
{
    const DATA_FILE = 'Data/data.credential.txt';

    /**
     * Inserts 1 row containing a Credential into the data file
     *
     * @static
     *
     * @param Credential $oCredential
     *
     * @return bool
     * @throws \AppConnector\Exceptions\InvalidJsonException
     * @throws \Exception
     */
    public static function insert(Credential $oCredential)
    {

        #@todo: check up dubbele public keys
        fwrite(static::openFileToWrite(), JsonSerializer::serialize($oCredential->toArray()) . "\r\n");
        Log::write('Data_Credential::Insert', 'INPUT', 'Row written on ' . $oCredential->getApiPublic());

        return true;
    }

    /**
     * Updates 1 row containing a Credential based on the Public Key
     *
     * @static
     *
     * @param Credential $oCredential
     *
     * @return bool
     */
    public static function update(Credential $oCredential)
    {
        $rFile = static::openFileToRead();
        $aData = [];
        while (($sLine = fgets($rFile)) !== false) {
            $oData = JsonSerializer::deSerialize($sLine);
            if ($oData->api_public === $oCredential->getApiPublic()) {
                $oData->customer_id = $oCredential->getCustomerId();
            }

            $aData[] = JsonSerializer::serialize($oData);
        }
        #Write empty line at file end
        $aData[] = null;

        file_put_contents(static::DATA_FILE, implode("\r\n", $aData));
        Log::write('Data_Credential::Update', 'INPUT', 'Row updated on ' . $oCredential->getApiPublic());

        return true;
    }

    /**
     * Deletes 1 row containing a WebHook based on the Public Key
     *
     * @static
     *
     * @param Credential $oCredential
     *
     * @return bool
     * @throws \Exception
     */
    public static function delete(Credential $oCredential)
    {
        $rFile = static::openFileToRead();
        $aData = [];

        while (($sLine = fgets($rFile)) !== false) {
            $oObject = new Credential(JsonSerializer::deSerialize($sLine));
            if ($oObject->getApiPublic() !== $oCredential->getApiPublic()) {
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
        Log::write('Data_Credential::Delete', 'INPUT', 'Row updated on ' . $oCredential->getApiPublic());

        return true;
    }

    /**
     * Return one Credential based on the Public Key
     *
     * @static
     *
     * @param string $sApiPublic
     *
     * @return Credential
     * @throws InvalidCredentialException
     * @throws \Exception
     */
    public static function getOneByPublicKey($sApiPublic = '')
    {
        $rFile = static::openFileToRead();
        while (($sLine = fgets($rFile)) !== false) {
            $oObject = new Credential(JsonSerializer::deSerialize($sLine));
            if ($oObject->getApiPublic() === $sApiPublic) {
                Log::write('Data_Credential::GetOneByPublicKey', 'INPUT', 'Row found for ' . $sApiPublic);
                return $oObject;
            }
        }
        throw new InvalidCredentialException();
    }

    /**
     * Return all Credentials
     *
     * @static
     *
     * @return Credential[]
     * @throws InvalidCredentialException
     * @throws \Exception
     */
    public static function getAll()
    {
        $rFile        = static::openFileToRead();
        $aCredentials = [];
        while (($sLine = fgets($rFile)) !== false) {
            $aCredentials[] = new Credential(JsonSerializer::deSerialize($sLine));
        }
        if (!empty($aCredentials)) {
            return $aCredentials;
        }
        throw new InvalidCredentialException();
    }
}