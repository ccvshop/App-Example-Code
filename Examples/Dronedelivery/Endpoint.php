<?php

	$oObject = json_decode(@file_get_contents('php://input'));

	#Check if the merchant submitted the label creation form.
	#We could of course check if this order has been submitted in the past and directly show the label.
	if($oObject instanceof stdClass && isset($oObject->form_data) && !empty($oObject->form_data->submit)) {
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
		echo json_encode($oResponse, JSON_PRETTY_PRINT);
		die();
	}

	#Show inital start form.
	$oResponse       = new \stdClass();
	$oResponse->view = 'onload';
	echo json_encode($oResponse, JSON_PRETTY_PRINT);

