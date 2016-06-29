<?php

	namespace AppConnector\Http;

	use AppConnector\Config;
	use AppConnector\Log\Log;

	/**
	 *
	 * Creates an hash based on the given inputs for authentication and integretity checks in the API
	 * @author  Adriaan Meijer
	 * @version 1.0    - Initial creation
	 *
	 */
	class Hash {
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
		 * Collection of data to be hashed.
		 * @var array
		 */
		protected $aDataToHash = [];

		public function AddData($sData) {
			if(is_string($sData)) {
				$this->aDataToHash[] = $sData;
			}
			return $this;
		}

		/**
		 * Returns the calculated hash
		 * @return string
		 */
		public function Hash() {
			$sStringToHash = implode($this::Hash_Field_Separator, $this->aDataToHash);
			$sHash         = hash_hmac($this::Hash_Encryption, $sStringToHash, Config::AppSecretKey);
			Log::Write('Hash::Hash', 'DATA', $sStringToHash);
			Log::Write('Hash::Hash', 'GENERATE', $sHash);
			return $sHash;
		}

		public function IsValid($sExpectedHash = '') {
			Log::Write('Hash::IsValid', 'VALIDATE', $sExpectedHash);
			if(!is_string($sExpectedHash)) {
				return false;
			}
			if($sExpectedHash === $this->Hash()) {
				Log::Write('Hash::IsValid', 'VALID', 'OK');
				return true;
			}
			return false;
		}
	}