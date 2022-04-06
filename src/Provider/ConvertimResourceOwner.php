<?php

namespace Convertim\OAuth2\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class ConvertimResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->response['type'];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->response['uuid'];
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['email'];
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->response['name'];
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->response['lastName'];
    }

    /**
     * @return string|null
     */
    public function getTelephonePrefix()
    {
        return $this->response['telephonePrefix'];
    }

    /**
     * @return string|null
     */
    public function getTelephoneNumber()
    {
        return $this->response['telephoneNumber'];
    }

    /**
     * @return string|null
     */
    public function getSelectedDeliveryAddressUuid()
    {
        return $this->response['selectedDeliveryAddressUuid'];
    }

    /**
     * @return string|null
     */
    public function getSelectedBillingAddressUuid()
    {
        return $this->response['selectedBillingAddressUuid'];
    }

    /**
     * @return array|null
     */
    public function getDeliveryAddresses()
    {
        return $this->response['deliveryAddresses'];
    }

    /**
     * @return array|null
     */
    public function getBillingAddresses()
    {
        return $this->response['billingAddresses'];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
