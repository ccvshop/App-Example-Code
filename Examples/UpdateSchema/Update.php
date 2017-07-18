<?php
/**
 * CLI Script for updating all views of all customers
 *
 * User: Nick Postma
 * Date: 13-6-2016
 * Time: 12:11
 */

namespace AppConnector;

use AppConnector\Entities\Credential;
use AppConnector\Exceptions\InvalidApiResponse;

require_once(__DIR__ . '/../../AppConnector.php');

# Get all customers
$oCrentalData = new \AppConnector\Data\Data_Credential();
$aCrentials   = $oCrentalData->getAll();

# Loop through all customers
/**
 * @var int        $i
 * @var Credential $oCredential
 */
foreach ($aCrentials as $i => $oCredential) {

    $oWebRequest = new \AppConnector\Http\WebRequest();
    $oWebRequest->setPublicKey($oCredential->getApiPublic());
    $oWebRequest->setSecretKey($oCredential->getApiSecret());
    $oWebRequest->setApiRoot($oCredential->getApiRoot());
    $oWebRequest->setApiResource('/api/rest/v1/apps');
    $sOutput = $oWebRequest->get();

    $aCollectionOfApps = \AppConnector\Json\JsonSerializer::deSerialize($sOutput);

    if (!isset($aCollectionOfApps->items)) {
        throw new InvalidApiResponse('Collection contained zero apps. Expected 1.');
    }

    if (count($aCollectionOfApps->items) > 1) {
        throw new InvalidApiResponse('Collection contained ' . count($aCollectionOfApps->items) . ' apps. Expected 1.');
    }

    $iAppId = $aCollectionOfApps->items[0]->id;

    echo "App found :: " . $iAppId . "\n";

    #Delete all current app codeblocks already installed for this app. There is no way to Patch! So delete and insert.
    #You sould probably keep track of the installed codeblocks somehow. This is not included in the example APP
    $oWebRequest->setApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');

    $sOutput                 = $oWebRequest->get();
    $aCollectionOfCodeBlocks = \AppConnector\Json\JsonSerializer::deSerialize($sOutput);

    if (isset($aCollectionOfCodeBlocks->items)) {
        foreach ($aCollectionOfCodeBlocks->items as $oItem) {
            echo "Deleting codeblock " . $oItem->id . "\n";
            $oWebRequest->setApiResource('/api/rest/v1/appcodeblocks/' . $oItem->id);
            $oWebRequest->delete();
        }
    }

    #Inserting new version of the codeblock
    $oWebRequest = new \AppConnector\Http\WebRequest();
    $oWebRequest->setPublicKey($oCredential->getApiPublic());
    $oWebRequest->setSecretKey($oCredential->getApiSecret());
    $oWebRequest->setApiRoot($oCredential->getApiRoot());

    $sData = file_get_contents(__DIR__ . '/AppCodeBlockV2.json');

    #Replace demo.securearea.eu for config setting if default scheme is used
    $sData = str_replace("https://demo.securearea.eu", Config::APP_URI, $sData);

    $oCodeBlock                      = new \stdClass();
    $oCodeBlock->placeholder         = 'backend-orders-external_connections';
    $oCodeBlock->interactive_content = json_decode($sData);

    $oWebRequest->setApiResource('/api/rest/v1/apps/' . $iAppId . '/appcodeblocks');
    $oWebRequest->setData($oCodeBlock);
    $oWebRequest->post();
}

