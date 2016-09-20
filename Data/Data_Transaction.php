<?php

	namespace AppConnector\Data;

	use AppConnector\Entities\Transaction;
	use AppConnector\Json\JsonSerializer;
	use AppConnector\Log\Log;

	/**
	 *
	 * @author  Adriaan Meijer
	 * @version 1.0    - Initiele opzet
	 *
	 */
	class Data_Transaction extends Data_Core {
		const DataFile = 'Data/data.transaction.txt';

		/**
		 * Inserts 1 row containing a Credential into the data file
		 *
		 * @static
		 *
		 * @param Transaction $oTransaction
		 *
		 * @return bool
		 * @throws \AppConnector\Exceptions\InvalidJsonException
		 * @throws \Exception
		 */
		static public function Insert(Transaction $oTransaction) {
			fwrite(static::OpenFileToWrite(), JsonSerializer::Serialize($oTransaction->ToArray()) . "\r\n");
			Log::Write('Data_Credential::Insert', 'INPUT', 'Row written on ' . $oTransaction->GetTransactionId());

			return true;
		}

		/**
		 * Updates 1 row containing a Credential based on the Public Key
		 *
		 * @static
		 *
		 * @param Transaction $oTransaction
		 *
		 * @return bool
		 */
		static public function Update(Transaction $oTransaction) {
			$rFile = static::OpenFileToRead();
			$aData = [];
			while(($sLine = fgets($rFile)) !== false) {
				$oData = JsonSerializer::DeSerialize($sLine);
				if($oData->transaction_id === $oTransaction->GetTransactionId()) {
					$oData->status = $oTransaction->GetStatus();
				}

				$aData[] = JsonSerializer::Serialize($oData);
			}
			#Write empty line at file end
			$aData[] = null;

			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/'.static::DataFile, implode("\r\n", $aData));
			Log::Write('Data_Credential::Update', 'INPUT', 'Row updated on ' . $oTransaction->GetTransactionId());

			return true;
		}

		/**
		 * Deletes 1 row containing a WebHook based on the Public Key
		 * @static
		 *
		 * @param \AppConnector\Entities\Transaction $oTransaction
		 *
		 * @return bool
		 */
		static public function Delete(Transaction $oTransaction) {
			$rFile = static::OpenFileToRead();
			$aData = [];

			while(($sLine = fgets($rFile)) !== false) {
				$oObject = new Transaction(JsonSerializer::DeSerialize($sLine));
				if($oObject->GetTransactionId() !== $oTransaction->GetTransactionId()) {
					$sLine = str_replace(["\n", "\r"], '', $sLine);
					$sLine = trim($sLine);
					if(!empty($sLine)) {
						$aData[] = $sLine;
					}
				}
			}
			#Write empty line at file end
			$aData[] = null;

			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/'.static::DataFile, implode("\r\n", $aData));
			Log::Write('Data_Credential::Delete', 'INPUT', 'Row updated on ' . $oTransaction->GetTransactionId());

			return true;
		}

		/**
		 * @static
		 *
		 * @param string $sTransactionId
		 *
		 * @return \AppConnector\Entities\Transaction
		 * @throws \AppConnector\Exceptions\InvalidTransactionId
		 */
		static public function GetOneByTransactionId($sTransactionId = '') {
			$rFile = static::OpenFileToRead();
			while(($sLine = fgets($rFile)) !== false) {
				$oObject = new Transaction(JsonSerializer::DeSerialize($sLine));

				if($oObject->GetTransactionId() === $sTransactionId) {
					Log::Write('Data_Credential::GetOneByPublicKey', 'INPUT', 'Row found for ' . $sTransactionId);
					return $oObject;
				}
			}
			throw new \AppConnector\Exceptions\InvalidTransactionId();
		}
	}