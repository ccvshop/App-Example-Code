<?php

	namespace PSPConnector;

	use AppConnector\AppConnector;
	use AppConnector\Exceptions\InvalidHashException;
	use AppConnector\Exceptions\InvalidMethod;
	use AppConnector\Http\Hash;

	class PSPConnector {
		/** @var  \AppConnector\AppConnector */
		protected $oAppConnector;

		const PaymethodsUri = 'https://ameijer-app-psp.ccvdev.nl/paymethods';


		public function __construct(){
			$aRequestHeaders = apache_request_headers();

			$oHash  = new Hash();
			$bValid = $oHash->AddData($this::PaymethodsUri)->AddData(@file_get_contents('php://input'))->IsValid($aRequestHeaders[Hash::Header_Hash]);

			if($bValid === false) {
				throw new InvalidHashException();
			}
		}

		public function ProcessPaymethods(){
			switch(
			$_SERVER['REQUEST_METHOD']){
				case 'GET':

					return file_get_contents($_SERVER['DOCUMENT_ROOT'].'/PSP/Paymethods.json');
					break;
				default:
					throw new InvalidMethod($_SERVER['REQUEST_METHOD']. ' is not support for this resource.');
			}
		}
	}