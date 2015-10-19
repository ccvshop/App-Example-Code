<?
	namespace AppConnector\Entities;
	/**
	 * Class WebHook
	 *
	 * @package AppConnector\Entities
	 * @author  Adriaan Meijer
	 * @date    2014-10-13
	 * @version 1.0    - First draft
	 */
	class WebHook {
		/**
		 * @var int Internal ID of this WebHook. Used for example purposes only.
		 */
		private $Id = 0;

		/**
		 * @var int Customer ID of this WebHook. Used for example purposes only.
		 */
		private $CustomerId = 0;
		/**
		 * @var string Event of the WebHook, representing an action that takes place on an object is called an event.
		 */
		private $Event = '';
		/**
		 * @var string Address of the WebHook, representing a remote HTTP URI to which the callback will be posted
		 */
		private $Address = '';
		/**
		 * @var string Key is used to validate the integrity of the data send by this WebHook
		 */
		private $Key = '';

		public function __construct(\stdClass $oObject) {
			$this->SetCustomerId($oObject->customer_id);
			$this->SetId($oObject->id);
			$this->SetEvent($oObject->event);
			$this->SetAddress($oObject->address);
			$this->SetKey($oObject->key);
		}

		/**
		 * @return int
		 */
		public function GetId() {
			return $this->Id;
		}

		/**
		 * @param int $Id
		 */
		public function SetId($Id) {
			$this->Id = $Id;
		}

		/**
		 * @return string
		 */
		public function GetAddress() {
			return $this->Address;
		}

		/**
		 * @param string $Address
		 */
		public function SetAddress($Address) {
			$this->Address = $Address;
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
		public function GetEvent() {
			return $this->Event;
		}

		/**
		 * @param string $Event
		 */
		public function SetEvent($Event) {
			$this->Event = $Event;
		}

		/**
		 * @return string
		 */
		public function GetKey() {
			return $this->Key;
		}

		/**
		 * @param string $Key
		 */
		public function SetKey($Key) {
			$this->Key = $Key;
		}
	}