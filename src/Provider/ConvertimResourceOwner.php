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
    public function getId()
    {
        return $this->response['uuid'];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
