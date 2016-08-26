<?php
	namespace AppConnector;

	use AppConnector\Data\Data_Credential;
	use AppConnector\Exceptions\InvalidHashException;
	use AppConnector\Http\Hash;
	use AppConnector\Log\Log;

	try {
		require_once('../../Config.php');
		require_once('../../AppConnector.php');

		$aRequestHeaders = apache_request_headers();
		$sIncomingData   = @file_get_contents('php://input');

		Log::WriteStartCall(__FILE__);
		Log::Write('Endpoint', 'INPUT_BODY', $sIncomingData);

		#Validate if the data we received is correct and authenticated.
		$sApiPublic      = $aRequestHeaders[\AppConnector\Http\Hash::Header_Public];
		$oCredential     = Data_Credential::GetOneByPublicKey($sApiPublic);

		#Validate if the data we received is correct and authenticated.
		$oIncomingHash = new \AppConnector\Http\Hash($oCredential->GetApiSecret());
		$bValid        =
			$oIncomingHash->AddData(Config::AppUri . $_SERVER['REQUEST_URI'])->AddData($sIncomingData)->IsValid($aRequestHeaders[Hash::Header_Hash]);

		if($bValid === false) {
			throw new InvalidHashException();
		}

		$oObject                  = json_decode($sIncomingData);
		$oObject                  = $oObject->payload;
		$aRank["ProductName"]     = (!empty($oObject->name));
		$aRank["SeoAlias"]        = (!empty($oObject->alias));
		$aRank["MetaTitle"]       = (!empty($oObject->page_title));
		$aRank["MetaDescription"] = (!empty($oObject->meta_description));
		$aRank["MetaKeywords"]    = (!empty($oObject->meta_keywords));
		$aRank["Price"]           = (!empty($oObject->price));
		$aRank["Brand"]           = (isset($oObject->brand->id));
		$aRank["EANNummer"]       = (!empty($oObject->eannumber));
		$aRank["Description"]     = (!empty($oObject->shortdescription));
		$aRank["Stock"]           = (!empty($oObject->stock));

		#Show inital start form.
		$oResponse                 = new \stdClass();
		$oResponse->view           = 'onload';
		$oResponse->data           = [];
		$oResponse->data['intro']  =
			'Ranking the product service gives you a ranking of your product to analyze data used by search engines, the higher the score the better the product';
		$oResponse->data['result'] = '
			<strong>Productnaam:</strong>&nbsp; ' . ($aRank["ProductName"] ? "Ja" : "Nee") . '<br />
			<strong>SEO Alias:</strong>&nbsp;' . ($aRank["SeoAlias"] ? "Ja" : "Nee") . '<br />
			<strong>SEO Meta pagina titel:</strong>&nbsp;' . ($aRank["MetaTitle"] ? "Ja" : "Nee") . '<br />
			<strong>SEO Meta omschrijving:</strong>&nbsp;' . ($aRank["MetaDescription"] ? "Ja" : "Nee") . '<br />
			<strong>SEO Meta keywords:</strong>&nbsp;' . ($aRank["MetaKeywords"] ? "Ja" : "Nee") . '<br />
			<strong>Prijs kwaliteit:</strong>&nbsp;' . ($aRank["Price"] && $aRank["Brand"] ? "Goed" : "Slecht") . '<br />
			<strong>EAN-Nummer:</strong>&nbsp;' . ($aRank["EANNummer"] ? "Goed" : "Slecht") . '<br />
			<strong>Omschrijving:</strong>&nbsp;' . ($aRank["Description"] ? "Goed" : "Slecht") . '<br />
			<strong>Stock:</strong>&nbsp;' . ($aRank["Stock"] ? "Goed" : "Slecht") . '<br /><br />
			<strong>Rank:</strong>&nbsp; ' . array_sum($aRank) . ' / ' . count($aRank) . '
		';

		$sResponse = json_encode($oResponse);
		Log::Write('Endpoint', 'OUTPUT_BODY', $sResponse);

		#Generate output hash, so the webshop can verify it's integrity and authenticate it.
		$oHash = new \AppConnector\Http\Hash($oCredential->GetApiSecret());
		$sHash = $oHash->AddData(Config::AppUri . $_SERVER['REQUEST_URI'])->AddData($sResponse)->Hash();

		header('HTTP/1.1 200 OK', true, 200);
		header('x-hash: ' . $sHash);

		Log::WriteEndCall(__FILE__);

		#Returns data to the webshop.
		echo $sResponse;
		die();
	} catch(\Exception $oEx) {

		Log::Write('Endpoint', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
		Log::WriteEndCall(__FILE__);

		header('HTTP/1.1 500 Internal Server Error', true, 500);
		echo $oEx->getMessage();
		die();
	}
