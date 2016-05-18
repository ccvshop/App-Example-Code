<?php

	$oObject = json_decode(@file_get_contents('php://input'));

	if($oObject instanceof stdClass && isset($oObject->form_data) && !empty($oObject->form_data->submit)) {
		$oResponse = new \stdClass();
		if($oObject->form_data->direct_download == '1') {
			$oResponse->view                       = 'succes-direct-download';
			$oResponse->data                       = [];
			$oResponse->data['package_label_1']    = 'https://ameijer-app.ccvdev.nl/Examples/SendService/Download.php?file=specimen_label.png';
			$oResponse->data['attachment_label_1'] = 'https://ameijer-app.ccvdev.nl/Examples/SendService/Download.php?file=specimen_label.png';
		} else {
			$oResponse->view                    = 'succes';
			$oResponse->data                    = [];
			$oResponse->data['package_label_1'] = 'https://ameijer-app.ccvdev.nl/Examples/SendService/Download.php?file=specimen_label.png';
		}
		echo json_encode($oResponse, JSON_PRETTY_PRINT);
		die();
	}

	$oResponse       = new \stdClass();
	$oResponse->view = 'onload';
	echo json_encode($oResponse, JSON_PRETTY_PRINT);

