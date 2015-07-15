<?php

namespace Chadicus\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Http\MessageBridge;
use OAuth2;
use Slim\Slim;

class Token
{
    private $slim;
    private $server;

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
}
