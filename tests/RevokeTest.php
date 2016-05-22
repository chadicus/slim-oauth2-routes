<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\Revoke;
use OAuth2;
use OAuth2\Storage;
use OAuth2\GrantType;
use Slim\Http;

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
        $storage = new Storage\Memory(
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

        $server = new OAuth2\Server(
            $storage,
            [
                'access_lifetime' => 3600,
            ],
            [
                new GrantType\ClientCredentials($storage),
            ]
        );

        $route = new Revoke($server);

        $json = json_encode(
            [
                'token_type_hint' => 'access_token',
                'token' => $token,
            ]
        );

        $stream = fopen('php://memory','r+');
        fwrite($stream, $json);
        rewind($stream);
        $body = new Http\Stream($stream);

        $env = Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'CONTENT_TYPE' => 'application/json',
                'PATH_INFO' => '/revoke',
                'CONTENT_LENGTH' => strlen($json),
                'slim.input' => $json,
            ]
        );

        $request = Http\Request::createFromEnvironment($env)->withBody($body);

        $response = $route($request, new Http\Response());

        $this->assertSame(200, $response->getStatusCode());

        $actual = json_decode((string)$response->getBody(), true);
        $this->assertSame(
            [
                'revoked' => true,
            ],
            $actual
        );

        $this->assertFalse($storage->getAccessToken($token));
    }
}
