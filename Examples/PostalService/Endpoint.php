<?php
	namespace AppConnector;

	use AppConnector\Log\Log;

	try {
		require_once('../../Config.php');
		require_once('../../AppConnector.php');

		Log::WriteStartCall();
		Log::Write('Endpoint', 'INPUT', @file_get_contents('php://input'));

		$oAppConnector = new AppConnector();
		$oAppConnector->ValidateInteractiveCodeBlock(\Config::AppUri . $_SERVER['REQUEST_URI']);

		header('HTTP/1.1 200 OK', true, 200);
		$oObject = json_decode(@file_get_contents('php://input'));

		#Check if the merchant submitted the label creation form.
		#We could of course check if this order has been submitted in the past and directly show the label.
		if($oObject instanceof \stdClass && isset($oObject->form_data) && !empty($oObject->form_data->submit)) {
			$oResponse = new \stdClass();
			#Present 'Save As' Dialog for the merchant.
			if($oObject->form_data->direct_download == '1') {
				$oResponse->view                       = 'success-direct-download';
				$oResponse->data                       = [];
				$oResponse->data['package_label_1']    = 'https://demo.securearea.eu/Examples/PostalService/Download.php?file=specimen_label.png';
				$oResponse->data['attachment_label_1'] = 'https://demo.securearea.eu/Examples/PostalService/Download.php?file=specimen_label.png';
			} else {
				$oResponse->view                    = 'success';
				$oResponse->data                    = [];
				$oResponse->data['package_label_1'] = 'https://demo.securearea.eu/Examples/PostalService/Download.php?file=specimen_label.png';
			}

			$oResponse->data['package_label_1']    = str_replace('https://demo.securearea.eu', \Config::AppUri, $oResponse->data['package_label_1']);
			$oResponse->data['attachment_label_1'] = str_replace('https://demo.securearea.eu', \Config::AppUri, $oResponse->data['attachment_label_1']);
		} else {

			#Show inital start form.
			$oResponse       = new \stdClass();
			$oResponse->view = 'onload';
		}
		$sResponse = json_encode($oResponse, JSON_PRETTY_PRINT);

		echo $sResponse;
		die();
	} catch(\Exception $oEx) {

		Log::Write('Endpoint', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
		Log::WriteEndCall();

		header('HTTP/1.1 500 Internal Server Error', true, 500);
		echo $oEx->getMessage();
		die();
	}
