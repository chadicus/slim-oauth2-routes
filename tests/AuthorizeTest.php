<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\Authorize;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use OAuth2;
use OAuth2\Storage;
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

        $response = $route(new ServerRequest(), new Response());

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

        $request = new ServerRequest([], [], null, null, 'php://input', [], [], ['client_id' => 'invalidClientId']);

        $response = $route($request, new Response());

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

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'authorized=yes');
        rewind($stream);
        $body = new Stream($stream);

        $request = new ServerRequest(
            [],
            [],
            'http://example.com/authorize',
            'POST',
            $body,
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [
                'client_id' => 'testClientId',
                'redirect_uri' => 'http://example.com',
                'response_type' => 'code',
                'state' => 'test',
            ],
            ['authorized' => 'yes']
        );

        $response = $route($request, new Response());

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

        $view = new Views\PhpRenderer(__DIR__ . '/../templates');

        $route = new Authorize($server, $view);

        $request = new ServerRequest(
            [],
            [],
            null,
            'GET',
            'php://input',
            [],
            [],
            [
                'client_id' => 'testClientId',
                'redirect_uri' => 'http://example.com',
                'response_type' => 'code',
                'state' => 'test',
            ]
        );

        $response = $route($request, new Response());

        $this->assertSame(200, $response->getStatusCode());
        $expected = <<<HTML
<form method="post">
    <label>Do You Authorize testClientId?</label><br />
    <input type="submit" name="authorized" value="yes">
    <input type="submit" name="authorized" value="no">
</form>

HTML;

        $this->assertSame($expected, (string)$response->getBody());
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

    /**
     * Verify behavior of __invoke() with user_id parameter.
     *
     * @test
     * @covers ::__invoke
     *
     * @return void
     */
    public function invokeWithUserId()
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

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'authorized=yes');
        rewind($stream);
        $request = $this->getRequest(new Stream($stream));
        $response = $route($request, new Response());

        $this->assertSame(302, $response->getStatusCode());

        $location = array_pop($response->getHeaders()['Location']);
        $parts = parse_url($location);
        parse_str($parts['query'], $query);

        $this->assertTrue(isset($query['code']));
        $this->assertSame('test', $query['state']);

        $expires = $storage->authorizationCodes[$query['code']]['expires'];

        $this->assertSame(
            [
                $query['code'] => [
                    'code' => $query['code'],
                    'client_id' => 'testClientId',
                    'user_id' => 'theUsername',
                    'redirect_uri' => 'http://example.com',
                    'expires' => $expires,
                    'scope' => null,
                    'id_token' => null,
                ],
            ],
            $storage->authorizationCodes
        );
    }

    private function getRequest(Stream $body)
    {
        return new ServerRequest(
            [],
            [],
            'http://example.com/authorize',
            'POST',
            $body,
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [
                'client_id' => 'testClientId',
                'redirect_uri' => 'http://example.com',
                'response_type' => 'code',
                'state' => 'test',
                'user_id' => 'theUsername',
            ],
            ['authorized' => 'yes']
        );
    }
}
