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
	 * 			1.2	   - Nick Postma: Added database credential storage
	 * 			1.3	   - Nick Postma: Implemented strategy pattern for Data_Credential (Classe renamed to Data_Credential_JSON) and now uses oCredential->ToArray()
	 *
	 */
	class Data_Credential_JSON  extends Data_Core implements IData_Credential {
		const DataFile = 'Data/data.credential.txt';

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

			#@todo: check up dubbele public keys
			fwrite(static::OpenFileToWrite(), JsonSerializer::Serialize($oCredential->ToArray()) . "\r\n");
			Log::Write('Data_Credential::Insert', 'INPUT', 'Row written on ' . $oCredential->GetApiPublic());

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
			$rFile = static::OpenFileToRead();
			$aData = array();
			while(($sLine = fgets($rFile)) !== false) {
				$oData = JsonSerializer::DeSerialize($sLine);
				if($oData->api_public === $oCredential->GetApiPublic()) {
					$oData->customer_id = $oCredential->GetCustomerId();
				}

				$aData[] = JsonSerializer::Serialize($oData);
			}
			#Write empty line at file end
			$aData[] = null;

			file_put_contents(static::DataFile, implode("\r\n", $aData));
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
			$rFile = static::OpenFileToRead();
			$aData = array();

			while(($sLine = fgets($rFile)) !== false) {
				$oObject = new Credential(JsonSerializer::DeSerialize($sLine));
				if($oObject->GetApiPublic() !== $oCredential->GetApiPublic()) {
					$sLine = str_replace(array("\n", "\r"), '', $sLine);
					$sLine = trim($sLine);
					if(!empty($sLine)) {
						$aData[] = $sLine;
					}
				}
			}
			#Write empty line at file end
			$aData[] = null;

			file_put_contents(static::DataFile, implode("\r\n", $aData));
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
			$rFile = static::OpenFileToRead();
			while(($sLine = fgets($rFile)) !== false) {
				$oObject = new Credential(JsonSerializer::DeSerialize($sLine));
				if($oObject->GetApiPublic() === $sApiPublic) {
					Log::Write('Data_Credential::GetOneByPublicKey', 'INPUT', 'Row found for ' . $sApiPublic);
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
		 * @return Credential
		 * @throws InvalidCredentialException
		 * @throws \Exception
		 */
		static public function GetAll()
		{
			$rFile = static::OpenFileToRead();
			$aCredentials = [];
			while(($sLine = fgets($rFile)) !== false) {
				$aCredentials[] = new Credential(JsonSerializer::DeSerialize($sLine));
			}
			if(!empty($aCredentials)) {
				return $aCredentials;
			}
			throw new InvalidCredentialException();
		}
	}