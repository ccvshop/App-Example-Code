<?
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
	class Data_Credential {

		/**
		 * @static
		 *
		 * @return IData_Credential
		 * @throws \Exception
		 */
		protected static function GetHandlerClassname() {
			$sDataCrentialClass = "AppConnector\Data\Data_Credential_" . Config::CredentialStorageType;

			if(!class_exists($sDataCrentialClass)) {
				throw new \Exception('Could not determine the credential handler (' .
									 $sDataCrentialClass .
									 '). Please check CredentialStorageType in the config class');
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
		static public function Insert(Credential $oCredential) {
			$sDataCrentialClass = static::GetHandlerClassname();
			return $sDataCrentialClass::Insert($oCredential);
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
		static public function Update(Credential $oCredential) {
			$sDataCrentialClass = static::GetHandlerClassname();
			return $sDataCrentialClass::Update($oCredential);
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
		static public function Delete(Credential $oCredential) {
			$sDataCrentialClass = static::GetHandlerClassname();
			return $sDataCrentialClass::Delete($oCredential);
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
		static public function GetOneByPublicKey($sApiPublic = '') {
			$sDataCrentialClass = static::GetHandlerClassname();
			return $sDataCrentialClass::GetOneByPublicKey($sApiPublic);
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
		static public function GetAll() {
			$sDataCrentialClass = static::GetHandlerClassname();
			return $sDataCrentialClass::GetAll();
		}

	}