<?php

	namespace AppConnector\Sql;
	use AppConnector\Config;

	/**
	 * Class Connection
	 * Handles all configurations
	 *
	 * @package Sql
	 * @author  Nick Postma
	 * @date    2016-06-13
	 * @version 1.0    - First draft
	 **/

	class Connection extends \mysqli {

		/**
		 * Create a default connection with based on the credentials in the Config.php
		 * @return self
		 */
		public static function Make() {
			return new self(Config::DatabaseHost, Config::DatabaseUsername, Config::DatabasePassword, Config::DatabaseName);
		}

		/**
		 * Connection constructor.
		 *
		 * @param string $sHost     Database hostname
		 * @param string $sUser     Database username
		 * @param string $sPassword Database password
		 * @param string $sDatbase  Database name
		 */
		public function __construct($sHost, $sUser, $sPassword, $sDatbase) {
			parent::mysqli($sHost, $sUser, $sPassword, $sDatbase);

			if(mysqli_connect_errno()) {
				printf("Connect failed: %s\n", mysqli_connect_error());
				exit();
			}

			$this->set_charset("utf8");
		}

		/**
		 * Select associative data by a query
		 *
		 * @param $sQuery
		 *
		 * @return array
		 */
		public function Select($sQuery) {
			$result = $this->query($sQuery);

			$aData = [];
			if($result->num_rows > 0) {
				// output data of each row
				while($row = $result->fetch_assoc()) {
					$aData[] = $row;
				}
			}
			return $aData;
		}

		/**
		 * Select one row by a query
		 *
		 * @param string $sQuery
		 *
		 * @return array|null
		 * @throws \Exception
		 */
		public function SelectOne($sQuery) {
			$result = $this->query($sQuery);

			if($result === false) {
				throw new \Exception($this->error);
			}

			if($result->num_rows > 0) {
				// output data of one row
				return $result->fetch_assoc();
			}
			return null;
		}

		/**
		 * Select one field from one row
		 *
		 * @param $sQuery
		 *
		 * @return mixed|null
		 */
		public function Result($sQuery) {
			$result = $this->query($sQuery);
			if($result->num_rows > 0) {
				// output data of one row
				$aRow  = $result->fetch_assoc();
				$mData = array_pop($aRow);
				return $mData;
			}
			return null;
		}

		/**
		 * Insert data into a table
		 *
		 * @param $sTableName
		 * @param $aData
		 *
		 * @return mixed
		 * @throws \Exception
		 */
		public function Insert($sTableName, $aData) {
			$sQuery  =
				"INSERT INTO `" . $sTableName . "` (`" . implode('`, `', array_keys($aData)) . "`) VALUES ('" . implode("', '", $this->Escape($aData)) . "'); ";
			$mResult = $this->query($sQuery);

			if($mResult == false) {
				throw new \Exception($this->error);
			}

			return $this->insert_id;
		}

		/**
		 * Insert data into a table
		 *
		 * @param      $sTableName
		 * @param      $aData
		 * @param null $sFieldname
		 * @param null $sMatchingValue
		 *
		 * @return mixed
		 * @throws \Exception
		 */
		public function Update($sTableName, $aData, $sFieldname = null, $sMatchingValue = null) {

			$sFieldValueString = '';

			foreach($aData as $sFieldName => $sValue) {
				if($sFieldValueString != '') {
					$sFieldValueString .= ", ";
				}
				$sFieldValueString .= "`" . $sFieldName . "` = '" . $this->Escape($sValue) . "'";
			}

			$sQuery = "UPDATE `" . $sTableName . "` SET " . $sFieldValueString . " ";

			if($sFieldname !== null && $sMatchingValue !== null) {
				$sQuery .= "WHERE `" . $sFieldname . "` = '" . $this->Escape($sMatchingValue) . "'";
			}

			$mResult = $this->query($sQuery);

			if($mResult == false) {
				throw new \Exception($this->error);
			}
		}

		/**
		 * Insert data into a table
		 *
		 * @param      $sTableName
		 * @param null $sFieldname
		 * @param null $sMatchingValue
		 *
		 * @return mixed
		 * @throws \Exception
		 */
		public function Delete($sTableName, $sFieldname = null, $sMatchingValue = null) {

			$sQuery = "DELETE FROM `" . $sTableName . "` ";

			if($sFieldname !== null && $sMatchingValue !== null) {
				$sQuery .= "WHERE `" . $sFieldname . "` = '" . $this->Escape($sMatchingValue) . "'";
			}

			$mResult = $this->query($sQuery);

			if($mResult == false) {
				throw new \Exception($this->error);
			}
		}

		/**
		 * Close database connection
		 */
		public function __destruct() {
			self::close();
		}

		/**
		 * Escape data for MySQL
		 *
		 * @param $mData mixed
		 *
		 * @return array|string
		 */
		public function Escape($mData) {
			if(is_array($mData)) {
				$aEscapedData = [];
				foreach($mData as $sKey => $sValue) {
					$aEscapedData[$sKey] = $this->escape_string($sValue);
				}
				return $aEscapedData;
			}

			return $this->escape_string($mData);
		}

	}