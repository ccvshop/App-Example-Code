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
	use AppConnector\Http\Hash;
	use AppConnector\Http\WebRequest;
	use AppConnector\Json\JsonSerializer;
	use AppConnector\Log\Log;

	require_once('Config.php');
	require_once('Sql/Connection.php');

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
	require_once('Http/Hash.php');
	require_once('Log/Log.php');

	/**
	 * Class AppConnector
	 * Handles all actions for the App.
	 *
	 * @package AppConnector
	 * @author  Adriaan Meijer
	 * @date    2014-10-13
	 * @version 1.0        - First draft
	 *          1.1        - Added logging
	 *          1.2        - Added construct check on config costants
	 *          1.3        - Added additional examples for interactive code blocks
	 *          1.4        - Nick Postma: demo.securearea.eu replacement with config data
	 *          1.5        - Thijs Bekke: Example of RankingTheProducts
	 */
	class AppConnector {
		/**
		 * @var Credential Credential Contains the credentials. Used for example purposes only
		 */
		protected $Credential;

		/**
		 * @var null|int Contains the ID
		 */
		protected $RemoteAppId = null;

		public function __construct() {
			if(is_null(Config::AppSecretKey)) {
				throw new \Exception('AppSecretKey is empty. Please config Config.php');
			}

			if(is_null(Config::AppHandshakeUri)) {
				throw new \Exception('AppHandshakeUri is empty. Please config Config.php');
			}

			if(is_null(Config::AppUninstallUri)) {
				throw new \Exception('AppUnInstallUri is empty. Please config Config.php');
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
			Log::Write(__FUNCTION__, 'START');
			$this->ValidateHash(Config::AppHandshakeUri);

			$oData = JsonSerializer::DeSerialize(@file_get_contents('php://input'));

			$this->Credential = new Credential($oData);
			Data_Credential::Insert($this->Credential);
			Log::Write(__FUNCTION__, 'END');
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

			switch($_REQUEST['install_type']) {
				case 'webhooks':
					$this->Install_WebHooks();
					break;
				case 'tracking_pixel':
					$this->Install_TrackingPixel();
					break;
				case 'postal_service':
					$this->Install_PostalService();
					break;
				case 'ranking_the_product_service':
					$this->Install_RankingTheProduct();
					break;
				case 'bare':
					#Just install the app.
				default:
					break;
			}

			#Marking the app as installed (MANDATORY).
			$this->Install_App();
		}

		/**
		 * Creates the webhooks in the webshop.
		 *
		 * @throws InvalidJsonException
		 */
		protected function Install_WebHooks() {
			$oWebRequest = new WebRequest();
			$oWebRequest->SetPublicKey($this->Credential->GetApiPublic());
			$oWebRequest->SetSecretKey($this->Credential->GetApiSecret());
			$oWebRequest->SetApiRoot($this->Credential->GetApiRoot());
			$oWebRequest->SetApiResource('/api/rest/v1/webhooks');

			#These webhooks will be created in the webshop. When the event is triggered a payload will be posted to the address.
			$aWebHooksToInstall   = [];
			$aWebHooksToInstall[] = (object) ['event' => 'products.created', 'address' => 'https://demo.securearea.eu/void.php'];
			$aWebHooksToInstall[] = (object) ['event' => 'products.updated', 'address' => 'https://demo.securearea.eu/void.php'];
			$aWebHooksToInstall[] = (object) ['event' => 'products.deleted', 'address' => 'https://demo.securearea.eu/void.php'];

			foreach($aWebHooksToInstall as $oData) {
				$oWebRequest->SetData($oData);
				$sOutput = $oWebRequest->Post();

				$oWebHook = new WebHook(JsonSerializer::DeSerialize($sOutput));
				$oWebHook->SetCustomerId($this->Credential->GetCustomerId());

				#Store WebHook keys
				Data_WebHook::Insert($oWebHook);
			}
		}

		/**
		 * Installing a app code block which places a tracking pixel in the footer on each frontend page.
		 *
		 * @throws \AppConnector\Exceptions\InvalidApiResponse
		 * @throws \AppConnector\Exceptions\InvalidJsonException
		 */
		protected function Install_TrackingPixel() {
			$oWebRequest = new WebRequest();
			#Getting Remote App resource
			$oWebRequest->SetPublicKey($this->Credential->GetApiPublic());
			$oWebRequest->SetSecretKey($this->Credential->GetApiSecret());
			$oWebRequest->SetApiRoot($this->Credential->GetApiRoot());

			$iAppId = $this->GetRemoteAppId();

			#Delete all current app codeblocks already installed for this app. Making it a clean install.
			$oWebRequest->SetApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');

			$sOutput                 = $oWebRequest->Get();
			$aCollectionOfCodeBlocks = JsonSerializer::DeSerialize($sOutput);

			if(isset($aCollectionOfCodeBlocks->items)) {
				foreach($aCollectionOfCodeBlocks->items as $oItem) {
					$oWebRequest->SetApiResource('/api/rest/v1/appcodeblocks/' . $oItem->id);
					$oWebRequest->Delete();
				}
			}

			#Creating new codeblock for the tracking pixel in the footer of each page.
			$oCodeBlock              = new \stdClass();
			$oCodeBlock->placeholder = 'footer';
			$oCodeBlock->value       = '<img src="https://demo.securearea.eu/pixel.php" width="1" height="1" />';

			$oWebRequest->SetApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');
			$oWebRequest->SetData($oCodeBlock);
			$oWebRequest->Post();
		}

		/**
		 * Installs the Postal Service label creator. A merchant can create labels in his order management.
		 *
		 * @throws \AppConnector\Exceptions\InvalidApiResponse
		 * @throws \AppConnector\Exceptions\InvalidJsonException
		 */
		protected function Install_PostalService() {
			$oWebRequest = new WebRequest();
			#Getting Remote App resource
			$oWebRequest->SetPublicKey($this->Credential->GetApiPublic());
			$oWebRequest->SetSecretKey($this->Credential->GetApiSecret());
			$oWebRequest->SetApiRoot($this->Credential->GetApiRoot());

			$iAppId = $this->GetRemoteAppId();

			#Delete all current app codeblocks already installed for this app. Making it a clean install.
			$oWebRequest->SetApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');

			$sOutput                 = $oWebRequest->Get();
			$aCollectionOfCodeBlocks = JsonSerializer::DeSerialize($sOutput);

			if(isset($aCollectionOfCodeBlocks->items)) {
				foreach($aCollectionOfCodeBlocks->items as $oItem) {
					$oWebRequest->SetApiResource('/api/rest/v1/appcodeblocks/' . $oItem->id);
					$oWebRequest->Delete();
				}
			}

			#Creating new codeblock for the send service.
			$sData = file_get_contents('Examples/PostalService/AppCodeBlock.json');

			#Replace demo.securearea.eu for config setting if default scheme is used
			$sData = str_replace("https://demo.securearea.eu", Config::AppUri, $sData);

			$oCodeBlock                      = new \stdClass();
			$oCodeBlock->placeholder         = 'backend-orders-external_connections';
			$oCodeBlock->interactive_content = json_decode($sData);

			$oWebRequest->SetApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');
			$oWebRequest->SetData($oCodeBlock);
			$oWebRequest->Post();
		}

		protected function Install_RankingTheProduct() {
			$oWebRequest = new WebRequest();
			#Getting Remote App resource
			$oWebRequest->SetPublicKey($this->Credential->GetApiPublic());
			$oWebRequest->SetSecretKey($this->Credential->GetApiSecret());
			$oWebRequest->SetApiRoot($this->Credential->GetApiRoot());

			$iAppId = $this->GetRemoteAppId();

			#Delete all current app codeblocks already installed for this app. Making it a clean install.
			$oWebRequest->SetApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');

			$sOutput                 = $oWebRequest->Get();
			$aCollectionOfCodeBlocks = JsonSerializer::DeSerialize($sOutput);

			if(isset($aCollectionOfCodeBlocks->items)) {
				foreach($aCollectionOfCodeBlocks->items as $oItem) {
					$oWebRequest->SetApiResource('/api/rest/v1/appcodeblocks/' . $oItem->id);
					$oWebRequest->Delete();
				}
			}

			#Creating new codeblock for the send service.
			$sData = file_get_contents('Examples/RankingTheProduct/AppCodeBlock.json');

			#Replace demo.securearea.eu for config setting if default scheme is used
			$sData = str_replace("https://demo.securearea.eu", Config::AppUri, $sData);

			$oCodeBlock                      = new \stdClass();
			$oCodeBlock->placeholder         = 'backend-show_product-meta_data';
			$oCodeBlock->interactive_content = json_decode($sData);

			$oWebRequest->SetApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');
			$oWebRequest->SetData($oCodeBlock);
			$oWebRequest->Post();
		}

		/**
		 * Mandatory.
		 * Calls the API and retrieves the App.Id associated with the api_public.
		 * After that a Patch is send to update the app.is_installed property, marking it as installed.
		 *
		 * @throws InvalidApiResponse
		 * @throws InvalidJsonException
		 */
		protected function Install_App() {
			$oWebRequest = new WebRequest();
			#Getting Remote App resource
			$oWebRequest->SetPublicKey($this->Credential->GetApiPublic());
			$oWebRequest->SetSecretKey($this->Credential->GetApiSecret());
			$oWebRequest->SetApiRoot($this->Credential->GetApiRoot());

			#Marking app as 'installed'
			$oApp               = new \stdClass();
			$oApp->is_installed = true;

			$iAppId = $this->GetRemoteAppId();

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
			$this->ValidateHash(Config::AppUninstallUri);

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
		protected function ValidateHash($sUri) {
			$aRequestHeaders = apache_request_headers();

			$oHash  = new Hash();
			$bValid = $oHash->AddData($sUri)->AddData(@file_get_contents('php://input'))->IsValid($aRequestHeaders[Hash::Header_Hash]);

			if($bValid === false) {
				throw new InvalidHashException();
			}
		}

		protected function GetRemoteAppId() {
			if(is_null($this->RemoteAppId)) {
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

				$this->RemoteAppId = $aCollectionOfApps->items[0]->id;
			}

			return $this->RemoteAppId;
		}
	}
