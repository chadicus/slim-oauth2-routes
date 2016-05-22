<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\Authorize;
use OAuth2;
use OAuth2\Storage;
use Slim;
use Slim\Http;
use Slim\Views;

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
        $storage = new Storage\Memory([]);
        $server = new OAuth2\Server($storage, [], []);

        $view = new Views\PhpRenderer(__DIR__ . '/../templates');

        $route = new Authorize($server, $view);

        $env = Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/authorize',
                'QUERY_STRING' => '',
            ]
        );

        $request = Http\Request::createFromEnvironment($env);
        $response = $route($request, new Http\Response);

        $this->assertSame(400, $response->getStatusCode());

        $actual = json_decode((string)$response->getBody(), true);
        $this->assertSame(
            [
                'error' => 'invalid_client',
                'error_description' => 'No client id supplied',
            ],
            $actual
        );
    }

    /**
     * Verify behavior of __invoke() with invalid client_id parameter.
     *
     * @test
     * @covers ::__invoke
     *
     * @return void
     */
    public function invokeInvalidClientSpecified()
    {
        $storage = new Storage\Memory([]);
        $server = new OAuth2\Server($storage, [], []);

        $view = new Views\PhpRenderer(__DIR__ . '/../templates');

        $route = new Authorize($server, $view);

        $env = Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/authorize',
                'QUERY_STRING' => 'client_id=invalidClientId',
            ]
        );

        $request = Http\Request::createFromEnvironment($env);

        $response = $route($request, new Http\Response);

        $this->assertSame(400, $response->getStatusCode());

        $actual = json_decode((string)$response->getBody(), true);
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
     * @group chad
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
        $server = new OAuth2\Server($storage, ['allow_implicit' => true], []);

        $view = new Views\PhpRenderer(__DIR__ . '/../templates/');

        $route = new Authorize($server, $view);

        $headers = new Http\Headers();
        $headers->set('Content-Type', 'application/x-www-form-urlencoded');

        $stream = fopen('php://memory','r+');
        fwrite($stream, 'authorized=yes');
        rewind($stream);
        $body = new Http\Stream($stream);

        $query = 'client_id=testClientId&redirect_uri=http://example.com&response_type=code&state=test';

        $request = new Http\Request(
            'POST',
            Http\Uri::createFromString("http://example.com/authorize?{$query}"),
            $headers,
            [],
            ['REQUEST_METHOD' => 'POST'],
            $body,
            []
        );

        $response = $route($request, new Http\Response());

        $this->assertSame(302, $response->getStatusCode());

        $location = array_pop($response->getHeaders()['Location']);
        $parts = parse_url($location);
        parse_str($parts['query'], $query);

        $this->assertTrue(isset($query['code']));
        $this->assertSame('test', $query['state']);
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
        $server = new OAuth2\Server($storage, [], []);

        $query = 'client_id=testClientId&redirect_uri=http://example.com&response_type=code&state=test';
        $env = Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/authorize',
                'QUERY_STRING' => $query,
            ]
        );

        $view = new Views\PhpRenderer(__DIR__ . '/../templates');

        $route = new Authorize($server, $view);

        $request = Http\Request::createFromEnvironment($env);

        $response = $route($request, new Http\Response());

        $expected = <<<HTML
HTTP/1.1 200 OK

<form method="post">
    <label>Do You Authorize testClientId?</label><br />
    <input type="submit" name="authorized" value="yes">
    <input type="submit" name="authorized" value="no">
</form>

HTML;

        ob_start();
        echo (string)$response;
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame($expected, $actual);
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
        new Authorize($server, new \StdClass());
    }
}
