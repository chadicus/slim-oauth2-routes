<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\Token;
use OAuth2;
use OAuth2\GrantType;
use OAuth2\Storage;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the \Chadicus\Slim\OAuth2\Routes\Token class.
 *
 * @coversDefaultClass \Chadicus\Slim\OAuth2\Routes\Token
 * @covers ::<private>
 * @covers ::__construct
 */
final class TokenTest extends TestCase
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
        $request = $this->getRequest($uri, $headers, $body);

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
        $oauth2ServerMock = $this->getMockBuilder('\\OAuth2\\Server')->disableOriginalConstructor()->getMock();
        $oauth2ServerMock->method('handleTokenRequest')->willReturn(
            new OAuth2\Response([], 200, [])
        );

        $route = new Token($oauth2ServerMock);
        $response = $route(new ServerRequest(), new Response());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Verify Content-Type header remains unchanged if OAuth2 response contains the header.
     *
     * @test
     * @covers ::__invoke
     *
     * @return void
     */
    public function invokeRetainsContentType()
    {
        $oauth2ServerMock = $this->getMockBuilder('\\OAuth2\\Server')->disableOriginalConstructor()->getMock();
        $oauth2ServerMock->method('handleTokenRequest')->willReturn(
            new OAuth2\Response([], 200, ['Content-Type' => 'text/html'])
        );

        $route = new Token($oauth2ServerMock);
        $response = $route(new ServerRequest(), new Response());
        $this->assertSame('text/html', $response->getHeaderLine('Content-Type'));
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
