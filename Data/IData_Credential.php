<?

namespace AppConnector\Data;

use AppConnector\Entities\Credential;
use AppConnector\Exceptions\InvalidCredentialException;

/**
 * Class Data_Credential
 * Abstract for all data manipulations for Credentials
 *
 * @package AppConnector\Data
 * @author  Nick Postma
 * @date    2016-06-14
 * @version 1.0    - Nick Postma: First draft
 *
 */
interface IData_Credential
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
    public static function insert(Credential $oCredential);

    /**
     * Updates 1 row containing a Credential based on the Public Key
     *
     * @static
     * @return bool
     *
     * @param Credential $oCredential
     */
    public static function update(Credential $oCredential);

    /**
     * Deletes 1 row containing a WebHook based on the Public Key
     *
     * @static
     * @return bool
     *
     * @param Credential $oCredential
     *
     * @throws \Exception
     */
    public static function delete(Credential $oCredential);

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
    public static function getOneByPublicKey($sApiPublic = '');

    /**
     * Return all Credentials
     *
     * @static
     *
     * @return Credential
     * @throws InvalidCredentialException
     * @throws \Exception
     */
    public static function getAll();

}