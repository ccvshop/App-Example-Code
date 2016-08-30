<?
	/**
	 * Step 3. UnInstall Endpoint
	 * Whenever the user uninstalls the app in his webshop, the UnInstall Endpoint will be called.
	 * This will give you the option to process the uninstall.
	 */
	namespace AppConnector;

	use AppConnector\Log\Log;

	try {
		require_once('AppConnector.php');

		Log::WriteStartCall(__FILE__);
		Log::Write('UnInstall', 'INPUT', @file_get_contents('php://input'));

		$oAppConnector = new AppConnector();
		$oAppConnector->UnInstall();

		Log::Write('Handshake', 'OUTPUT', 'HTTP/1.1 200 OK');
		Log::WriteEndCall(__FILE__);

		header('HTTP/1.1 200 OK', true, 200);
		die('OK');
	} catch(\Exception $oEx) {
		Log::Write('UnInstall', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
		Log::WriteEndCall(__FILE__);

		header('HTTP/1.1 500 Internal Server Error', true, 500);
		echo $oEx->getMessage();
		die();
	}
