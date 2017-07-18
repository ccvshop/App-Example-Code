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
class Connection extends \mysqli
{

    /**
     * Create a default connection with based on the credentials in the Config.php
     * @return self
     */
    public static function make()
    {
        return new self(Config::DATABASE_HOST, Config::DATABASE_USER, Config::DATABASE_PASSWORD, Config::DATABASE_NAME);
    }

    /**
     * Connection constructor.
     *
     * @param string $sHost     Database hostname
     * @param string $sUser     Database username
     * @param string $sPassword Database password
     * @param string $sDatbase  Database name
     */
    public function __construct($sHost, $sUser, $sPassword, $sDatbase)
    {
        parent::mysqli($sHost, $sUser, $sPassword, $sDatbase);

        if (mysqli_connect_errno()) {
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
    public function select($sQuery)
    {
        $result = $this->query($sQuery);

        $aData = [];
        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {
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
    public function selectOne($sQuery)
    {
        $result = $this->query($sQuery);

        if ($result === false) {
            throw new \Exception($this->error);
        }

        if ($result->num_rows > 0) {
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
    public function result($sQuery)
    {
        $result = $this->query($sQuery);
        if ($result->num_rows > 0) {
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
    public function insert($sTableName, $aData)
    {
        $sQuery  = "INSERT INTO `" . $sTableName . "` (`" . implode('`, `', array_keys($aData)) . "`) VALUES ('" . implode("', '", $this->escape($aData)) . "'); ";
        $mResult = $this->query($sQuery);

        if ($mResult == false) {
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
    public function update($sTableName, $aData, $sFieldname = null, $sMatchingValue = null)
    {

        $sFieldValueString = '';

        foreach ($aData as $sFieldName => $sValue) {
            if ($sFieldValueString != '') {
                $sFieldValueString .= ", ";
            }
            $sFieldValueString .= "`" . $sFieldName . "` = '" . $this->escape($sValue) . "'";
        }

        $sQuery = "UPDATE `" . $sTableName . "` SET " . $sFieldValueString . " ";

        if ($sFieldname !== null && $sMatchingValue !== null) {
            $sQuery .= "WHERE `" . $sFieldname . "` = '" . $this->escape($sMatchingValue) . "'";
        }

        $mResult = $this->query($sQuery);

        if ($mResult == false) {
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
    public function delete($sTableName, $sFieldname = null, $sMatchingValue = null)
    {

        $sQuery = "DELETE FROM `" . $sTableName . "` ";

        if ($sFieldname !== null && $sMatchingValue !== null) {
            $sQuery .= "WHERE `" . $sFieldname . "` = '" . $this->escape($sMatchingValue) . "'";
        }

        $mResult = $this->query($sQuery);

        if ($mResult == false) {
            throw new \Exception($this->error);
        }
    }

    /**
     * Close database connection
     */
    public function __destruct()
    {
        self::close();
    }

    /**
     * Escape data for MySQL
     *
     * @param $mData mixed
     *
     * @return array|string
     */
    public function escape($mData)
    {
        if (is_array($mData)) {
            $aEscapedData = [];
            foreach ($mData as $sKey => $sValue) {
                $aEscapedData[$sKey] = $this->escape_string($sValue);
            }
            return $aEscapedData;
        }

        return $this->escape_string($mData);
    }

}