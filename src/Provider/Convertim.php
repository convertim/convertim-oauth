<?php

namespace Convertim\OAuth2\Provider;

use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Convertim extends AbstractProvider
{

    /**
     * @var string Key used in a token response to identify the resource owner.
     */
    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'projectId';

    /**
     * @var string
     */
    protected $projectId;

    /**
     * @param string[] $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        if (array_key_exists('clientId', $options) === false || $options['clientId'] === null) {
            throw new \InvalidArgumentException('The "clientId" option not set. Please set it.');
        } elseif (array_key_exists('clientSecret', $options) === false || $options['clientSecret'] === null) {
            throw new \InvalidArgumentException('The "clientSecret" option not set. Please set it.');
        } elseif (array_key_exists('redirectUri', $options) === false || $options['redirectUri'] === null) {
            throw new \InvalidArgumentException('The "redirectUri" option not set. Please set it.');
        } elseif (array_key_exists('projectId', $options) === false || $options['projectId'] === null) {
            throw new \InvalidArgumentException('The "projectId" option not set. Please set it.');
        }

        $collaborators['optionProvider'] = new HttpBasicAuthOptionProvider();

        parent::__construct($options, $collaborators);
    }

    /**
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return '';
    }

    /**
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://api.convertim.com/oauth';
    }

    /**
     * @param \League\OAuth2\Client\Token\AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.convertim.com/common?path=get-customer';
    }

    /**
     * @return string[]
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array|string $data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['error'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * @param array $response
     * @param \League\OAuth2\Client\Token\AccessToken $token
     * @return \Convertim\OAuth2\Provider\ConvertimResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new ConvertimResourceOwner($response);
    }

    /**
     * @param array $response
     * @param \League\OAuth2\Client\Grant\AbstractGrant $grant
     * @return \League\OAuth2\Client\Token\AccessToken|\League\OAuth2\Client\Token\AccessTokenInterface
     */
    protected function createAccessToken(array $response, AbstractGrant $grant)
    {
        $accessToken = parent::createAccessToken($response, $grant);

        foreach ($response as $k => $v) {
            if (!property_exists($accessToken, $k)) {
                $accessToken->$k = $v;
            }
        }

        return $accessToken;
    }

    /**
     * @return string[]
     */
    protected function getDefaultHeaders()
    {
        return [
            'x-project-id' => $this->projectId,
        ];
    }

    /**
     * @param \League\OAuth2\Client\Token\AccessToken|null $token
     * @return string[]
     */
    protected function getAuthorizationHeaders($token = null)
    {
        if ($token === null) {
            return [];
        }

        return [
            'Authorization' => 'Bearer ' . $token->getToken(),
        ];
    }
}
