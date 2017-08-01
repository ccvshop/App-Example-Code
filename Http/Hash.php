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
class Hash
{
    /**
     * This is the field in the header of each request that contains the hash. Do NOT change this unless instructed by CCV.
     */
    const Header_Public = 'x-public';
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
     * Public key to valid with. Normally this is the App Secret Key, but could be the API credential Secret.
     * @var null|string
     */
    protected $sSecretKey = null;

    /**
     * Collection of data to be hashed.
     * @var array
     */
    protected $aDataToHash = [];

    public function __construct($sSecretKey = null)
    {
        if (is_null($sSecretKey)) {
            $this->sSecretKey = Config::APP_SECRET_KEY;
        } else {
            $this->sSecretKey = $sSecretKey;
        }
    }

    public function addData($sData)
    {
        if (is_string($sData)) {
            $this->aDataToHash[] = $sData;
        }
        return $this;
    }

    /**
     * Returns the calculated hash
     * @return string
     */
    public function hash()
    {
        $sStringToHash = implode($this::Hash_Field_Separator, $this->aDataToHash);
        $sHash         = hash_hmac($this::Hash_Encryption, $sStringToHash, $this->sSecretKey);

        Log::write('Hash::Hash', 'DATA', $sStringToHash);
        Log::write('Hash::Hash', 'GENERATE', $sHash);
        return $sHash;
    }

    public function isValid($sExpectedHash = '')
    {
        Log::write('Hash::IsValid', 'VALIDATE', $sExpectedHash);
        if (!is_string($sExpectedHash)) {
            return false;
        }
        if ($sExpectedHash === $this->hash()) {
            Log::write('Hash::IsValid', 'VALID', 'OK');
            return true;
        }
        Log::write('Hash::IsValid', 'VALID', 'FAILED');
        return false;
    }
}