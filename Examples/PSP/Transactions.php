<?php
	/**
	 *
	 * @author  Adriaan Meijer
	 * @version 1.0    - Initiele opzet
	 *
	 */
	try {

		require_once('../../Config.php');
		require_once('../../Data/Data_Core.php');
		require_once('../../Data/Data_Credential.php');
		require_once('../../Data/Data_WebHook.php');
		require_once('../../Data/Data_Transaction.php');
		require_once('../../Entities/Credential.php');
		require_once('../../Entities/WebHook.php');
		require_once('../../Entities/Transaction.php');
		require_once('../../Exceptions/InvalidApiResponse.php');
		require_once('../../Exceptions/InvalidCredentialException.php');
		require_once('../../Exceptions/InvalidHashException.php');
		require_once('../../Exceptions/InvalidJsonException.php');
		require_once('../../Exceptions/InvalidTransactionId.php');
		require_once('../../Json/JsonSerializer.php');
		require_once('../../Http/WebRequest.php');
		require_once('../../Http/Hash.php');
		require_once('../../Log/Log.php');
		#Transaction opslaan en eigen ID genereren.

		$aRequestHeaders = apache_request_headers();
		$sIncomingData   = @file_get_contents('php://input');

		\AppConnector\Log\Log::WriteStartCall(__FILE__);
		\AppConnector\Log\Log::Write('Endpoint', 'INPUT_BODY', $sIncomingData);

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

		if(isset($_GET['transaction_id'])) {

			$oTransaction = \AppConnector\Data\Data_Transaction::GetOneByTransactionId($_GET['transaction_id']);
			$sResponse = \AppConnector\Json\JsonSerializer::Serialize($oTransaction->ToArray());

			$oHash = new \AppConnector\Http\Hash();
			$sHash = $oHash->AddData(\AppConnector\Config::AppUri . $_SERVER['REQUEST_URI'])->AddData($sResponse)->Hash();

			header('HTTP/1.1 200 OK', true, 200);
			header('x-hash: ' . $sHash);

			\AppConnector\Log\Log::WriteEndCall(__FILE__);
			echo $sResponse;
			die();

			die();
		}

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

		\AppConnector\Log\Log::Write('Endpoint', 'OUTPUT_BODY', $sResponse);

		$oHash = new \AppConnector\Http\Hash();
		$sHash = $oHash->AddData(\AppConnector\Config::AppUri . $_SERVER['REQUEST_URI'])->AddData($sResponse)->Hash();

		header('HTTP/1.1 200 OK', true, 200);
		header('x-hash: ' . $sHash);

		\AppConnector\Log\Log::WriteEndCall(__FILE__);
		echo $sResponse;
		die();

	} catch(\Exception $oEx) {

		\AppConnector\Log\Log::Write('Endpoint', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
		\AppConnector\Log\Log::WriteEndCall(__FILE__);

		header('HTTP/1.1 500 Internal Server Error', true, 500);
		echo $oEx->getMessage();
		die();
	}
