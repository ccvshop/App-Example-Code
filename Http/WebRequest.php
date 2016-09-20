<?php
	namespace AppConnector\Http;

	use AppConnector\Exceptions\InvalidApiResponse;
	use AppConnector\Json\JsonSerializer;
	use AppConnector\Log\Log;

	/**
	 * Class WebRequest
	 * Handles all the calls to the REST API.
	 *
	 * @package AppConnector\Http
	 * @author  Adriaan Meijer
	 * @date    2014-10-13
	 * @version 1.0   ::First draft
	 *          1.1   ::Added Logging
	 */
	class WebRequest {
		/**
		 * @var string The "Public key" or "Api key" can be retrieved in the webshop, This should be the same as the header 'x-public'.
		 */
		private $PublicKey = '';

		/**
		 * @var string The "Secret key" or "Api secret" can be retrieved in the webshop.
		 */
		private $SecretKey = '';

		/**
		 * @var string The data that is being posted to the resource (only with POST or PATCH methods)
		 */
		private $Data = '';

		/**
		 * @var string The request URI minus the domain name
		 */
		private $ApiRoot = '';

		/**
		 * @var string The request domain without trailing slash
		 */
		private $ApiResource = '';

		/**
		 * Makes a GET request to the REST API
		 *
		 * @return string
		 * @throws InvalidApiResponse
		 */
		public function Get() {
			#HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
			$sMethod    = 'GET';
			$sTimeStamp = gmdate('c');

			#Creating the hash
			$sHashString = implode('|', array($this->GetPublicKey(),
											  $sMethod,
											  $this->GetApiResource(),
											  '',
											  $sTimeStamp,));

			$sHash = hash_hmac('sha512', $sHashString, $this->GetSecretKey());

			$rCurlHandler = curl_init();
			curl_setopt($rCurlHandler, CURLOPT_URL, $this->GetApiRoot() . $this->GetApiResource());
			curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
			curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

			curl_setopt($rCurlHandler, CURLOPT_HTTPHEADER,
						array(
							"x-date: " . $sTimeStamp,
							"x-hash: " . $sHash,
							"x-public: " . $this->GetPublicKey(),
							"Content-Type: text/json",
						)
			);

			$sOutput   = curl_exec($rCurlHandler);
			$iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
			curl_close($rCurlHandler);

			Log::Write('WebRequest', 'GET::REQUEST', $this->GetApiRoot() . $this->GetApiResource());
			Log::Write('WebRequest', 'GET::HTTPCODE', $iHTTPCode);
			Log::Write('WebRequest', 'GET::RESPONSE', $sOutput);

			if($iHTTPCode !== 200) {
				throw new InvalidApiResponse('HttpCode was ' . $iHTTPCode . '. Expected 200');
			}

			return $sOutput;
		}

		/**
		 * Makes a DELETE request to the REST API
		 *
		 * @return string
		 * @throws InvalidApiResponse
		 */
		public function Delete() {
			#HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
			$sMethod    = 'DELETE';
			$sTimeStamp = gmdate('c');

			#Creating the hash
			$sHashString = implode('|', array($this->GetPublicKey(),
											  $sMethod,
											  $this->GetApiResource(),
											  $this->GetData(),
											  $sTimeStamp,));

			$sHash = hash_hmac('sha512', $sHashString, $this->GetSecretKey());

			$rCurlHandler = curl_init();
			curl_setopt($rCurlHandler, CURLOPT_URL, $this->GetApiRoot() . $this->GetApiResource());
			curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
			curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

			curl_setopt($rCurlHandler, CURLOPT_HTTPHEADER,
						array(
							"x-date: " . $sTimeStamp,
							"x-hash: " . $sHash,
							"x-public: " . $this->GetPublicKey(),
							"Content-Type: text/json",
						)
			);
			$sOutput   = curl_exec($rCurlHandler);
			$iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
			curl_close($rCurlHandler);

			Log::Write('WebRequest', 'DELETE::REQUEST', $this->GetApiRoot() . $this->GetApiResource());
			Log::Write('WebRequest', 'DELETE::HTTPCODE', $iHTTPCode);
			Log::Write('WebRequest', 'DELETE::RESPONSE', $sOutput);

			if($iHTTPCode !== 204) {
				throw new InvalidApiResponse('HttpCode was ' . $iHTTPCode . '. Expected 204');
			}
			return $sOutput;
		}

		/**
		 * Makes a POST request to the REST API
		 *
		 * @return string
		 * @throws InvalidApiResponse
		 */
		public function Post() {
			#HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
			$sMethod    = 'POST';
			$sTimeStamp = gmdate('c');

			#Creating the hash
			$sHashString = implode('|', array($this->GetPublicKey(),
											  $sMethod,
											  $this->GetApiResource(),
											  $this->GetData(),
											  $sTimeStamp,));

			$sHash = hash_hmac('sha512', $sHashString, $this->GetSecretKey());

			$rCurlHandler = curl_init();
			curl_setopt($rCurlHandler, CURLOPT_URL, $this->GetApiRoot() . $this->GetApiResource());
			curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($rCurlHandler, CURLOPT_POSTFIELDS, $this->GetData());
			curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
			curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

			curl_setopt($rCurlHandler, CURLOPT_HTTPHEADER,
						array(
							"x-date: " . $sTimeStamp,
							"x-hash: " . $sHash,
							"x-public: " . $this->GetPublicKey(),
							"Content-Type: text/json",
						)
			);
			$sOutput   = curl_exec($rCurlHandler);
			$iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
			curl_close($rCurlHandler);

			Log::Write('WebRequest', 'POST::REQUEST', $this->GetApiRoot() . $this->GetApiResource());
			Log::Write('WebRequest', 'POST::DATA', $this->GetData());
			Log::Write('WebRequest', 'POST::HTTPCODE', $iHTTPCode);
			Log::Write('WebRequest', 'POST::RESPONSE', $sOutput);

			$this->SetData('');

			if(!in_array($iHTTPCode, array(200, 201))) {
				throw new InvalidApiResponse('HttpCode was ' . $iHTTPCode . '. Expected 200|201 on [POST] '. $this->GetApiRoot() . $this->GetApiResource());
			}
			return $sOutput;
		}

		/**
		 * Makes a PATCH request to the REST API
		 *
		 * @return string
		 * @throws InvalidApiResponse
		 */
		public function Patch() {
			#HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
			$sMethod    = 'PATCH';
			$sTimeStamp = gmdate('c');

			#Creating the hash
			$sHashString = implode('|', array($this->GetPublicKey(),
											  $sMethod,
											  $this->GetApiResource(),
											  $this->GetData(),
											  $sTimeStamp,));

			$sHash = hash_hmac('sha512', $sHashString, $this->GetSecretKey());

			$rCurlHandler = curl_init();
			curl_setopt($rCurlHandler, CURLOPT_URL, $this->GetApiRoot() . $this->GetApiResource());
			curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($rCurlHandler, CURLOPT_POSTFIELDS, $this->GetData());
			curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
			curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

			curl_setopt($rCurlHandler, CURLOPT_HTTPHEADER,
						array(
							"x-date: " . $sTimeStamp,
							"x-hash: " . $sHash,
							"x-public: " . $this->GetPublicKey(),
							"Content-Type: text/json",
						)
			);
			$sOutput   = curl_exec($rCurlHandler);
			$iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
			curl_close($rCurlHandler);

			Log::Write('WebRequest', 'PATCH::REQUEST', $this->GetApiRoot() . $this->GetApiResource());
			Log::Write('WebRequest', 'PATCH::DATA', $this->GetData());
			Log::Write('WebRequest', 'PATCH::HTTPCODE', $iHTTPCode);
			Log::Write('WebRequest', 'PATCH::RESPONSE', $sOutput);

			$this->SetData('');

			if($iHTTPCode !== 204) {
				throw new InvalidApiResponse('HttpCode was ' . $iHTTPCode . '. Expected 204');
			}
			return $sOutput;
		}

		/**
		 * The request domain without trailing slash
		 *
		 * @return string
		 */
		public function GetApiResource() {
			return $this->ApiResource;
		}

		/**
		 * The request domain without trailing slash
		 *
		 * @param string $ApiResource
		 */
		public function SetApiResource($ApiResource) {
			$this->ApiResource = $ApiResource;
		}

		/**
		 * The request URI minus the domain name
		 *
		 * @return string
		 */
		public function GetApiRoot() {
			return $this->ApiRoot;
		}

		/**
		 * The request URI minus the domain name
		 *
		 * @param string $ApiRoot
		 */
		public function SetApiRoot($ApiRoot) {
			$this->ApiRoot = $ApiRoot;
		}

		/**
		 * The data that is being posted to the resource (only with POST or PATCH methods)
		 *
		 * @return string
		 */
		public function GetData() {
			return $this->Data;
		}

		/**
		 * The data that is being posted to the resource (only with POST or PATCH methods)
		 *
		 * @param string $Data
		 */
		public function SetData($Data) {
			$this->Data = JsonSerializer::Serialize($Data);
		}

		/**
		 * The "Public key" or "Api key" can be retrieved in the webshop, This should be the same as the header 'x-public'.
		 *
		 * @return string
		 */
		public function GetPublicKey() {
			return $this->PublicKey;
		}

		/**
		 * The "Public key" or "Api key" can be retrieved in the webshop, This should be the same as the header 'x-public'.
		 *
		 * @param string $PublicKey
		 */
		public function SetPublicKey($PublicKey) {
			$this->PublicKey = $PublicKey;
		}

		/**
		 * The "Secret key" or "Api secret" can be retrieved in the webshop.
		 *
		 * @return string
		 */
		public function GetSecretKey() {
			return $this->SecretKey;
		}

		/**
		 * The "Secret key" or "Api secret" can be retrieved in the webshop.
		 *
		 * @param string $SecretKey
		 */
		public function SetSecretKey($SecretKey) {
			$this->SecretKey = $SecretKey;
		}
	}
