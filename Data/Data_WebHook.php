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
	class Data_WebHook extends Data_Core {
		const DataFile = 'Data/data.webhook.txt';

		/**
		 * Inserts 1 row containing a WebHook into the data file
		 *
		 * @static
		 *
		 * @param WebHook $oWebHook
		 *
		 * @return bool
		 */
		static public function Insert(WebHook $oWebHook) {

			$oData              = new \stdClass();
			$oData->id          = static::GetLastId() + 1;
			$oData->customer_id = $oWebHook->GetCustomerId();
			$oData->event       = $oWebHook->GetEvent();
			$oData->address     = $oWebHook->GetAddress();
			$oData->key         = $oWebHook->GetKey();
			fwrite(static::OpenFileToWrite(), JsonSerializer::Serialize($oData) . "\r\n");
			Log::Write('Data_WebHook::Insert', 'INPUT', 'Row written on ' . $oData->id);
			return true;
		}

		/**
		 * Deletes 1 row containing a WebHook based on ID
		 *
		 * @static
		 *
		 * @param WebHook $oWebHook
		 */
		static public function Delete(WebHook $oWebHook) {
			$rFile = static::OpenFileToRead();
			$aData = array();
			while(($sLine = fgets($rFile)) !== false) {
				$oData = new WebHook(JsonSerializer::DeSerialize($sLine));
				if($oData->GetId() !== $oWebHook->GetId()) {
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
			Log::Write('Data_WebHook::Delete', 'INPUT', 'Row deleted on ' . $oWebHook->GetId());
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
		static public function GetAllByCustomerId($iCustomerId = 0) {
			$rFile   = static::OpenFileToRead();
			$aResult = array();
			while(($sLine = fgets($rFile)) !== false) {
				$oWebHook = new WebHook(JsonSerializer::DeSerialize($sLine));
				if($oWebHook->GetCustomerId() === $iCustomerId) {
					$aResult[] = $oWebHook;
				}
			}

			Log::Write('Data_WebHook::GetAllByCustomerId', 'INPUT', count($aResult) . ' Rows found for ' . $iCustomerId);

			return $aResult;
		}

		/**
		 * Returns the last used ID in the data file.
		 *
		 * @static
		 * @return int
		 * @throws \AppConnector\Exceptions\InvalidJsonException
		 */
		static private function GetLastId() {
			$rFile = static::OpenFileToRead();
			$iId   = 0;
			while(($sLine = fgets($rFile)) !== false) {
				$oWebHook = new WebHook(JsonSerializer::DeSerialize($sLine));
				if($oWebHook->GetId() > $iId) {
					$iId = $oWebHook->GetId();
				}
			}

			return $iId;
		}
	}