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
	 * @version 1.0    	- Nick Postma: First draft
	 * 			1.1	   	- Nick Postma: Added database credential storage
	 * 			1.2		- Nick Postma: Implemented strategy pattern for Data_Credential and now uses oCredential->ToArray()
	 *
	 */
	class Data_Credential_SQL extends Data_Core implements IData_Credential {
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
		static public function Insert(Credential $oCredential) {
			$oSqlConnection = Connection::Make();
			$iInsertId = $oSqlConnection->Insert('app_credential', $oCredential->ToArray());
			Log::Write('Data_Credential::Insert', 'INPUT', 'Row inserted into database with Id ' . $iInsertId);

			return true;
		}

		/**
		 * Updates 1 row containing a Credential based on the Public Key
		 *
		 * @static
		 *
		 * @param Credential $oCredential
		 * @return bool
		 */
		static public function Update(Credential $oCredential)
		{
			$oSqlConnection = Connection::Make();
			$oSqlConnection->Update('app_credential', $oCredential->ToArray(), 'api_public', $oCredential->GetApiPublic());
			Log::Write('Data_Credential::Update', 'INPUT', 'Row updated on ' . $oCredential->GetApiPublic());

			return true;
		}

		/**
		 * Deletes 1 row containing a WebHook based on the Public Key
		 *
		 * @static
		 *
		 * @param Credential $oCredential
		 * @return bool
		 * @throws \Exception
		 */
		static public function Delete(Credential $oCredential)
		{
			$oSqlConnection = Connection::Make();
			$oSqlConnection->Delete('app_credential', 'api_public', $oCredential->GetApiPublic());
			Log::Write('Data_Credential::Delete', 'INPUT', 'Row updated on ' . $oCredential->GetApiPublic());

			return true;
		}

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
		static public function GetOneByPublicKey($sApiPublic = '')
		{
			$oSqlConnection = Connection::Make();
			$aRow = $oSqlConnection->SelectOne("
				SELECT *
				FROM `app_credential`
				WHERE `api_public` = '" . $oSqlConnection->Escape($sApiPublic) . "'
			");

			if(!empty($aRow)) {
				return new Credential((object)$aRow);
			}

			throw new InvalidCredentialException();
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
		static public function GetAll()
		{
			$oSqlConnection = Connection::Make();
			$aRows = $oSqlConnection->Select("
				SELECT *
				FROM `app_credential`
			");

			if(!empty($aRows)) {
				$aCredentials = [];
				foreach($aRows as $i => $aRow) {
					$aCredentials[] = new Credential((object)$aRow);
				}
				return $aCredentials;
			}

			throw new InvalidCredentialException();
		}
	}