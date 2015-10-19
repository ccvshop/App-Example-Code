<?
	namespace AppConnector;

	use AppConnector\Data\Data_Credential;
	use AppConnector\Data\Data_WebHook;
	use AppConnector\Entities\Credential;
	use AppConnector\Entities\WebHook;
	use AppConnector\Exceptions\InvalidApiResponse;
	use AppConnector\Exceptions\InvalidCredentialException;
	use AppConnector\Exceptions\InvalidHashException;
	use AppConnector\Exceptions\InvalidJsonException;
	use AppConnector\Http\WebRequest;
	use AppConnector\Json\JsonSerializer;
	use AppConnector\Log\Log;

	require_once('Data/Data_Core.php');
	require_once('Data/Data_Credential.php');
	require_once('Data/Data_WebHook.php');
	require_once('Entities/Credential.php');
	require_once('Entities/WebHook.php');
	require_once('Exceptions/InvalidApiResponse.php');
	require_once('Exceptions/InvalidCredentialException.php');
	require_once('Exceptions/InvalidHashException.php');
	require_once('Exceptions/InvalidJsonException.php');
	require_once('Json/JsonSerializer.php');
	require_once('Http/WebRequest.php');
	require_once('Log/Log.php');

	/**
	 * Class AppConnector
	 * Handles all actions for the App.
	 *
	 * @package AppConnector
	 * @author  Adriaan Meijer
	 * @date    2014-10-13
	 * @version 1.0    - First draft
	 *          1.1    - Added logging
	 *          1.2	   - Added construct check on config costants
	 */
	class AppConnector {
		/**
		 * This contains a secret key which is unique for this App.
		 * You can find this as a property of the App in the Developer App Center
		 * Example: 'dsadsakldjsakljdklsajdklsajdkljas'
		 */
		const AppSecretKey = null;

		/**
		 * This is the URI of the handshake. Use this to validate calls from the App store.
		 * Example: https://demo.biedmeer.nl/Handshake.php
		 */
		const AppHandshakeUri = null;

		/**
		 * This is the URI of the Uninstall. Use this to validate calls from the App store.
		 * Example: https://demo.biedmeer.nl/UnInstall.php
		 */
		const AppUninstallUri = null;

		/**
		 * This is the field in the header of each request that contains the hash. Do NOT change this unless instructed by CCV.
		 */
		const Header_Hash = 'x-hash';

		/**
		 * This is the encryption method with which the hash was made. Do NOT change this unless instructed by CCV.
		 */
		const Hash_Encryption = 'sha512';

		/**
		 * This character separates the fields which are hashed. Do NOT change this unless instructed by CCV.
		 */
		const Hash_Field_Separator = '|';

		/**
		 * @var object Credential Contains the credentials. Used for example purposes only
		 */
		private $Credential;

		/**
		 * @var array Contains the webhooks which need to be Posted to the web shop. Used for example purposes only.
		 */
		private $RequiredWebHooks = array(
			array('event' => 'products.created', 'address' => 'https://development.bmdev.nl/void.php'),
			array('event' => 'products.updated', 'address' => 'https://development.bmdev.nl/void.php'),
			array('event' => 'products.deleted', 'address' => 'https://development.bmdev.nl/void.php'),
		);

		public function __construct() {
			if(is_null($this::AppSecretKey)) {
				throw new \Exception('AppSecretKey is empty. Please config AppConnector.php');
			}

			if(is_null($this::AppHandshakeUri)) {
				throw new \Exception('AppHandshakeUri is empty. Please config AppConnector.php');
			}

			if(is_null($this::AppUninstallUri)) {
				throw new \Exception('AppUnInstallUri is empty. Please config AppConnector.php');
			}
		}

		/**
		 * Processes the handshake. The app store will send JSON containing api credentials.
		 * These credentials will be needed further in the process.
		 *
		 * @throws InvalidHashException
		 * @throws InvalidJsonException
		 */
		public function ProcessCredentials() {
			$this->ValidateHash($this::AppHandshakeUri);

			$oData = JsonSerializer::DeSerialize(@file_get_contents('php://input'));

			$this->Credential = new Credential($oData);
			Data_Credential::Insert($this->Credential);
		}

		/**
		 * Once the customer has successfully filled in the form, we proceed with the installation.
		 * Creating the needed WebHooks in the webshop and marking the app as installed.
		 *
		 * @throws InvalidApiResponse
		 * @throws InvalidCredentialException
		 */
		public function Install() {
			$sApiPublic       = $_REQUEST['api_public'];
			$this->Credential = Data_Credential::GetOneByPublicKey($sApiPublic);
			$this->Credential->SetCustomerId($_REQUEST['customer_id']);

			Data_Credential::Update($this->Credential);

			#Creating WebHooks in the webshop
			$this->Install_WebHooks();

			#Marking the app as installed (MANDATORY).
			$this->Install_App();
		}

		/**
		 * Creates the required webhooks in the webshop.
		 *
		 * @throws InvalidJsonException
		 */
		private function Install_WebHooks() {
			$oWebRequest = new WebRequest();
			$oWebRequest->SetPublicKey($this->Credential->GetApiPublic());
			$oWebRequest->SetSecretKey($this->Credential->GetApiSecret());
			$oWebRequest->SetApiRoot($this->Credential->GetApiRoot());
			$oWebRequest->SetApiResource('/api/rest/v1/webhooks');

			foreach($this->RequiredWebHooks as $aWebHook) {
				$oData          = new \stdClass();
				$oData->event   = $aWebHook['event'];
				$oData->address = $aWebHook['address'];

				$oWebRequest->SetData($oData);
				$sOutput = $oWebRequest->Post();

				$oWebHook = new WebHook(JsonSerializer::DeSerialize($sOutput));
				$oWebHook->SetCustomerId($this->Credential->GetCustomerId());

				#Store WebHook keys
				Data_WebHook::Insert($oWebHook);
			}
		}

		/**
		 * Mandatory.
		 * Calls the API and retrieves the App.Id associated with the api_public.
		 * After that a Patch is send to update the app.is_installed property, marking it as installed.
		 *
		 * @throws InvalidApiResponse
		 * @throws InvalidJsonException
		 */
		private function Install_App() {
			$oWebRequest = new WebRequest();
			#Getting Remote App resource
			$oWebRequest->SetPublicKey($this->Credential->GetApiPublic());
			$oWebRequest->SetSecretKey($this->Credential->GetApiSecret());
			$oWebRequest->SetApiRoot($this->Credential->GetApiRoot());
			$oWebRequest->SetApiResource('/api/rest/v1/apps');
			$sOutput = $oWebRequest->Get();

			$aCollectionOfApps = JsonSerializer::DeSerialize($sOutput);

			if(!isset($aCollectionOfApps->items)) {
				throw new InvalidApiResponse('Collection contained zero apps. Expected 1.');
			}

			if(count($aCollectionOfApps->items) > 1) {
				throw new InvalidApiResponse('Collection contained ' . count($aCollectionOfApps->items) . ' apps. Expected 1.');
			}
			$iAppId = $aCollectionOfApps->items[0]->id;

			#Marking app as 'installed'
			$oApp               = new \stdClass();
			$oApp->is_installed = true;

			$oWebRequest->SetApiResource('/api/rest/v1/apps/' . $iAppId);
			$oWebRequest->SetData($oApp);
			$oWebRequest->Patch();
		}

		/**
		 * Optional.
		 * Just clears up some of the local data files.
		 *
		 * @throws InvalidCredentialException
		 * @throws InvalidHashException
		 * @throws InvalidJsonException
		 */
		public function UnInstall() {
			$this->ValidateHash($this::AppUninstallUri);

			$oPostedData      = JsonSerializer::DeSerialize(@file_get_contents('php://input'));
			$this->Credential = Data_Credential::GetOneByPublicKey($oPostedData->api_public);

			$aWebHooks = Data_WebHook::GetAllByCustomerId($this->Credential->GetCustomerId());

			/** @var WebHook $oWebHook */
			foreach($aWebHooks as $oWebHook) {
				Data_WebHook::Delete($oWebHook);
			}

			Data_Credential::Delete($this->GetCredential());
		}

		/**
		 * @return Credential
		 * @throws InvalidCredentialException
		 */
		public function GetCredential() {
			if(!is_a($this->Credential, 'AppConnector\Entities\Credential')) {
				throw new InvalidCredentialException();
			}
			return $this->Credential;
		}

		/**
		 * Validates the hash in the header with the calculated hash. Check data integrity.
		 *
		 * @param $sUri
		 *
		 * @throws InvalidHashException
		 */
		private function ValidateHash($sUri) {
			$aRequestHeaders = apache_request_headers();
			Log::Write('ValidateHash', 'VALIDATE', $aRequestHeaders[self::Header_Hash]);
			$aDataToHash[] = $sUri;
			$aDataToHash[] = @file_get_contents('php://input');

			$sStringToHash = implode(static::Hash_Field_Separator, $aDataToHash);

			$sHash = hash_hmac(static::Hash_Encryption, $sStringToHash, $this::AppSecretKey);

			if($sHash !== $aRequestHeaders[self::Header_Hash]) {
				throw new InvalidHashException();
			}
			Log::Write('ValidateHash', 'VALIDATE OK', $aRequestHeaders[self::Header_Hash]);
		}
	}
