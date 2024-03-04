<?php

namespace AppConnector\Data;

use AppConnector\Config;
use AppConnector\Entities\Credential;
use AppConnector\Exceptions\InvalidCredentialException;

require_once('IData_Credential.php');
require_once('Data_Credential_SQL.php');
require_once('Data_Credential_JSON.php');

/**
 * Class Data_Credential
 * Concrete class for all data manipulations for Credentials
 *
 * @package AppConnector\Data
 * @author  Nick Postma
 * @date    2016-06-14
 * @version 1.0    - First draft
 *
 */
class Data_Credential
{

    /**
     * @static
     *
     * @return IData_Credential
     * @throws \Exception
     */
    protected static function getHandlerClassname()
    {
        /** @var IData_Credential $sDataCrentialClass */
        $sDataCrentialClass = "AppConnector\Data\Data_Credential_" . Config::CREDENTIAL_STORAGE_TYPE;

        if (!class_exists($sDataCrentialClass)) {
            throw new \Exception('Could not determine the credential handler (' . $sDataCrentialClass . '). Please check CredentialStorageType in the config class');
        }

        return $sDataCrentialClass;
    }

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
        $sDataCrentialClass = static::getHandlerClassname();
        return $sDataCrentialClass::insert($oCredential);
    }

    /**
     * Updates 1 row containing a Credential based on the Public Key
     *
     * @static
     *
     * @param \AppConnector\Entities\Credential $oCredential
     *
     * @return bool
     * @throws \Exception
     */
    public static function update(Credential $oCredential)
    {
        $sDataCrentialClass = static::getHandlerClassname();
        return $sDataCrentialClass::update($oCredential);
    }

    /**
     * Deletes 1 row containing a WebHook based on the Public Key
     * @static
     *
     * @param \AppConnector\Entities\Credential $oCredential
     *
     * @return bool
     * @throws \Exception
     */
    public static function delete(Credential $oCredential)
    {
        $sDataCrentialClass = static::getHandlerClassname();
        return $sDataCrentialClass::delete($oCredential);
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
        $sDataCrentialClass = static::getHandlerClassname();
        return $sDataCrentialClass::getOneByPublicKey($sApiPublic);
    }

    /**
     * Return all Credentials
     *
     * @static
     *
     * @return Credential
     * @throws InvalidCredentialException
     * @throws \Exception
     */
    public static function getAll()
    {
        $sDataCrentialClass = static::getHandlerClassname();
        return $sDataCrentialClass::getAll();
    }

}