<?php
	namespace AppConnector\Examples\PSP;

	use AppConnector\Data\Data_Credential;
	use AppConnector\Entities\Credential;
	use AppConnector\Exceptions\InvalidTransactionId;

	class TransactionFactory {
		/** @var Credential */
		protected $oCredential = null;

		public function __construct() {
		}

		public function GetStatus($sTransactionId = '') {
			$this->VerifyHash();

			$oTransaction = \AppConnector\Data\Data_Transaction::GetOneByTransactionId($sTransactionId);
			if($oTransaction->GetCustomerId() !== $this->oCredential->GetCustomerId()) {
				throw new InvalidTransactionId();
			}

			$sResponse = \AppConnector\Json\JsonSerializer::Serialize($oTransaction->toStdClass());

			$oHash = new \AppConnector\Http\Hash($this->oCredential->GetApiSecret());
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
			\AppConnector\Log\Log::Write('TransactionFactory', 'INPUT_BODY', $sIncomingData);
			$this->VerifyHash($sIncomingData);

			$oPostedData  = \AppConnector\Json\JsonSerializer::DeSerialize(@file_get_contents('php://input'));
			$oTransaction = new \AppConnector\Entities\Transaction($oPostedData);
			$this->DoCreditCheck($oTransaction);

			$oTransaction->SetCustomerId($this->oCredential->GetCustomerId());
			$oTransaction->SetPayUrl(\AppConnector\Config::AppUri . '/Examples/PSP/PaymentSimulator.php?transaction_id=' . $oTransaction->GetTransactionId());

			\AppConnector\Data\Data_Transaction::Insert($oTransaction);

			$sResponse = \AppConnector\Json\JsonSerializer::Serialize($oTransaction->toStdClass());

			\AppConnector\Log\Log::Write('TransactionFactory', 'OUTPUT_BODY', $sResponse);
			$oHash = new \AppConnector\Http\Hash($this->oCredential->GetApiSecret());
			$sHash = $oHash->AddData(\AppConnector\Config::AppUri . $_SERVER['REQUEST_URI'])->AddData($sResponse)->Hash();

			header('HTTP/1.1 200 OK', true, 200);
			header('x-hash: ' . $sHash);

			return $sResponse;
		}

		/**
		 * @param string $sIncomingData
		 *
		 * @throws \AppConnector\Exceptions\InvalidHashException
		 */
		protected function VerifyHash($sIncomingData = '') {
			$aRequestHeaders   = apache_request_headers();
			$sApiPublic        = $aRequestHeaders[\AppConnector\Http\Hash::Header_Public];
			$this->oCredential = Data_Credential::GetOneByPublicKey($sApiPublic);

			#Validate if the data we received is correct and authenticated.
			$oIncomingHash = new \AppConnector\Http\Hash($this->oCredential->GetApiSecret());

			$oIncomingHash->AddData(\AppConnector\Config::AppUri . $_SERVER['REQUEST_URI']);
			if(!empty($sIncomingData)) {
				$oIncomingHash->AddData($sIncomingData);
			}

			$bValid = $oIncomingHash->IsValid($aRequestHeaders[\AppConnector\Http\Hash::Header_Hash]);

			if($bValid === false) {
				throw new \AppConnector\Exceptions\InvalidHashException();
			}
		}

		/**
		 * @param \AppConnector\Entities\Transaction $oTransaction
		 */
		protected function DoCreditCheck(\AppConnector\Entities\Transaction &$oTransaction) {
			switch($oTransaction->GetMethod()) {
				case 'afterpay':
					\AppConnector\Log\Log::Write('TransactionFactory', 'DO_CREDIT_CHECK', 'Age is ' . $oTransaction->GetAge());
					if($oTransaction->GetAge() >= 18) {
						$oTransaction->SetStatus('SUCCESS');
					} else {
						$oTransaction->SetStatus('FAILED')->SetError('Consumer does not meet the age requirement.');
					}
					break;
				default:
					break;
			}

			return;
		}
	}