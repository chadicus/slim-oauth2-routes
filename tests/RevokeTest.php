<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\Revoke;
use OAuth2;
use OAuth2\Storage;
use OAuth2\GrantType;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the \Chadicus\Slim\OAuth2\Routes\Revoke class.
 *
 * @coversDefaultClass \Chadicus\Slim\OAuth2\Routes\Revoke
 * @covers ::<private>
 * @covers ::__construct
 */
final class RevokeTest extends TestCase
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

        $body = [
            'token_type_hint' => 'access_token',
            'token' => $token,
        ];

        $uri = '/revoke';
        $headers = ['Content-Type' => ['application/json']];
        $request = $this->getRequest($uri, $headers, $body);

        $response = $route($request, new Response());

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

    private function getRequest($uri, array $headers, array $body)
    {
        return new ServerRequest(
            ['REQUEST_METHOD' => 'POST'],
            [],
            $uri,
            'POST',
            'php://input',
            $headers,
            [],
            [],
            $body
        );
    }
}
