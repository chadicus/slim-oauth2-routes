<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\Authorize;

/**
 * Unit tests for the \Chadicus\Slim\OAuth2\Routes\Authorize class.
 *
 * @coversDefaultClass \Chadicus\Slim\OAuth2\Routes\Authorize
 * @covers ::<private>
 * @covers ::__construct
 */
final class AuthorizeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verify behavior of __invoke() with no client_id parameter
     *
     * @test
     * @covers ::__invoke
     *
     * @return void
     */
    public function invokeNoClientSpecified()
    {
        $storage = new \OAuth2\Storage\Memory([]);
        $server = new \OAuth2\Server($storage, [], []);

        \Slim\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/authorize',
                'QUERY_STRING' => '',
            ]
        );

        $slim = new \Slim\Slim();
        $slim->get('/authorize', new Authorize($slim, $server));

        ob_start();

        $slim->run();

        ob_get_clean();

        $this->assertSame(400, $slim->response->status());

        $actual = json_decode($slim->response->getBody(), true);
        $this->assertSame(
            [
                'error' => 'invalid_client',
                'error_description' => 'No client id supplied',
            ],
            $actual
        );
    }

    /**
     * Verify behavior of __invoke() with invalid client_id parameter
     *
     * @test
     * @covers ::__invoke
     *
     * @return void
     */
    public function invokeInvalidClientSpecified()
    {
        $storage = new \OAuth2\Storage\Memory([]);
        $server = new \OAuth2\Server($storage, [], []);

        \Slim\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/authorize',
                'QUERY_STRING' => 'client_id=invalidClientId',
            ]
        );

        $slim = new \Slim\Slim();
        $slim->get('/authorize', new Authorize($slim, $server));

        ob_start();

        $slim->run();

        ob_get_clean();

        $this->assertSame(400, $slim->response->status());

        $actual = json_decode($slim->response->getBody(), true);
        $this->assertSame(
            [
                'error' => 'invalid_client',
                'error_description' => 'The client id supplied is invalid',
            ],
            $actual
        );
    }

    /**
     * Verify basic behavior of __invoke().
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
                'allow_implicit' => true,
            ],
            []
        );

        \Slim\Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'PATH_INFO' => '/authorize',
                'QUERY_STRING' => 'client_id=testClientId&redirect_uri=http://example.com&response_type=code&'
                . 'state=test',
                'slim.input' => 'authorized=yes',
            ]
        );

        $slim = new \Slim\Slim();
        $slim->map('/authorize', new Authorize($slim, $server))->via('POST', 'GET');

        ob_start();

        $slim->run();

        ob_get_clean();

        $this->assertSame(302, $slim->response->status());

        $location = $slim->response->headers()->get('Location');
        $parts = parse_url($location);
        parse_str($parts['query'], $query);

        $this->assertTrue(isset($query['code']));
        $this->assertSame('test', $query['state']);

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

        Authorize::register($slim, $server);

        $route = $slim->router()->getNamedRoute('authorize');

        $this->assertInstanceOf('\Slim\Route', $route);
        $this->assertInstanceOf('\Chadicus\Slim\OAuth2\Routes\Authorize', $route->getCallable());
        $this->assertSame([\Slim\Http\Request::METHOD_GET, \Slim\Http\Request::METHOD_POST], $route->getHttpMethods());
    }

    /**
     * Verify bahavior of /authorize route when authorized parameter is empty.
     *
     * @test
     * @covers ::__invoke
     *
     * @return void
     */
    public function invokeEmptyAuthorized()
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
        $server = new \OAuth2\Server($storage, [], []);

        \Slim\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/authorize',
                'QUERY_STRING' => 'client_id=testClientId&redirect_uri=http://example.com&response_type=code'
                . '&state=test',
            ]
        );

        $slim = new \Slim\Slim();
        $slim->get('/authorize', new Authorize($slim, $server));

        ob_start();

        $slim->run();

        ob_get_clean();

        $expected = <<<HTML
<form method="post">
    <label>Do You Authorize testClientId?</label><br />
    <input type="submit" name="authorized" value="yes">
    <input type="submit" name="authorized" value="no">
</form>

HTML;

        $this->assertSame($expected, $slim->response->getBody());
    }
}
