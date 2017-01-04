<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\Token;
use OAuth2;
use OAuth2\GrantType;
use OAuth2\Storage;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

/**
 * Unit tests for the \Chadicus\Slim\OAuth2\Routes\Token class.
 *
 * @coversDefaultClass \Chadicus\Slim\OAuth2\Routes\Token
 * @covers ::<private>
 * @covers ::__construct
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
        $storage = new Storage\Memory(
            [
                'client_credentials' => [
                    'testClientId' => [
                        'client_id' => 'testClientId',
                        'client_secret' => 'testClientSecret',
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

        $uri = 'localhost:8888/token';

        $headers = ['Content-Type' => ['application/json']];

        $body = [
            'client_id' => 'testClientId',
            'client_secret' => 'testClientSecret',
            'grant_type' => 'client_credentials',
        ];
        $request = new ServerRequest(['REQUEST_METHOD' => 'POST'], [], $uri, 'POST', 'php://input', $headers, [], [], $body);

        $route = new Token($server);

        $response = $route($request, new Response());

        $actual = json_decode((string)$response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());

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

    /**
     * Verify Content-Type header is added to response
     *
     * @test
     * @covers ::__invoke
     *
     * @return void
     */
    public function invokeAddsContentType()
    {
        $storage = new Storage\Memory(
            [
                'client_credentials' => [
                    'testClientId' => [
                        'client_id' => 'testClientId',
                        'client_secret' => 'testClientSecret',
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

        $uri = 'localhost:8888/token';

        $headers = ['Content-Type' => ['application/json']];

        $body = [
            'client_id' => 'testClientId',
            'client_secret' => 'testClientSecret',
            'grant_type' => 'client_credentials',
        ];
        $request = new ServerRequest(['REQUEST_METHOD' => 'POST'], [], $uri, 'POST', 'php://input', $headers, [], [], $body);

        $route = new Token($server);

        $response = $route($request, new Response());

		$this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }
}
