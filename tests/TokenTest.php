<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\Token;

/**
 * Unit tests for the \Chadicus\Slim\OAuth2\Routes\Token class.
 *
 * @coversDefaultClass \Chadicus\Slim\OAuth2\Routes\Token
 * @covers ::<private>
 */
final class TokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verify basic behavior of __invoke()
     *
     * @test
     * @covers ::__invoke
     *
     * @return void
     */
    public function invoke()
    {
        $storage = new \OAuth2\Storage\Memory(
            [
                'client_credentials' => [
                    'testClientId' => [
                        'client_id' => 'testClientId',
                        'client_secret' => 'testClientSecret',
                    ],
                ],
            ]
        );

        $server = new \OAuth2\Server(
            $storage,
            [
                'access_lifetime' => 3600,
            ],
            [
                new \OAuth2\GrantType\ClientCredentials($storage),
            ]
        );

        $json = json_encode(
            [
                'client_id' => 'testClientId',
                'client_secret' => 'testClientSecret',
                'grant_type' => 'client_credentials',
            ]
        );

        \Slim\Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'CONTENT_TYPE' => 'application/json',
                'PATH_INFO' => '/token',
                'CONTENT_LENGTH' => strlen($json),
                'slim.input' => $json,
            ]
        );

        $slim = new \Slim\Slim();
        $slim->post('/token', new Token($slim, $server));

        ob_start();

        $slim->run();

        ob_get_clean();

        $this->assertSame(200, $slim->response->status());

        $actual = json_decode($slim->response->getBody(), true);
        $this->assertSame(
            [
                'access_token' => $actual['access_token'],
                'expires_in' => 3600,
                'token_type' => 'Bearer',
                'scope' => null,
            ],
            $actual
        );
    }
}
