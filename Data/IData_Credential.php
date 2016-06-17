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
	interface IData_Credential {
		/**
		 * Inserts 1 row containing a Credential into the data file
		 *
		 * @static
		 *
		 * @param Credential $oCredential
		 * @return bool
		 * @throws \AppConnector\Exceptions\InvalidJsonException
		 * @throws \Exception
		 */
		static public function Insert(Credential $oCredential);

		/**
		 * Updates 1 row containing a Credential based on the Public Key
		 *
		 * @static
		 * @return bool
		 * @param Credential $oCredential
		 */
		static public function Update(Credential $oCredential);

		/**
		 * Deletes 1 row containing a WebHook based on the Public Key
		 *
		 * @static
		 * @return bool
		 * @param Credential $oCredential
		 * @throws \Exception
		 */
		static public function Delete(Credential $oCredential);

		/**
		 * Return one Credential based on the Public Key
		 *
		 * @static
		 *
		 * @param string $sApiPublic
		 * @return Credential
		 * @throws InvalidCredentialException
		 * @throws \Exception
		 */
		static public function GetOneByPublicKey($sApiPublic = '');

		/**
		 * Return all Credentials
		 *
		 * @static
		 *
		 * @return Credential
		 * @throws InvalidCredentialException
		 * @throws \Exception
		 */
		static public function GetAll();

	}