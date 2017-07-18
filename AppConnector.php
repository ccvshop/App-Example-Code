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
class AppConnector
{
    /**
     * @var Credential Credential Contains the credentials. Used for example purposes only
     */
    protected $credential;

    /**
     * @var null|int Contains the ID
     */
    protected $remoteAppId = null;

    public function __construct()
    {
        if (is_null(Config::APP_SECRET_KEY)) {
            throw new \Exception('AppSecretKey is empty. Please config Config.php');
        }

        if (is_null(Config::APP_HANDSHAKE_URI)) {
            throw new \Exception('AppHandshakeUri is empty. Please config Config.php');
        }

        if (is_null(Config::APP_UNINSTALL_URI)) {
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
    public function processCredentials()
    {
        Log::write(__FUNCTION__, 'START');
        $this->validateHash(Config::APP_HANDSHAKE_URI);

        $oData = JsonSerializer::deSerialize(@file_get_contents('php://input'));

        $this->credential = new Credential($oData);
        Data_Credential::insert($this->credential);
        Log::write(__FUNCTION__, 'END');
    }

    /**
     * Once the customer has successfully filled in the form, we proceed with the installation.
     * Creating the needed WebHooks in the webshop and marking the app as installed.
     *
     * @throws InvalidApiResponse
     * @throws InvalidCredentialException
     */
    public function install()
    {
        $sApiPublic       = $_REQUEST['api_public'];
        $this->credential = Data_Credential::getOneByPublicKey($sApiPublic);
        $this->credential->setCustomerId($_REQUEST['customer_id']);

        Data_Credential::update($this->credential);

        switch ($_REQUEST['install_type']) {
            case 'webhooks':
                $this->installWebHooks();
                break;
            case 'tracking_pixel':
                $this->installTrackingPixel();
                break;
            case 'postal_service':
                $this->installPostalService();
                break;
            case 'ranking_the_product_service':
                $this->installRankingTheProduct();
                break;
            case 'app_psp':
                $this->installAppPSP();
                break;
            case 'language':
                $this->installLanguage();
                break;
            case 'bare':
                #Just install the app.
            default:
                break;
        }

        #Marking the app as installed (MANDATORY).
        $this->installApp();
    }

    /**
     * Creates the webhooks in the webshop.
     *
     * @throws InvalidJsonException
     */
    protected function installWebHooks()
    {
        $oWebRequest = new WebRequest();
        $oWebRequest->setPublicKey($this->credential->getApiPublic());
        $oWebRequest->setSecretKey($this->credential->getApiSecret());
        $oWebRequest->setApiRoot($this->credential->getApiRoot());
        $oWebRequest->setApiResource('/api/rest/v1/webhooks');

        #These webhooks will be created in the webshop. When the event is triggered a payload will be posted to the address.
        $aWebHooksToInstall   = [];
        $aWebHooksToInstall[] = (object)['event' => 'products.created', 'address' => 'https://demo.securearea.eu/void.php'];
        $aWebHooksToInstall[] = (object)['event' => 'products.updated', 'address' => 'https://demo.securearea.eu/void.php'];
        $aWebHooksToInstall[] = (object)['event' => 'products.deleted', 'address' => 'https://demo.securearea.eu/void.php'];

        foreach ($aWebHooksToInstall as $oData) {
            $oWebRequest->setData($oData);
            $sOutput = $oWebRequest->post();

            $oWebHook = new WebHook(JsonSerializer::deSerialize($sOutput));
            $oWebHook->setCustomerId($this->credential->getCustomerId());

            #Store WebHook keys
            Data_WebHook::insert($oWebHook);
        }
    }

    protected function installLanguage()
    {
        #First we need to create a new language.
        $oWebRequest = new WebRequest();
        $oWebRequest->setPublicKey($this->credential->getApiPublic());
        $oWebRequest->setSecretKey($this->credential->getApiSecret());
        $oWebRequest->setApiRoot($this->credential->getApiRoot());
        $oWebRequest->setApiResource('/api/rest/v1/languages/');

        #We'll be creating Pirate language.
        $oLanguage                = new \stdClass();
        $oLanguage->label         = "Pirate";
        $oLanguage->base_language = "en";
        $oLanguage->flag_icon     = "pi";

        $oWebRequest->setData($oLanguage);
        try {
            $sOutput         = $oWebRequest->post();
            $oPirateLanguage = Json\JsonSerializer::deSerialize($sOutput);
        } catch (InvalidApiResponse $e) {
            return false;
        }

        //Now we can add the translation. $aPirate includes all keys and translations.
        $aPirate = [];
        include_once('Data/Pirate.php');

        $oWebRequest = new WebRequest();
        $oWebRequest->setPublicKey($this->credential->getApiPublic());
        $oWebRequest->setSecretKey($this->credential->getApiSecret());
        $oWebRequest->setApiRoot($this->credential->getApiRoot());
        $oWebRequest->setApiResource('/api/rest/v1/translations/');
        $oWebRequest->setAcceptLanguage($oPirateLanguage->iso_code);

        $i                     = 0;
        $oObject               = new \stdClass();
        $oObject->translations = [];
        foreach ($aPirate as $sKey => $sValue) {

            $oTranslation        = new \stdClass();
            $oTranslation->key   = $sKey;
            $oTranslation->value = $sValue;

            $oObject->translations[] = $oTranslation;

            if ($i > 100) {
                $oWebRequest->setData($oObject);
                $oWebRequest->put();

                $oObject->translations = [];
                $i                     = 0;
            }
            $i++;
        }
        if (!empty($oObject->translations)) {
            $oWebRequest->setData($oObject);
            $oWebRequest->put();
        }
        return true;
    }

    /**
     * Installing a app code block which places a tracking pixel in the footer on each frontend page.
     *
     * @throws \AppConnector\Exceptions\InvalidApiResponse
     * @throws \AppConnector\Exceptions\InvalidJsonException
     */
    protected function installTrackingPixel()
    {
        $oWebRequest = new WebRequest();
        #Getting Remote App resource
        $oWebRequest->setPublicKey($this->credential->getApiPublic());
        $oWebRequest->setSecretKey($this->credential->getApiSecret());
        $oWebRequest->setApiRoot($this->credential->getApiRoot());

        $iAppId = $this->getRemoteAppId();

        #Delete all current app codeblocks already installed for this app. Making it a clean install.
        $oWebRequest->setApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');

        $sOutput                 = $oWebRequest->get();
        $aCollectionOfCodeBlocks = JsonSerializer::deSerialize($sOutput);

        if (isset($aCollectionOfCodeBlocks->items)) {
            foreach ($aCollectionOfCodeBlocks->items as $oItem) {
                $oWebRequest->setApiResource('/api/rest/v1/appcodeblocks/' . $oItem->id);
                $oWebRequest->delete();
            }
        }

        #Creating new codeblock for the tracking pixel in the footer of each page.
        $oCodeBlock              = new \stdClass();
        $oCodeBlock->placeholder = 'footer';
        $oCodeBlock->value       = '<img src="https://demo.securearea.eu/pixel.php" width="1" height="1" />';

        $oWebRequest->setApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');
        $oWebRequest->setData($oCodeBlock);
        $oWebRequest->post();
    }

    /**
     * Installs the Postal Service label creator. A merchant can create labels in his order management.
     *
     * @throws \AppConnector\Exceptions\InvalidApiResponse
     * @throws \AppConnector\Exceptions\InvalidJsonException
     */
    protected function installPostalService()
    {
        $oWebRequest = new WebRequest();
        #Getting Remote App resource
        $oWebRequest->setPublicKey($this->credential->getApiPublic());
        $oWebRequest->setSecretKey($this->credential->getApiSecret());
        $oWebRequest->setApiRoot($this->credential->getApiRoot());

        $iAppId = $this->getRemoteAppId();

        #Delete all current app codeblocks already installed for this app. Making it a clean install.
        $oWebRequest->setApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');

        $sOutput                 = $oWebRequest->get();
        $aCollectionOfCodeBlocks = JsonSerializer::deSerialize($sOutput);

        if (isset($aCollectionOfCodeBlocks->items)) {
            foreach ($aCollectionOfCodeBlocks->items as $oItem) {
                $oWebRequest->setApiResource('/api/rest/v1/appcodeblocks/' . $oItem->id);
                $oWebRequest->delete();
            }
        }

        #Creating new codeblock for the send service.
        $sData = file_get_contents('Examples/PostalService/AppCodeBlock.json');

        #Replace demo.securearea.eu for config setting if default scheme is used
        $sData = str_replace("https://demo.securearea.eu", Config::APP_URI, $sData);

        $oCodeBlock                      = new \stdClass();
        $oCodeBlock->placeholder         = 'backend-orders-external_connections';
        $oCodeBlock->interactive_content = json_decode($sData);

        $oWebRequest->setApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');
        $oWebRequest->setData($oCodeBlock);
        $oWebRequest->post();
    }

    protected function installRankingTheProduct()
    {
        $oWebRequest = new WebRequest();
        #Getting Remote App resource
        $oWebRequest->setPublicKey($this->credential->getApiPublic());
        $oWebRequest->setSecretKey($this->credential->getApiSecret());
        $oWebRequest->setApiRoot($this->credential->getApiRoot());

        $iAppId = $this->getRemoteAppId();

        #Delete all current app codeblocks already installed for this app. Making it a clean install.
        $oWebRequest->setApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');

        $sOutput                 = $oWebRequest->get();
        $aCollectionOfCodeBlocks = JsonSerializer::deSerialize($sOutput);

        if (isset($aCollectionOfCodeBlocks->items)) {
            foreach ($aCollectionOfCodeBlocks->items as $oItem) {
                $oWebRequest->setApiResource('/api/rest/v1/appcodeblocks/' . $oItem->id);
                $oWebRequest->delete();
            }
        }

        #Creating new codeblock for the send service.
        $sData = file_get_contents('Examples/RankingTheProduct/AppCodeBlock.json');

        #Replace demo.securearea.eu for config setting if default scheme is used
        $sData = str_replace("https://demo.securearea.eu", Config::APP_URI, $sData);

        $oCodeBlock                      = new \stdClass();
        $oCodeBlock->placeholder         = 'backend-show_product-meta_data';
        $oCodeBlock->interactive_content = json_decode($sData);

        $oWebRequest->setApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');
        $oWebRequest->setData($oCodeBlock);
        $oWebRequest->post();
    }

    protected function installAppPSP()
    {
        $oWebRequest = new WebRequest();
        #Getting Remote App resource
        $oWebRequest->setPublicKey($this->credential->getApiPublic());
        $oWebRequest->setSecretKey($this->credential->getApiSecret());
        $oWebRequest->setApiRoot($this->credential->getApiRoot());
        $oWebRequest->setApiResource('/api/rest/v1/apps');
        $sOutput = $oWebRequest->get();

        $aCollectionOfApps = JsonSerializer::deSerialize($sOutput);

        if (!isset($aCollectionOfApps->items)) {
            throw new InvalidApiResponse('Collection contained zero apps. Expected 1.');
        }

        if (count($aCollectionOfApps->items) > 1) {
            throw new InvalidApiResponse('Collection contained ' . count($aCollectionOfApps->items) . ' apps. Expected 1.');
        }
        $iAppId = $aCollectionOfApps->items[0]->id;

        #Marking app as 'installed'
        $oAppPSP              = new \stdClass();
        $oAppPSP->name        = 'Smoke & Mirrors PSP';
        $oAppPSP->description = 'Smoke & Mirrors PSP is installed via the App Store.';
        $oAppPSP->endpoint    = Config::APP_URI;
        $oAppPSP->paymethods  = json_decode(file_get_contents('Examples/PSP/Paymethods.json'));

        $oWebRequest->setApiResource('/api/rest/v1/apps/' . $iAppId . '/apppsp/');
        $oWebRequest->setData($oAppPSP);
        $oWebRequest->post();
    }

    /**
     * Mandatory.
     * Calls the API and retrieves the App.Id associated with the api_public.
     * After that a Patch is send to update the app.is_installed property, marking it as installed.
     *
     * @throws InvalidApiResponse
     * @throws InvalidJsonException
     */
    protected function installApp()
    {
        $oWebRequest = new WebRequest();
        #Getting Remote App resource
        $oWebRequest->setPublicKey($this->credential->getApiPublic());
        $oWebRequest->setSecretKey($this->credential->getApiSecret());
        $oWebRequest->setApiRoot($this->credential->getApiRoot());

        #Marking app as 'installed'
        $oApp               = new \stdClass();
        $oApp->is_installed = true;

        $iAppId = $this->getRemoteAppId();

        $oWebRequest->setApiResource('/api/rest/v1/apps/' . $iAppId);
        $oWebRequest->setData($oApp);
        $oWebRequest->patch();
    }

    /**
     * Optional.
     * Just clears up some of the local data files.
     *
     * @throws InvalidCredentialException
     * @throws InvalidHashException
     * @throws InvalidJsonException
     */
    public function unInstall()
    {
        $this->validateHash(Config::APP_UNINSTALL_URI);

        $oPostedData      = JsonSerializer::deSerialize(@file_get_contents('php://input'));
        $this->credential = Data_Credential::getOneByPublicKey($oPostedData->api_public);

        $aWebHooks = Data_WebHook::getAllByCustomerId($this->credential->getCustomerId());

        /** @var WebHook $oWebHook */
        foreach ($aWebHooks as $oWebHook) {
            Data_WebHook::delete($oWebHook);
        }

        Data_Credential::delete($this->getCredential());
    }

    /**
     * @return Credential
     * @throws InvalidCredentialException
     */
    public function getCredential()
    {
        if (!is_a($this->credential, 'AppConnector\Entities\Credential')) {
            throw new InvalidCredentialException();
        }
        return $this->credential;
    }

    /**
     * Validates the hash in the header with the calculated hash. Check data integrity.
     *
     * @param $sUri
     *
     * @throws InvalidHashException
     */
    protected function validateHash($sUri)
    {
        $aRequestHeaders = apache_request_headers();

        $oHash  = new Hash();
        $bValid = $oHash->addData($sUri)->addData(@file_get_contents('php://input'))->isValid($aRequestHeaders[Hash::Header_Hash]);

        if ($bValid === false) {
            throw new InvalidHashException();
        }
    }

    protected function getRemoteAppId()
    {
        if (is_null($this->remoteAppId)) {
            $oWebRequest = new WebRequest();
            #Getting Remote App resource
            $oWebRequest->setPublicKey($this->credential->getApiPublic());
            $oWebRequest->setSecretKey($this->credential->getApiSecret());
            $oWebRequest->setApiRoot($this->credential->getApiRoot());
            $oWebRequest->setApiResource('/api/rest/v1/apps');
            $sOutput = $oWebRequest->get();

            $aCollectionOfApps = JsonSerializer::deSerialize($sOutput);

            if (!isset($aCollectionOfApps->items)) {
                throw new InvalidApiResponse('Collection contained zero apps. Expected 1.');
            }

            if (count($aCollectionOfApps->items) > 1) {
                throw new InvalidApiResponse('Collection contained ' . count($aCollectionOfApps->items) . ' apps. Expected 1.');
            }

            $this->remoteAppId = $aCollectionOfApps->items[0]->id;
        }

        return $this->remoteAppId;
    }
}
