<?php

	namespace PSPConnector;

	use AppConnector\AppConnector;
	use AppConnector\Exceptions\InvalidMethod;

	class PSPConnector {
		/** @var  \AppConnector\AppConnector */
		protected $oAppConnector;

		const PaymethodsUri = 'https://vertoshop.devdev.nl/paymethods';


		public function __construct(){
			$this->oAppConnector = new AppConnector();

			$this->oAppConnector->ValidateHash($this::PaymethodsUri);
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