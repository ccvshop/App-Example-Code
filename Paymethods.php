<?
	/**
	 * Step 1. Handshake Endpoint
	 * The Handshake is the first step in installing the application. The webshop send the initial credentials to the Handshake Endpoint.
	 * When the Handshake is successful and this page returns a HTTP 200 OK, the user will be forwarded to the Install Endpoint (step 2).
	 */
	namespace AppConnector;

	use AppConnector\Log\Log;
	use PSPConnector\PSPConnector;

	try {
		require_once('PSPConnector.php');
		require_once('AppConnector.php');


		Log::WriteStartCall();
		Log::Write('Paymethods', 'INPUT', @file_get_contents('php://input'));

		$oPSPConnector = new PSPConnector();
		$sOutput       = $oPSPConnector->ProcessPaymethods();

		header('HTTP/1.1 200 OK', true, 200);
		echo $sOutput;

		Log::Write('Paymethods', 'OUTPUT_HEAD', 'HTTP/1.1 200 OK');
		Log::Write('Paymethods', 'OUTPUT_BODY', $sOutput);
		Log::WriteEndCall();

		die();
	} catch(\Exception $oEx) {

		Log::Write('Paymethods', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
		Log::WriteEndCall();

		header('HTTP/1.1 500 Internal Server Error', true, 500);
		echo $oEx->getMessage();
		die();
	}

