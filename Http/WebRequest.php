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
class WebRequest
{
    /**
     * @var string The "Public key" or "Api key" can be retrieved in the webshop, This should be the same as the header 'x-public'.
     */
    private $publicKey = '';

    /**
     * @var string The "Secret key" or "Api secret" can be retrieved in the webshop.
     */
    private $secretKey = '';

    /**
     * @var string The data that is being posted to the resource (only with POST or PATCH methods)
     */
    private $data = '';

    /**
     * @var string The request URI minus the domain name
     */
    private $apiRoot = '';

    /**
     * @var string The request domain without trailing slash
     */
    private $apiResource = '';

    /**
     * @var string The accept language of this call.
     */
    private $acceptLanguage = null;

    /**
     * Makes a GET request to the REST API
     *
     * @return string
     * @throws InvalidApiResponse
     */
    public function get()
    {
        #HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
        $sMethod    = 'GET';
        $sTimeStamp = gmdate('c');

        #Creating the hash
        $sHashString = implode('|', [
            $this->getPublicKey(),
            $sMethod,
            $this->getApiResource(),
            '',
            $sTimeStamp,
        ]);

        $sHash = hash_hmac('sha512', $sHashString, $this->getSecretKey());

        $rCurlHandler = curl_init();
        curl_setopt($rCurlHandler, CURLOPT_URL, $this->getApiRoot() . $this->getApiResource());
        curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($rCurlHandler, CURLOPT_HTTPHEADER, [
            "x-date: " . $sTimeStamp,
            "x-hash: " . $sHash,
            "x-public: " . $this->getPublicKey(),
            "Content-Type: text/json",
        ]);

        $sOutput   = curl_exec($rCurlHandler);
        $iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
        curl_close($rCurlHandler);

        Log::write('WebRequest', 'GET::REQUEST', $this->getApiRoot() . $this->getApiResource());
        Log::write('WebRequest', 'GET::HTTPCODE', $iHTTPCode);
        Log::write('WebRequest', 'GET::RESPONSE', $sOutput);

        if ($iHTTPCode !== 200) {
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
    public function delete()
    {
        #HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
        $sMethod    = 'DELETE';
        $sTimeStamp = gmdate('c');

        #Creating the hash
        $sHashString = implode('|', [
            $this->getPublicKey(),
            $sMethod,
            $this->getApiResource(),
            $this->getData(),
            $sTimeStamp,
        ]);

        $sHash = hash_hmac('sha512', $sHashString, $this->getSecretKey());

        $rCurlHandler = curl_init();
        curl_setopt($rCurlHandler, CURLOPT_URL, $this->getApiRoot() . $this->getApiResource());
        curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($rCurlHandler, CURLOPT_HTTPHEADER, [
            "x-date: " . $sTimeStamp,
            "x-hash: " . $sHash,
            "x-public: " . $this->getPublicKey(),
            "Content-Type: text/json",
        ]);
        $sOutput   = curl_exec($rCurlHandler);
        $iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
        curl_close($rCurlHandler);

        Log::write('WebRequest', 'DELETE::REQUEST', $this->getApiRoot() . $this->getApiResource());
        Log::write('WebRequest', 'DELETE::HTTPCODE', $iHTTPCode);
        Log::write('WebRequest', 'DELETE::RESPONSE', $sOutput);

        if ($iHTTPCode !== 204) {
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
    public function post()
    {
        #HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
        $sMethod    = 'POST';
        $sTimeStamp = gmdate('c');

        #Creating the hash
        $sHashString = implode('|', [
            $this->getPublicKey(),
            $sMethod,
            $this->getApiResource(),
            $this->getData(),
            $sTimeStamp,
        ]);

        $sHash = hash_hmac('sha512', $sHashString, $this->getSecretKey());

        $rCurlHandler = curl_init();
        curl_setopt($rCurlHandler, CURLOPT_URL, $this->getApiRoot() . $this->getApiResource());
        curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurlHandler, CURLOPT_POSTFIELDS, $this->getData());
        curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($rCurlHandler, CURLOPT_HTTPHEADER, [
            "x-date: " . $sTimeStamp,
            "x-hash: " . $sHash,
            "x-public: " . $this->getPublicKey(),
            "Content-Type: text/json",
        ]);
        $sOutput   = curl_exec($rCurlHandler);
        $iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
        curl_close($rCurlHandler);

        Log::write('WebRequest', 'POST::REQUEST', $this->getApiRoot() . $this->getApiResource());
        Log::write('WebRequest', 'POST::DATA', $this->getData());
        Log::write('WebRequest', 'POST::HTTPCODE', $iHTTPCode);
        Log::write('WebRequest', 'POST::RESPONSE', $sOutput);

        $this->setData('');

        if (!in_array($iHTTPCode, [200, 201, 204])) {
            throw new InvalidApiResponse('HttpCode was ' . $iHTTPCode . '. Expected 200|201 on [POST] ' . $this->getApiRoot() . $this->getApiResource());
        }
        return $sOutput;
    }

    /**
     * Makes a PATCH request to the REST API
     *
     * @return string
     * @throws InvalidApiResponse
     */
    public function patch()
    {
        #HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
        $sMethod    = 'PATCH';
        $sTimeStamp = gmdate('c');

        #Creating the hash
        $sHashString = implode('|', [
            $this->getPublicKey(),
            $sMethod,
            $this->getApiResource(),
            $this->getData(),
            $sTimeStamp,
        ]);

        $sHash = hash_hmac('sha512', $sHashString, $this->getSecretKey());

        $rCurlHandler = curl_init();
        curl_setopt($rCurlHandler, CURLOPT_URL, $this->getApiRoot() . $this->getApiResource());
        curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurlHandler, CURLOPT_POSTFIELDS, $this->getData());
        curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($rCurlHandler, CURLOPT_HTTPHEADER, [
            "x-date: " . $sTimeStamp,
            "x-hash: " . $sHash,
            "x-public: " . $this->getPublicKey(),
            "Content-Type: text/json",
        ]);
        $sOutput   = curl_exec($rCurlHandler);
        $iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
        curl_close($rCurlHandler);

        Log::write('WebRequest', 'PATCH::REQUEST', $this->getApiRoot() . $this->getApiResource());
        Log::write('WebRequest', 'PATCH::DATA', $this->getData());
        Log::write('WebRequest', 'PATCH::HTTPCODE', $iHTTPCode);
        Log::write('WebRequest', 'PATCH::RESPONSE', $sOutput);

        $this->setData('');

        if ($iHTTPCode !== 204) {
            throw new InvalidApiResponse('HttpCode was ' . $iHTTPCode . '. Expected 204');
        }
        return $sOutput;
    }

    /**
     * Makes a PUT request to the REST API
     *
     * @return string
     * @throws InvalidApiResponse
     */
    public function put()
    {
        #HTTP method in uppercase (ie: GET, POST, PUT, DELETE)
        $sMethod    = 'PUT';
        $sTimeStamp = gmdate('c');

        #Creating the hash
        $sHashString = implode('|', [
            $this->getPublicKey(),
            $sMethod,
            $this->getApiResource(),
            $this->getData(),
            $sTimeStamp,
        ]);

        $sHash = hash_hmac('sha512', $sHashString, $this->getSecretKey());

        $rCurlHandler = curl_init();
        curl_setopt($rCurlHandler, CURLOPT_URL, $this->getApiRoot() . $this->getApiResource());
        curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurlHandler, CURLOPT_POSTFIELDS, $this->getData());
        curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($rCurlHandler, CURLOPT_HTTPHEADER, [
            "accept-language: " . $this->getAcceptLanguage(),
            "x-date: " . $sTimeStamp,
            "x-hash: " . $sHash,
            "x-public: " . $this->getPublicKey(),
            "Content-Type: text/json",
        ]);
        $sOutput   = curl_exec($rCurlHandler);
        $iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
        curl_close($rCurlHandler);

        Log::write('WebRequest', 'PUT::REQUEST', $this->getApiRoot() . $this->getApiResource());
        Log::write('WebRequest', 'PUT::DATA', $this->getData());
        Log::write('WebRequest', 'PUT::HTTPCODE', $iHTTPCode);
        Log::write('WebRequest', 'PUT::RESPONSE', $sOutput);

        $this->setData('');

        if ($iHTTPCode !== 204) {
            print_r($sOutput);
            throw new InvalidApiResponse('HttpCode was ' . $iHTTPCode . '. Expected 204');
        }
        return $sOutput;
    }

    /**
     * The request domain without trailing slash
     *
     * @return string
     */
    public function getApiResource()
    {
        return $this->apiResource;
    }

    /**
     * The request domain without trailing slash
     *
     * @param string $ApiResource
     *
     * @return $this
     */
    public function setApiResource($ApiResource)
    {
        $this->apiResource = $ApiResource;

        return $this;
    }

    /**
     * The request URI minus the domain name
     *
     * @return string
     */
    public function getApiRoot()
    {
        return $this->apiRoot;
    }

    /**
     * The request URI minus the domain name
     *
     * @param string $ApiRoot
     *
     * @return $this
     */
    public function setApiRoot($ApiRoot)
    {
        $this->apiRoot = $ApiRoot;

        return $this;
    }

    /**
     * The data that is being posted to the resource (only with POST or PATCH methods)
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * The data that is being posted to the resource (only with POST or PATCH methods)
     *
     * @param string $Data
     *
     * @return $this
     */
    public function setData($Data)
    {
        $this->data = JsonSerializer::serialize($Data);

        return $this;
    }

    /**
     * The "Public key" or "Api key" can be retrieved in the webshop, This should be the same as the header 'x-public'.
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * The "Public key" or "Api key" can be retrieved in the webshop, This should be the same as the header 'x-public'.
     *
     * @param string $PublicKey
     *
     * @return $this
     */
    public function setPublicKey($PublicKey)
    {
        $this->publicKey = $PublicKey;

        return $this;
    }

    /**
     * The "Secret key" or "Api secret" can be retrieved in the webshop.
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * The "Secret key" or "Api secret" can be retrieved in the webshop.
     *
     * @param string $SecretKey
     *
     * @return $this
     */
    public function setSecretKey($SecretKey)
    {
        $this->secretKey = $SecretKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getAcceptLanguage()
    {
        if (is_null($this->acceptLanguage)) {
            return 'nl';
        }
        return $this->acceptLanguage;
    }

    /**
     * @param string $AcceptLanguage
     *
     * @return WebRequest
     */
    public function setAcceptLanguage($AcceptLanguage)
    {
        $this->acceptLanguage = $AcceptLanguage;
        return $this;
    }

}
