<?php

namespace Convertim\OAuth2\Tests;

use Convertim\OAuth2\Provider\Convertim;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Mockery as m;

class ConvertimTest extends TestCase
{

    protected $provider;

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('Convertim\OAuth2\Provider\Convertim');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    protected function setUp(): void
    {
        $this->provider = new Convertim([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'projectId' => 'dev',
        ]);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];
        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);
        $this->assertEquals('/oauth', $uri['path']);
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testResourceOwnerDetailsUrl()
    {
        $token = m::mock('League\OAuth2\Client\Token\AccessToken');

        $url = $this->provider->getResourceOwnerDetailsUrl($token);
        $uri = parse_url($url);

        $this->assertEquals('/common', $uri['path']);
        $this->assertEquals('path=get-customer', $uri['query']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"access_token":"mock_access_token", "token_type":"bearer"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken(
            'authorization_code',
            ['code' => 'mock_authorization_code']
        );
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testExceptionThrownWhenErrorObjectReceived()
    {
        $this->expectException(IdentityProviderException::class);


        $message = uniqid();
        $status = rand(400, 600);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn(' {"error":"' . $message . '"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $this->provider->getAccessToken(
            'authorization_code',
            ['code' => 'mock_authorization_code']
        );
    }

    public function testUserData()
    {
        $id = uniqid();
        $email = uniqid();
        $statementDescriptor = uniqid();
        $displayName = uniqid();
        $timezone = uniqid();
        $detailsSubmitted = true;
        $chargesEnabled = true;
        $transfersEnabled = true;
        $currenciesSupported = [];
        $defaultCurrency = uniqid();
        $country = uniqid();
        $object = "account";
        $businessName = uniqid();
        $businessUrl = uniqid();
        $supportPhone = uniqid();
        $businessLogo = uniqid();
        $metaData = [];
        $managed = false;

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);
        $accountResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $accountResponse->shouldReceive('getBody')->andReturn('{"uuid": "'.$id.'","email": "'.$email.'","statement_descriptor": "'.$statementDescriptor.'","display_name": "'.$displayName.'","timezone": "'.$timezone.'","details_submitted": '.$detailsSubmitted.',"charges_enabled": '.$chargesEnabled.',"transfers_enabled": '.$transfersEnabled.',"currencies_supported": [],"default_currency": "'.$defaultCurrency.'","country": "'.$country.'","object": "'.$object.'","business_name": "'.$businessName.'","business_url": "'.$businessUrl.'","support_phone": "'.$supportPhone.'","business_logo": "'.$businessLogo.'","metadata": [],"managed": false}');
        $accountResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $accountResponse->shouldReceive('getStatusCode')->andReturn(200);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $accountResponse);

        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $account = $this->provider->getResourceOwner($token);

        $this->assertEquals($id, $account->getId());
        $this->assertEquals($id, $account->toArray()['uuid']);
        $this->assertEquals($email, $account->toArray()['email']);
        $this->assertEquals($statementDescriptor, $account->toArray()['statement_descriptor']);
        $this->assertEquals($displayName, $account->toArray()['display_name']);
        $this->assertEquals($timezone, $account->toArray()['timezone']);
        $this->assertEquals($detailsSubmitted, $account->toArray()['details_submitted']);

        $this->assertEquals($chargesEnabled, $account->toArray()['charges_enabled']);
        $this->assertEquals($currenciesSupported, $account->toArray()['currencies_supported']);
        $this->assertEquals($defaultCurrency, $account->toArray()['default_currency']);
        $this->assertEquals($country, $account->toArray()['country']);
        $this->assertEquals($object, $account->toArray()['object']);
        $this->assertEquals($businessName, $account->toArray()['business_name']);
        $this->assertEquals($supportPhone, $account->toArray()['support_phone']);
        $this->assertEquals($businessLogo, $account->toArray()['business_logo']);
        $this->assertEquals($metaData, $account->toArray()['metadata']);
        $this->assertEquals($managed, $account->toArray()['managed']);
        $this->assertEquals($transfersEnabled, $account->toArray()['transfers_enabled']);
        $this->assertEquals($businessUrl, $account->toArray()['business_url']);
    }

    public function testExtraPropertiesAreAddedToAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"access_token":"mock_access_token", "token_type":"bearer", "extra_1": "mock_extra_1", "extra_2": "mock_extra_2"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken(
            'authorization_code',
            ['code' => 'mock_authorization_code']
        );
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
        $this->assertObjectHasAttribute('extra_1', $token);
        $this->assertObjectHasAttribute('extra_2', $token);
    }
}
