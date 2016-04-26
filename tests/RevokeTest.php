<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\Revoke;

/**
 * Unit tests for the \Chadicus\Slim\OAuth2\Routes\Revoke class.
 *
 * @coversDefaultClass \Chadicus\Slim\OAuth2\Routes\Revoke
 * @covers ::<private>
 * @covers ::__construct
 */
final class RevokeTest extends \PHPUnit_Framework_TestCase
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
        $token = md5(time());
        $storage = new \OAuth2\Storage\Memory(
            [
                'access_tokens' => [
                    $token => [
                        'access_token' => $token,
                        'client_id' => 'a client id',
                        'user_id' => 'a user id',
                        'expires' => 99999999900,
                        'scope' => null,
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
                'token_type_hint' => 'access_token',
                'token' => $token,
            ]
        );

        \Slim\Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'CONTENT_TYPE' => 'application/json',
                'PATH_INFO' => '/revoke',
                'CONTENT_LENGTH' => strlen($json),
                'slim.input' => $json,
            ]
        );

        $slim = new \Slim\Slim();
        $slim->post('/revoke', new Revoke($slim, $server));

        ob_start();

        $slim->run();

        ob_get_clean();

        $this->assertSame(200, $slim->response->status());

        $actual = json_decode($slim->response->getBody(), true);
        $this->assertSame(
            [
                'revoked' => true,
            ],
            $actual
        );

        $this->assertFalse($storage->getAccessToken($token));
    }

    /**
     * Verify basic behavior of register
     *
     * @test
     * @covers ::register
     *
     * @return void
     */
    public function register()
    {
        $storage = new \OAuth2\Storage\Memory([]);
        $server = new \OAuth2\Server($storage, [], []);

        \Slim\Environment::mock();

        $slim = new \Slim\Slim();

        Revoke::register($slim, $server);

        $route = $slim->router()->getNamedRoute('revoke');

        $this->assertInstanceOf('\Slim\Route', $route);
        $this->assertInstanceOf('\Chadicus\Slim\OAuth2\Routes\Revoke', $route->getCallable());
        $this->assertSame([\Slim\Http\Request::METHOD_POST], $route->getHttpMethods());
    }
}
