<?php

	$oObject = json_decode(@file_get_contents('php://input'));

	if($oObject instanceof stdClass && isset($oObject->form_data) && !empty($oObject->form_data->submit)) {
		$oResponse = new \stdClass();
		if($oObject->form_data->direct_download == '1') {
			$oResponse->view                     = 'succes-direct-download';
			$oResponse->data                     = [];
			$oResponse->data['description']      = '[View: succes-direct-download]. Package label has been created succesfully.';
			$oResponse->data['package_label_1']  = 'https://ameijer-app.ccvdev.nl/Examples/SendService/specimen_label.png';
			$oResponse->data['download_label_1'] = 'https://ameijer-app.ccvdev.nl/Examples/SendService/specimen_label.png';
		} else {
			$oResponse->view                    = 'succes';
			$oResponse->data                    = [];
			$oResponse->data['description']     = '[View: succes]. Package label has been created succesfully.';
			$oResponse->data['package_label_1'] = 'https://ameijer-app.ccvdev.nl/Examples/SendService/specimen_label.png';
		}
		echo json_encode($oResponse, JSON_PRETTY_PRINT);
		die();
	}

	$oResponse                      = new \stdClass();
	$oResponse->view                = 'onload';
	$oResponse->data                = [];
	$oResponse->data['description'] = '[View: onload]';

	echo json_encode($oResponse, JSON_PRETTY_PRINT);

