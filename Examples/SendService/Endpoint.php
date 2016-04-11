<?php

	$oObject = json_decode(@file_get_contents('php://input'));

	if($oObject instanceof stdClass && isset($oObject->form_data) && !empty($oObject->form_data->type_bacon_text)) {

		$oResponse                      = new \stdClass();
		$oResponse->view                = 'succes';
		$oResponse->data                = [];
		$oResponse->data['description'] = '[View: succes]. Package label has been created succesfully.';
		$oResponse->data['label-1']     = 'https://ameijer-app.ccvdev.nl/Examples/SendService/specimen_label.png';

		echo json_encode($oResponse, JSON_PRETTY_PRINT);
		die();
	}

		$oResponse                              = new \stdClass();
		$oResponse->view                        = 'onload';
		$oResponse->data                        = [];
		$oResponse->data['description']         = '[View: onload]';
		$oResponse->data['type_bacon_text']     = 'Hello Bacon';
		$oResponse->data['type_bacon_textarea'] = 'This is the onload text, from a remote source2';
		$oResponse->data['invoice']             = 'https://ameijer-app.ccvdev.nl/Examples/SendService/specimen_label.png';
		$oResponse->data['invoice-2']           = 'https://ameijer-app.ccvdev.nl/Examples/SendService/specimen_label.png';

		echo json_encode($oResponse, JSON_PRETTY_PRINT);

