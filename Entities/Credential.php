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
class Credential
{
    /**
     * @var string Public key used to connect to the api.
     */
    private $apiPublic;
    /**
     * @var string Secret key used to connect to the api
     */
    private $apiSecret;
    /**
     * @var string URI to connect to the api
     */
    private $apiRoot;
    /**
     * @var string Once the user has successfully installed the app, return him here.
     */
    private $returnUrl;
    /**
     * @var int A Customer Id. Used for example purposes only.
     */
    private $customerId;
    /**
     * @var string Create date of this Credential. Used for example purposes only.
     */
    private $createDate;

    public function __construct(\stdClass $oObject)
    {
        $this->setApiPublic($oObject->api_public);
        $this->setApiSecret($oObject->api_secret);
        $this->setApiRoot($oObject->api_root);
        $this->setReturnUrl($oObject->return_url);
        $this->setCustomerId($oObject->customer_id);
        $this->setCreateDate($oObject->create_date);
    }

    /**
     * Convert this credential object to an array
     * @return array
     */
    public function toArray()
    {
        return [
            'api_public'  => $this->apiPublic,
            'api_secret'  => $this->apiSecret,
            'api_root'    => $this->apiRoot,
            'return_url'  => $this->returnUrl,
            'customer_id' => $this->customerId,
            'create_date' => $this->createDate,
        ];
    }

    /**
     * Convert this credential object to an std object
     * @return object
     */
    public function toStd()
    {
        return (object)$this->toArray();
    }

    /**
     * Print this credential as an array
     * @return string
     */
    public function __toString()
    {
        return print_r($this->toArray(), 1);
    }

    /**
     * @return string
     */
    public function getApiPublic()
    {
        return $this->apiPublic;
    }

    /**
     * @param string $ApiPublic
     */
    public function setApiPublic($ApiPublic)
    {
        $this->apiPublic = $ApiPublic;
    }

    /**
     * @return string
     */
    public function getApiRoot()
    {
        return $this->apiRoot;
    }

    /**
     * @param string $ApiRoot
     */
    public function setApiRoot($ApiRoot)
    {
        $this->apiRoot = $ApiRoot;
    }

    /**
     * @return string
     */
    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * @param string $ApiSecret
     */
    public function setApiSecret($ApiSecret)
    {
        $this->apiSecret = $ApiSecret;
    }

    /**
     * @return string
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param string $CreateDate
     */
    public function setCreateDate($CreateDate)
    {
        $this->createDate = $CreateDate;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $CustomerId
     */
    public function setCustomerId($CustomerId)
    {
        $this->customerId = $CustomerId;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $ReturnUrl
     */
    public function setReturnUrl($ReturnUrl)
    {
        $this->returnUrl = $ReturnUrl;
    }
}