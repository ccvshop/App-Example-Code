<?
	namespace AppConnector\Entities;
	/**
	 * Class Credential
	 * Represents Credential data
	 *
	 * @package AppConnector\Entities
	 * @author  Adriaan Meijer
	 * @date    2014-10-13
	 * @version 1.0    - First draft
	 */
	class Credential {
		/**
		 * @var string Public key used to connect to the api.
		 */
		private $ApiPublic;
		/**
		 * @var string Secret key used to connect to the api
		 */
		private $ApiSecret;
		/**
		 * @var string URI to connect to the api
		 */
		private $ApiRoot;
		/**
		 * @var string Once the user has successfully installed the app, return him here.
		 */
		private $ReturnUrl;
		/**
		 * @var int A Customer Id. Used for example purposes only.
		 */
		private $CustomerId;
		/**
		 * @var string Create date of this Credential. Used for example purposes only.
		 */
		private $CreateDate;

		public function __construct(\stdClass $oObject) {
			$this->SetApiPublic($oObject->api_public);
			$this->SetApiSecret($oObject->api_secret);
			$this->SetApiRoot($oObject->api_root);
			$this->SetReturnUrl($oObject->return_url);
			$this->SetCustomerId($oObject->customer_id);
			$this->SetCreateDate($oObject->create_date);
		}

		/**
		 * Convert this credential object to an array
		 * @return array
		 */
		public function ToArray() {
			return [
				'api_public' => $this->ApiPublic,
				'api_secret' => $this->ApiSecret,
				'api_root' => $this->ApiRoot,
				'return_url' => $this->ReturnUrl,
				'customer_id' => $this->CustomerId,
				'create_date' => $this->CreateDate,
			];
		}

		/**
		 * Convert this credential object to an std object
		 * @return object
		 */
		public function ToStd() {
			return (object)$this->ToArray();
		}

		/**
		 * Print this credential as an array
		 * @return string
		 */
		public function __toString()
		{
			return print_r($this->ToArray(), 1);
		}

		/**
		 * @return string
		 */
		public function GetApiPublic() {
			return $this->ApiPublic;
		}

		/**
		 * @param string $ApiPublic
		 */
		public function SetApiPublic($ApiPublic) {
			$this->ApiPublic = $ApiPublic;
		}

		/**
		 * @return string
		 */
		public function GetApiRoot() {
			return $this->ApiRoot;
		}

		/**
		 * @param string $ApiRoot
		 */
		public function SetApiRoot($ApiRoot) {
			$this->ApiRoot = $ApiRoot;
		}

		/**
		 * @return string
		 */
		public function GetApiSecret() {
			return $this->ApiSecret;
		}

		/**
		 * @param string $ApiSecret
		 */
		public function SetApiSecret($ApiSecret) {
			$this->ApiSecret = $ApiSecret;
		}

		/**
		 * @return string
		 */
		public function GetCreateDate() {
			return $this->CreateDate;
		}

		/**
		 * @param string $CreateDate
		 */
		public function SetCreateDate($CreateDate) {
			$this->CreateDate = $CreateDate;
		}

		/**
		 * @return int
		 */
		public function GetCustomerId() {
			return $this->CustomerId;
		}

		/**
		 * @param int $CustomerId
		 */
		public function SetCustomerId($CustomerId) {
			$this->CustomerId = $CustomerId;
		}

		/**
		 * @return string
		 */
		public function GetReturnUrl() {
			return $this->ReturnUrl;
		}

		/**
		 * @param string $ReturnUrl
		 */
		public function SetReturnUrl($ReturnUrl) {
			$this->ReturnUrl = $ReturnUrl;
		}
	}