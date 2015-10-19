<?
	namespace AppConnector\Data;

	use AppConnector\Entities\Credential;
	use AppConnector\Exceptions\InvalidCredentialException;
	use AppConnector\Json\JsonSerializer;
	use AppConnector\Log\Log;

	/**
	 * Class Data_Credential
	 * Handles all data manipulations for Credentials
	 *
	 * @package AppConnector\Data
	 * @author  Adriaan Meijer
	 * @date    2014-10-13
	 * @version 1.0    - First draft
	 *          1.1    - Added logging
	 */
	class Data_Credential extends Data_Core {
		const DataFile = 'Data/data.credential.txt';

		/**
		 * Inserts 1 row containing a Credential into the data file
		 *
		 * @static
		 *
		 * @param Credential $oCredential
		 *
		 * @return bool
		 */
		static public function Insert(Credential $oCredential) {
			$oData              = new \stdClass();
			$oData->create_date = date('Y-m-d H:i:s');
			$oData->api_public  = $oCredential->GetApiPublic();
			$oData->api_secret  = $oCredential->GetApiSecret();
			$oData->api_root    = $oCredential->GetApiRoot();
			$oData->return_url  = $oCredential->GetReturnUrl();
			$oData->customer_id = null;

			#@todo: check up dubbele public keys

			fwrite(static::OpenFileToWrite(), JsonSerializer::Serialize($oData) . "\r\n");

			Log::Write('Data_Credential::Insert', 'INPUT', 'Row written on ' . $oCredential->GetApiPublic());
			return true;
		}

		/**
		 * Updates 1 row containing a Credential based on the Public Key
		 *
		 * @static
		 *
		 * @param Credential $oCredential
		 */
		static public function Update(Credential $oCredential) {
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
		}

		/**
		 * Deletes 1 row containing a WebHook based on the Public Key
		 *
		 * @static
		 *
		 * @param Credential $oCredential
		 */
		static public function Delete(Credential $oCredential) {
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
		 * @throws \AppConnector\Exceptions\InvalidJsonException
		 */
		static public function GetOneByPublicKey($sApiPublic = '') {
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
	}