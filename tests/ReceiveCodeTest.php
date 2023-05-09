<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\ReceiveCode;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use OAuth2;
use OAuth2\GrantType;
use OAuth2\Storage;
use Slim\Views;

/**
 * Unit tests for the \Chadicus\Slim\OAuth2\Routes\ReceiveCode class.
 *
 * @coversDefaultClass \Chadicus\Slim\OAuth2\Routes\ReceiveCode
 * @covers ::<private>
 * @covers ::__construct
 */
final class ReceiveCodeTest extends \PHPUnit_Framework_TestCase
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
                        'redirect_uri' => '/receive-code',
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

        $view = new Views\PhpRenderer(__DIR__ . '/../templates');

        $route = new ReceiveCode($view);

        $code = md5(time());
        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            'php://input',
            ['Content-Type' => 'application/json'],
            [],
            [
                'code' => $code,
                'state' => 'xyz',
            ]
        );

        $response = $route($request, new Response());

        $expectedBody = "<h2>The authorization code is {$code}</h2>\n";

        $this->assertSame($expectedBody, (string)$response->getBody());
    }

    /**
     * Verify behavior of __construct() when $view is invalid.
     *
     * @test
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMethod $view must implement a render() method
     *
     * @return void
     */
    public function constructWithInvalidView()
    {
        $server = new OAuth2\Server(new Storage\Memory([]), [], []);
        new ReceiveCode($server, new \StdClass());
    }
}
