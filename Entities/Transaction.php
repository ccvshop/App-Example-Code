<?php

namespace AppConnector\Entities;

/**
 *
 * @author  Adriaan Meijer
 * @version 1.0    - Initiele opzet
 *
 */
class Transaction
{
    /** @var  int */
    protected $customer_id;

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
    protected $return_url;
    /** @var  string */
    protected $webhook_url;

    /** @var  string */
    protected $pay_url;
    /** @var  string */
    protected $transaction_id;
    /** @var  int */
    protected $created;

    /** @var  \AppConnector\Entities\Transaction\Address */
    protected $billing_address;
    /** @var  \AppConnector\Entities\Transaction\Address */
    protected $shipping_address;

    /** @var  string */
    protected $date_of_birth;

    /** @var null|string */
    protected $error = null;

    public function __construct(\stdClass $oObject)
    {
        if (isset($oObject->customer_id)) {
            $this->setCustomerId($oObject->customer_id);
        }

        if (isset($oObject->amount)) {
            $this->setAmount($oObject->amount);
        }
        if (isset($oObject->currency)) {
            $this->setCurrency($oObject->currency);
        }
        if (isset($oObject->language)) {
            $this->setLanguage($oObject->language);
        }
        if (isset($oObject->return_url)) {
            $this->setReturnUrl($oObject->return_url);
        }
        if (isset($oObject->webhook_url)) {
            $this->setWebhookUrl($oObject->webhook_url);
        }
        if (isset($oObject->pay_url)) {
            $this->setPayUrl($oObject->pay_url);
        }
        if (isset($oObject->order_id)) {
            $this->setOrderId($oObject->order_id);
        }
        if (isset($oObject->order_number)) {
            $this->setOrderNumber($oObject->order_number);
        }
        if (isset($oObject->method)) {
            $this->setMethod($oObject->method);
        }
        if (isset($oObject->issuer)) {
            $this->setIssuer($oObject->issuer);
        }
        if (isset($oObject->date_of_birth)) {
            $this->setDateofbirth($oObject->date_of_birth);
        }

        $this->billing_address  = new \AppConnector\Entities\Transaction\Address($oObject->billing_address);
        $this->shipping_address = new \AppConnector\Entities\Transaction\Address($oObject->shipping_address);

        /** Defaults */
        $this->setCreated(gmdate('c', time()));
        $this->setStatus('OPEN');
        $this->setTransactionId(uniqid());

        if (!empty($oObject->created)) {
            $this->setCreated($oObject->created);
        }

        if (!empty($oObject->status)) {
            $this->setStatus($oObject->status);
        }

        if (!empty($oObject->transaction_id)) {
            $this->setTransactionId($oObject->transaction_id);
        }
    }

    /**
     * Convert this credential object to an array
     * @return array
     */
    public function toArray()
    {
        return [
            'customer_id'      => $this->getCustomerId(),
            'amount'           => $this->getAmount(),
            'currency'         => $this->getCurrency(),
            'status'           => $this->getStatus(),
            'order_id'         => $this->getOrderId(),
            'order_number'     => $this->getOrderNumber(),
            'language'         => $this->getLanguage(),
            'method'           => $this->getMethod(),
            'issuer'           => $this->getIssuer(),
            'return_url'       => $this->getReturnUrl(),
            'webhook_url'      => $this->getWebhookUrl(),
            'pay_url'          => $this->getPayUrl(),
            'transaction_id'   => $this->getTransactionId(),
            'billing_address'  => (array)$this->getBillingAddress(),
            'shipping_address' => (array)$this->getShippingAddress(),
            'created'          => $this->getCreated(),
            'date_of_birth'    => $this->getDateofbirth(),
            'error'            => $this->GetError(),

        ];
    }

