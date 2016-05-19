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
	 * It's advised to store this in a session for instances. For demo purposes we'll leave this in the request.
	 */
	$_GET['api_public'] = (isset($_GET['api_public']) && is_string($_GET['api_public'])) ? $_GET['api_public'] : null;

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

		<p>
			This is a simulation tool for differnt kind of apps. All apps use the same installation principal, but the effect it will have in the webshop
			differs. The purpose of this tool is to demo the options and possibilties you could use when developing an app.
		</p>

		<h2>Debug Info</h2>

		<p>
			api_public: <?= $_GET['api_public'] ?>
		</p>

		<h2>Bare installation</h2>

		<p>This will only install the app. No futher API calls will be done. This is a good starting point if you have full understanding of the API</p>

		<form action="Install.php" method="post">
			<input type="hidden" name="api_public" id="api_public" value="<?= $_GET['api_public'] ?>"/>
			<input type="hidden" name="install_type" id="install_type" value="bare"/>

			<label for="customer_id">Customer Id</label>
			<input type="text" name="customer_id" id="customer_id" value="1337"/>

			<button name="Cancel">Cancel</button>
			<button name="Install">Install</button>
		</form>

		<h2>Webhook installation</h2>

		<p>This will install the app after creating a few webhooks in the webshop.</p>

		<form action="Install.php" method="post">
			<input type="hidden" name="api_public" id="api_public" value="<?= $_GET['api_public'] ?>"/>
			<input type="hidden" name="install_type" id="install_type" value="webhooks"/>

			<label for="customer_id">Customer Id</label>
			<input type="text" name="customer_id" id="customer_id" value="1337"/>

			<button name="Cancel">Cancel</button>
			<button name="Install">Install</button>
		</form>

		<h2>Basic App Code block installation</h2>

		<p>This will install the app after creating an App Code block in the frontend of the webshop. This could be used to place tracking pixels, chat
		   services, etc. In this example a tracking pixel is added.</p>

		<form action="Install.php" method="post">
			<input type="hidden" name="api_public" id="api_public" value="<?= $_GET['api_public'] ?>"/>
			<input type="hidden" name="install_type" id="install_type" value="tracking_pixel"/>

			<label for="customer_id">Customer Id</label>
			<input type="text" name="customer_id" id="customer_id" value="1337"/>

			<button name="Cancel">Cancel</button>
			<button name="Install">Install</button>
		</form>

		<h2>Interactive App Code block installation - Pigeon Postal Service</h2>

		<p>This will install the app after creating an interactive App Code block. In this example a postal service is installed. In the order management on the tab 'Connections' the merchant can choose
		   different options when creating a package label.</p>

		<form action="Install.php" method="post">
			<input type="hidden" name="api_public" id="api_public" value="<?= $_GET['api_public'] ?>"/>
			<input type="hidden" name="install_type" id="install_type" value="postal_service"/>

			<label for="customer_id">Customer Id</label>
			<input type="text" name="customer_id" id="customer_id" value="1337"/>

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

