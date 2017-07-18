<?

namespace AppConnector\Data;

use AppConnector\Entities\Credential;
use AppConnector\Exceptions\InvalidCredentialException;
use AppConnector\Log\Log;
use AppConnector\Sql\Connection;

/**
 * Class Data_Credential_SQL
 * Handles all data manipulations for Credentials
 *
 * @package AppConnector\Data
 * @author  Nick Postma
 * @date    2014-10-13
 * @version 1.0        - Nick Postma: First draft
 *            1.1        - Nick Postma: Added database credential storage
 *            1.2        - Nick Postma: Implemented strategy pattern for Data_Credential and now uses oCredential->ToArray()
 *
 */
class Data_Credential_SQL extends Data_Core implements IData_Credential
{
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
        $oSqlConnection = Connection::make();
        $iInsertId      = $oSqlConnection->insert('app_credential', $oCredential->toArray());
        Log::write('Data_Credential::Insert', 'INPUT', 'Row inserted into database with Id ' . $iInsertId);

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
        $oSqlConnection = Connection::make();
        $oSqlConnection->update('app_credential', $oCredential->toArray(), 'api_public', $oCredential->getApiPublic());
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
        $oSqlConnection = Connection::make();
        $oSqlConnection->delete('app_credential', 'api_public', $oCredential->getApiPublic());
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
        $oSqlConnection = Connection::make();
        $aRow           = $oSqlConnection->selectOne("
				SELECT *
				FROM `app_credential`
				WHERE `api_public` = '" . $oSqlConnection->escape($sApiPublic) . "'
			");

        if (!empty($aRow)) {
            return new Credential((object)$aRow);
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
        $oSqlConnection = Connection::make();
        $aRows          = $oSqlConnection->select("
				SELECT *
				FROM `app_credential`
			");

        if (!empty($aRows)) {
            $aCredentials = [];
            foreach ($aRows as $i => $aRow) {
                $aCredentials[] = new Credential((object)$aRow);
            }
            return $aCredentials;
        }

        throw new InvalidCredentialException();
    }
}