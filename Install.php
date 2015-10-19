<?
	/**
	 * Step 2. Install Endpoint
	 * The install step is the location where the user will be ask to confirm his account and set any settings associated with this app.
	 * You can look up the credentials given in the step 1 handshake with the api_public in the uri.
	 * You are free to design this process in any way needed. Once the user completes his installation process,
	 * he should be forwarded to the Return URL given in the handshake.
	 * Make sure you mark the app as 'installed' before forwarding the user.
	 */
	namespace AppConnector;

	use AppConnector\Log\Log;

	require_once('AppConnector.php');

	/**
	 * Some minor validation if the input is indeed a string.
	 */
	$_GET['api_public'] = is_string($_GET['api_public']) ? $_GET['api_public'] : null;

	if(empty($_POST)) {
		Log::WriteStartCall();
		Log::Write('Install', 'VIEW', 'api_public: ' . $_GET['api_public']);
		Log::WriteEndCall();
		#First visit

		?>
		<html>
			<head>
				<title></title>
			</head>
			<body>
				<h1>App Simulator - Installation</h1>

				<form action="Install.php" method="post">
					<label for="api_public">api_public</label>
					<input type="text" name="api_public" id="api_public" value="<?= $_GET['api_public'] ?>" />
					<br />
					<label for="customer_id">Customer Id</label>
					<input type="text" name="customer_id" id="customer_id" value="1337" />
					<button name="Cancel">Cancel</button>
					<button name="Install">Install</button>
				</form>
			</body>

		</html>
	<?
	} else {

		try {
			#Installing App
			$oAppConnector = new AppConnector();
			$oAppConnector->Install();

			Log::WriteStartCall();
			Log::Write('Install', 'OUTPUT', 'Location: ' . $oAppConnector->GetCredential()->GetReturnUrl());
			Log::WriteEndCall();

			header('Location: ' . $oAppConnector->GetCredential()->GetReturnUrl());
			die();
		} catch(\Exception $oEx) {

			Log::Write('Install', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
			Log::WriteEndCall();

			header('HTTP/1.1 500 Internal Server Error', true, 500);
			echo $oEx->getMessage();
			die();
		}
	}

