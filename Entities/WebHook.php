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
class WebHook
{
    /**
     * @var int Internal ID of this WebHook. Used for example purposes only.
     */
    private $id = 0;

    /**
     * @var int Customer ID of this WebHook. Used for example purposes only.
     */
    private $customerId = 0;
    /**
     * @var string Event of the WebHook, representing an action that takes place on an object is called an event.
     */
    private $event = '';
    /**
     * @var string Address of the WebHook, representing a remote HTTP URI to which the callback will be posted
     */
    private $address = '';
    /**
     * @var string Key is used to validate the integrity of the data send by this WebHook
     */
    private $key = '';

    public function __construct(\stdClass $oObject)
    {
        if (isset($oObject->customer_id)) {
            $this->setCustomerId($oObject->customer_id);
        }
        $this->setId($oObject->id);
        $this->setEvent($oObject->event);
        $this->setAddress($oObject->address);
        $this->setKey($oObject->key);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $Id
     */
    public function setId($Id)
    {
        $this->id = $Id;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $Address
     */
    public function setAddress($Address)
    {
        $this->address = $Address;
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
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param string $Event
     */
    public function setEvent($Event)
    {
        $this->event = $Event;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $Key
     */
    public function setKey($Key)
    {
        $this->key = $Key;
    }
}