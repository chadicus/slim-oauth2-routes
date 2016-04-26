<?php

namespace Chadicus\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Http\MessageBridge;
use OAuth2;
use Slim\Slim;

/**
 * The revoke class.
 *
 */
class Revoke
{
    const ROUTE = '/revoke';

    /**
     * The slim framework application instance.
     *
     * @var Slim
     */
    private $slim;

    /**
     * The oauth2 server instance.
     *
     * @var OAuth2\Server
     */
    private $server;

    /**
     * Construct a new instance of Authorize.
     *
     * @param Slim          $slim   The slim framework application instance.
     * @param OAuth2\Server $server The oauth2 server imstance.
     */
    public function __construct(Slim $slim, OAuth2\Server $server)
    {
        $this->slim = $slim;
        $this->server = $server;
    }

    /**
     * Call this class as a function.
     *
     * @return void
     */
    public function __invoke()
    {
        $request = MessageBridge::newOAuth2Request($this->slim->request());
        MessageBridge::mapResponse(
            $this->server->handleRevokeRequest($request),
            $this->slim->response()
        );
    }

    /**
     * Register this route with the given Slim application and OAuth2 server
     *
     * @param Slim          $slim   The slim framework application instance.
     * @param OAuth2\Server $server The oauth2 server instance.
     *
     * @return void
     */
    public static function register(Slim $slim, OAuth2\Server $server)
    {
        $slim->post(self::ROUTE, new static($slim, $server))->name('revoke');
    }
}
