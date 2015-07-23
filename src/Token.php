<?php

namespace Chadicus\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Http\MessageBridge;
use OAuth2;
use Slim\Slim;

class Token
{
    const ROUTE = '/token';

    /**
     * The slim framework application.
     *
     * @var Slim
     */
    private $slim;

    /**
     * The OAuth2 server instance.
     *
     * @var OAuth2\Server
     */
    private $server;

    /**
     * Create a new instance of the Token route.
     *
     * @param Slim          $slim   The slim framework application instance.
     * @param OAuth2\Server $server The oauth2 server imstance.
     */
    public function __construct(Slim $slim, OAuth2\Server $server)
    {
        $this->slim = $slim;
        $this->server = $server;
    }

    public function __invoke()
    {
        $request = MessageBridge::newOAuth2Request($this->slim->request());
        MessageBridge::mapResponse(
            $this->server->handleTokenRequest($request),
            $this->slim->response()
        );
    }

    /**
     * Register this route with the given Slim application and OAuth2 server
     *
     * @param Slim          $slim   The slim framework application instance.
     * @param OAuth2\Server $server The oauth2 server imstance.
     *
     * @return void
     */
    public static function register(Slim $slim, OAuth2\Server $server)
    {
        $slim->post(self::ROUTE, new static($slim, $server))->name('token');
    }
}
