<?php
	/**
	 *
	 * @author  Adriaan Meijer
	 * @version 1.0    - Initiele opzet
	 *
	 */

	if(!isset($_GET['file'])) {
		header('HTTP/1.1 404 File Not Found', true, 404);
		die();
	}
	$sFileName = urldecode($_GET['file']);

	if($sFileName == 'specimen_label.png') {
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"specimen_label.png\";");
		header("Content-Transfer-Encoding:  binary");
		echo file_get_contents($sFileName);
	}

	header('HTTP/1.1 404 File Not Found', true, 404);
	die();