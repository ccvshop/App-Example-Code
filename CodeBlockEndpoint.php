<?php

	$oObject = json_decode(@file_get_contents('php://input') );


	if($oObject instanceof stdClass && isset($oObject->form_data) && isset($oObject->form_data->submit) && $oObject->form_data->submit != 'send') {
		$oResponse                              = new \stdClass();
		$oResponse->view                        = 'onload';
		$oResponse->data                        = [];
		$oResponse->data['type_bacon_text']     = 'Hello Bacon';
		$oResponse->data['type_bacon_textarea'] = 'This is the onload text, from a remote source';

		echo json_encode($oResponse, JSON_PRETTY_PRINT);

	} else if($oObject instanceof stdClass && isset($oObject->form_data) && isset($oObject->form_data->submit)) {

		$oResponse                           = new \stdClass();
		$oResponse->view                     = 'succes';
		$oResponse->data                     = [];
		$oResponse->data['placeholder_data'] = 'Button value: '. $oObject->form_data->submit;

		echo json_encode($oResponse, JSON_PRETTY_PRINT);
	} else {
		$oResponse                              = new \stdClass();
		$oResponse->view                        = 'onload';
		$oResponse->data                        = [];
		$oResponse->data['type_bacon_text']     = 'Hello Bacon';
		$oResponse->data['type_bacon_textarea'] = 'This is the onload text, from a remote source';

		echo json_encode($oResponse, JSON_PRETTY_PRINT);
	}
