<?php

	namespace AppConnector\Entities;

	/**
	 *
	 * @author  Adriaan Meijer
	 * @version 1.0    - Initiele opzet
	 *
	 */
	class Transaction {
		/** @var  int */
		protected $amount;
		/** @var  string */
		protected $currency;

		/** @var  string */
		protected $status;

		/** @var  int */
		protected $order_id;
		/** @var  int */
		protected $order_number;

		/** @var  string */
		protected $language;
		/** @var  string */
		protected $method;
		/** @var  string */
		protected $issuer;
		/** @var  string */
		protected $returnUrl;

		/** @var  string */
		protected $payUrl;
		/** @var  string */
		protected $transaction_id;
		/** @var  int */
		protected $created;

		public function __construct(\stdClass $oObject) {
			$this->SetAmount($oObject->amount)
				 ->SetCurrency($oObject->currency)
				 ->SetLanguage($oObject->language)
				 ->SetReturnUrl($oObject->returnUrl)
				 ->SetOrderId($oObject->order_id)
				 ->SetOrderNumber($oObject->order_number)
				 ->SetMethod($oObject->method)
				 ->SetIssuer($oObject->issuer);

			if(empty($oObject->created)) {
				$this->SetCreated(gmdate('r', time()));
			} else {
				$this->SetCreated($oObject->created);
			}

			if(empty($oObject->status)) {
				$this->SetStatus('OPEN');
			} else {
				$this->SetStatus($oObject->status);
			}

			if(empty($oObject->transaction_id)) {
				$this->SetTransactionId(uniqid());
			} else {
				$this->SetTransactionId($oObject->transaction_id);
			}
		}

		/**
		 * Convert this credential object to an array
		 * @return array
		 */
		public function ToArray() {
			return ['amount'         => $this->amount,
					'currency'       => $this->currency,
					'status'         => $this->status,
					'order_id'       => $this->order_id,
					'order_number'   => $this->order_number,
					'language'       => $this->language,
					'method'         => $this->method,
					'issuer'         => $this->issuer,
					'returnUrl'      => $this->returnUrl,
					'payUrl'         => $this->payUrl,
					'transaction_id' => $this->transaction_id,
					'created'        => $this->created,

			];
		}

		/**
		 * @return string
		 */
		public function GetTransactionId() {
			return $this->transaction_id;
		}

		/**
		 * @return int
		 */
		public function GetAmount() {
			return $this->amount;
		}

		/**
		 * @return string
		 */
		public function GetCurrency() {
			return $this->currency;
		}

		/**
		 * @return string
		 */
		public function GetStatus() {
			return $this->status;
		}

		/**
		 * @return int
		 */
		public function GetOrderId() {
			return $this->order_id;
		}

		/**
		 * @return string
		 */
		public function GetOrderNumber() {
			return $this->order_number;
		}

		/**
		 * @return string
		 */
		public function GetLanguage() {
			return $this->language;
		}

		/**
		 * @return string
		 */
		public function GetMethod() {
			return $this->method;
		}

		/**
		 * @return string
		 */
		public function GetIssuer() {
			return $this->issuer;
		}

		/**
		 * @return string
		 */
		public function GetReturnUrl() {
			return $this->returnUrl;
		}

		/**
		 * @return string
		 */
		public function GetPayUrl() {
			return $this->payUrl;
		}

		/**
		 * @return int
		 */
		public function GetCreated() {
			return $this->created;
		}

		/**
		 * @param int $amount
		 *
		 * @return Transaction
		 */
		public function SetAmount($amount) {
			$this->amount = $amount;
			return $this;
		}

		/**
		 * @param string $currency
		 *
		 * @return Transaction
		 */
		public function SetCurrency($currency) {
			$this->currency = $currency;
			return $this;
		}

		/**
		 * @param string $status
		 *
		 * @return Transaction
		 */
		public function SetStatus($status) {
			$this->status = $status;
			return $this;
		}

		/**
		 * @param int $order_id
		 *
		 * @return Transaction
		 */
		public function SetOrderId($order_id) {
			$this->order_id = $order_id;
			return $this;
		}

		/**
		 * @param string $order_number
		 *
		 * @return Transaction
		 */
		public function SetOrderNumber($order_number) {
			$this->order_number = $order_number;
			return $this;
		}

		/**
		 * @param string $language
		 *
		 * @return Transaction
		 */
		public function SetLanguage($language) {
			$this->language = $language;
			return $this;
		}

		/**
		 * @param string $method
		 *
		 * @return Transaction
		 */
		public function SetMethod($method) {
			$this->method = $method;
			return $this;
		}

		/**
		 * @param string $issuer
		 *
		 * @return Transaction
		 */
		public function SetIssuer($issuer) {
			$this->issuer = $issuer;
			return $this;
		}

		/**
		 * @param string $returnUrl
		 *
		 * @return Transaction
		 */
		public function SetReturnUrl($returnUrl) {
			$this->returnUrl = $returnUrl;
			return $this;
		}

		/**
		 * @param string $payUrl
		 *
		 * @return Transaction
		 */
		public function SetPayUrl($payUrl) {
			$this->payUrl = $payUrl;
			return $this;
		}

		/**
		 * @param string $transaction_id
		 *
		 * @return Transaction
		 */
		public function SetTransactionId($transaction_id) {
			$this->transaction_id = $transaction_id;
			return $this;
		}

		/**
		 * @param int $created
		 *
		 * @return Transaction
		 */
		public function SetCreated($created) {
			$this->created = $created;
			return $this;
		}

	}