    /**
     * @return \stdClass
     */
    public function toStdClass()
    {
        $oObject                 = new \stdClass();
        $oObject->status         = $this->getStatus();
        $oObject->pay_url        = $this->getPayUrl();
        $oObject->transaction_id = $this->getTransactionId();
        $oObject->error          = $this->GetError();
        $oObject->amount         = $this->getAmount();
        $oObject->currency       = $this->getCurrency();
        $oObject->return_url     = $this->getReturnUrl();
        $oObject->webhook_url    = $this->getWebhookUrl();
        $oObject->method         = $this->getMethod();
        $oObject->order_id       = $this->getOrderId();
        $oObject->order_number   = $this->getOrderNumber();
        $oObject->language       = $this->getLanguage();
        $oObject->issuer         = $this->getIssuer();

        $oObject->billing_address  = $this->getBillingAddress()->toStdClass();
        $oObject->shipping_address = $this->getShippingAddress()->toStdClass();

        $oObject->created       = $this->getCreated();
        $oObject->date_of_birth = $this->getDateofbirth();

        return $oObject;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return null|string
     */
    public function GetError()
    {
        if (empty($this->error)) {
            switch ($this->getStatus()) {
                case 'CANCELLED':
                    $this->error = 'The transaction is cancelled by the customer.';
                    break;
                case 'FAILED':
                    $this->error = 'A general error that the transaction can\'t be processed correctly';
                    break;
                case 'EXPIRED':
                    $this->error = 'The transaction is expired which can happen if remote services such as iDEAL or PayPal expire the transaction on their side.';
                    break;
                case 'OPEN':
                case 'SUCCESS':
                    break;
            }
        }
        return $this->error;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->order_number;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->return_url;
    }

    /**
     * @return string
     */
    public function getWebhookUrl()
    {
        return $this->webhook_url;
    }

    /**
     * @return string
     */
    public function getPayUrl()
    {
        return $this->pay_url;
    }

    /**
     * @return int
     */
    public function getCreated()
    {
        $sTimeStamp = str_replace('+00:00', 'Z', $this->created);
        return $sTimeStamp;
    }

    /**
     * @return \AppConnector\Entities\Transaction\Address
     */
    public function getBillingAddress()
    {
        return $this->billing_address;
    }

    /**
     * @return \AppConnector\Entities\Transaction\Address
     */
    public function getShippingAddress()
    {
        return $this->shipping_address;
    }

    /**
     * @return string
     */
    public function getDateofbirth()
    {
        return $this->date_of_birth;
    }

    public function getAge()
    {
        $oDateOfBirth = new \DateTime($this->date_of_birth);
        $oNow         = new \DateTime();
        $iAge         = $oDateOfBirth->diff($oNow)->format('%y');

        return $iAge;
    }

    /**
     * @param string $date_of_birth
     *
     * @return Transaction
     */
    public function setDateofbirth($date_of_birth)
    {
        $this->date_of_birth = $date_of_birth;
        return $this;
    }

    /**
     * @param int $amount
     *
     * @return Transaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param string $currency
     *
     * @return Transaction
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @param string $status
     *
     * @return Transaction
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param int $order_id
     *
     * @return Transaction
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    /**
     * @param string $order_number
     *
     * @return Transaction
     */
    public function setOrderNumber($order_number)
    {
        $this->order_number = $order_number;
        return $this;
    }

    /**
     * @param string $language
     *
     * @return Transaction
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @param string $method
     *
     * @return Transaction
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param string $issuer
     *
     * @return Transaction
     */
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
        return $this;
    }

    /**
     * @param string $return_url
     *
     * @return Transaction
     */
    public function setReturnUrl($return_url)
    {
        $this->return_url = $return_url;
        return $this;
    }

    /**
     * @param string $webhook_url
     *
     * @return Transaction
     */
    public function setWebhookUrl($webhook_url)
    {
        $this->webhook_url = $webhook_url;
        return $this;
    }

    /**
     * @param string $pay_url
     *
     * @return Transaction
     */
    public function setPayUrl($pay_url)
    {
        $this->pay_url = $pay_url;
        return $this;
    }

    /**
     * @param string $transaction_id
     *
     * @return Transaction
     */
    public function setTransactionId($transaction_id)
    {
        $this->transaction_id = $transaction_id;
        return $this;
    }

    /**
     * @param int $created
     *
     * @return Transaction
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @param \AppConnector\Entities\Transaction\Address $billing_address
     *
     * @return Transaction
     */
    public function setBillingAddress($billing_address)
    {
        $this->billing_address = $billing_address;
        return $this;
    }

    /**
     * @param \AppConnector\Entities\Transaction\Address $shipping_address
     *
     * @return Transaction
     */
    public function setShippingAddress($shipping_address)
    {
        $this->shipping_address = $shipping_address;
        return $this;
    }

    /**
     * @param null|string $error
     *
     * @return Transaction
     */
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * @param int $customer_id
     *
     * @return Transaction
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        return $this;
    }

}