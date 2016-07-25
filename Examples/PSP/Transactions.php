<?php
	namespace AppConnector\Examples\PSP;

	class Transactions {

		public function __construct() {
		}

		public function GetStatus($sTransactionId = '') {
			$this->VerifyHash();

			$oTransaction = \AppConnector\Data\Data_Transaction::GetOneByTransactionId($sTransactionId);
			$sResponse    = \AppConnector\Json\JsonSerializer::Serialize($oTransaction->ToArray());

			$oHash = new \AppConnector\Http\Hash();
			$sHash = $oHash->AddData(\AppConnector\Config::AppUri . $_SERVER['REQUEST_URI'])->AddData($sResponse)->Hash();

			header('HTTP/1.1 200 OK', true, 200);
			header('x-hash: ' . $sHash);

			return $sResponse;
		}

		/**
		 *
		 * @return string
		 * @throws \AppConnector\Exceptions\InvalidHashException
		 */
		public function Create() {

			$sIncomingData = @file_get_contents('php://input');
			\AppConnector\Log\Log::Write('Transactions', 'INPUT_BODY', $sIncomingData);

			$this->VerifyHash($sIncomingData);

			$oPostedData  = \AppConnector\Json\JsonSerializer::DeSerialize(@file_get_contents('php://input'));
			$oTransaction = new \AppConnector\Entities\Transaction($oPostedData);
			\AppConnector\Data\Data_Transaction::Insert($oTransaction);

			$oOutput                 = new \stdClass();
			$oOutput->status         = 'OK'; #FAILED
			$oOutput->pay_url        = \AppConnector\Config::AppUri . '/Examples/PSP/PaymentSimulator.php?transaction_id=' . $oTransaction->GetTransactionId();
			$oOutput->transaction_id = $oTransaction->GetTransactionId();
			$oOutput->errorCode      = null;
			$oOutput->errorMsg       = null;
			$sResponse               = \AppConnector\Json\JsonSerializer::Serialize($oOutput);

			\AppConnector\Log\Log::Write('Transactions', 'OUTPUT_BODY', $sResponse);

			$oHash = new \AppConnector\Http\Hash();
			$sHash = $oHash->AddData(\AppConnector\Config::AppUri . $_SERVER['REQUEST_URI'])->AddData($sResponse)->Hash();

			header('HTTP/1.1 200 OK', true, 200);
			header('x-hash: ' . $sHash);

			\AppConnector\Log\Log::WriteEndCall(__FILE__);
			return $sResponse;
		}

		/**
		 * @param string $sIncomingData
		 *
		 * @throws \AppConnector\Exceptions\InvalidHashException
		 */
		protected function VerifyHash($sIncomingData = '') {
			$aRequestHeaders = apache_request_headers();
			#Validate if the data we received is correct and authenticated.
			$oIncomingHash = new \AppConnector\Http\Hash();

			$oIncomingHash->AddData(\AppConnector\Config::AppUri . $_SERVER['REQUEST_URI']);
			if(!empty($sIncomingData)) {
				$oIncomingHash->AddData($sIncomingData);
			}

			$bValid = $oIncomingHash->IsValid($aRequestHeaders[\AppConnector\Http\Hash::Header_Hash]);

			if($bValid === false) {
				throw new \AppConnector\Exceptions\InvalidHashException();
			}
		}
	}