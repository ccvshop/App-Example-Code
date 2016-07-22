<?php
	/**
	 *
	 * @author  Adriaan Meijer
	 * @version 1.0    - Initiele opzet
	 *
	 */
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

	if(isset($_GET['transaction_id'])){

		$oTransaction = \AppConnector\Data\Data_Transaction::GetOneByTransactionId($_GET['transaction_id']);
		echo \AppConnector\Json\JsonSerializer::Serialize($oTransaction->ToArray());
		die();

	}


	$oPostedData  = \AppConnector\Json\JsonSerializer::DeSerialize(@file_get_contents('php://input'));
	$oTransaction = new \AppConnector\Entities\Transaction($oPostedData);
	\AppConnector\Data\Data_Transaction::Insert($oTransaction);

	$oOutput                 = new \stdClass();
	$oOutput->status         = 'OK'; #FAILED
	$oOutput->pay_url         = \AppConnector\Config::AppUri . '/Examples/PSP/PaymentSimulator.php?transaction_id=' . $oTransaction->GetTransactionId();
	$oOutput->transaction_id = $oTransaction->GetTransactionId();
	$oOutput->errorCode      = null;
	$oOutput->errorMsg       = null;

	echo json_encode($oOutput);
