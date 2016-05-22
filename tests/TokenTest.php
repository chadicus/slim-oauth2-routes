<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\Token;
use OAuth2;
use OAuth2\GrantType;
use OAuth2\Storage;
use Slim;
use Slim\Http;

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

        $uri = Http\Uri::createFromString('https://example.com/foo/bar?baz=bat');

        $headers = new Http\Headers();
        $headers->add('Content-Type', 'application/json');

        $json = json_encode(
            [
                'client_id' => 'testClientId',
                'client_secret' => 'testClientSecret',
                'grant_type' => 'client_credentials',
            ]
        );

        $stream = fopen('php://memory','r+');
        fwrite($stream, $json);
        rewind($stream);
        $body = new Http\Stream($stream);

        $request = new Http\Request('POST', $uri, $headers, [], ['REQUEST_METHOD' => 'POST'], $body, []);

        $route = new Token($server);

        $response = $route($request, new Http\Response());

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
}
