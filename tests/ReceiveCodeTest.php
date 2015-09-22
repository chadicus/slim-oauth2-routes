<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\ReceiveCode;

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
        $storage = new \OAuth2\Storage\Memory(
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

        $server = new \OAuth2\Server(
            $storage,
            [
                'access_lifetime' => 3600,
            ],
            [
                new \OAuth2\GrantType\ClientCredentials($storage),
            ]
        );

        $code = md5(time());

        \Slim\Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'CONTENT_TYPE' => 'application/json',
                'PATH_INFO' => '/receive-code',
                'QUERY_STRING' => "code={$code}&state=xyz",
            ]
        );

        $slim = new \Slim\Slim();
        $slim->post('/receive-code', new ReceiveCode($slim));

        ob_start();

        $slim->run();

        ob_get_clean();

        $this->assertSame(200, $slim->response->status());

        $expected = <<<HTML
<h2>The authorization code is {$code}</h2>

HTML;

        $this->assertSame($expected, $slim->response->getBody());
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

        ReceiveCode::register($slim);

        $route = $slim->router()->getNamedRoute('receive-code');

        $this->assertInstanceOf('\Slim\Route', $route);
        $this->assertInstanceOf('\Chadicus\Slim\OAuth2\Routes\ReceiveCode', $route->getCallable());
        $this->assertSame(
            [\Slim\Http\Request::METHOD_GET, \Slim\Http\Request::METHOD_POST],
            $route->getHttpMethods()
        );
    }
}
