<?php

namespace Chadicus\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Http\MessageBridge;
use OAuth2;
use Slim\Slim;

/**
 * Slim route for /authorization endpoint.
 */
class Authorize
{
    const ROUTE = '/authorize';

    /**
     * The slim framework application instance.
     *
     * @var Slim
     */
    private $slim;

    /**
     * The oauth2 server imstance.
     *
     * @var OAuth2\Server
     */
    private $server;

    /**
     * The template for /authorize
     *
     * @var string
     */
    private $template;

    /**
     * Construct a new instance of Authorize.
     *
     * @param Slim          $slim     The slim framework application instance.
     * @param OAuth2\Server $server   The oauth2 server imstance.
     * @param string        $template The template for /authorize.
     */
    public function __construct(Slim $slim, OAuth2\Server $server, $template = 'authorize.phtml')
    {
        $this->slim = $slim;
        $this->server = $server;
        $this->template = $template;
    }

    /**
     * Call this class as a function.
     *
     * @return void
     */
    public function __invoke()
    {
        $request = MessageBridge::newOAuth2Request($this->slim->request());
        $response = new OAuth2\Response();
        $isValid = $this->server->validateAuthorizeRequest($request, $response);
        if (!$isValid) {
            MessageBridge::mapResponse($response, $this->slim->response());
            return;
        }

        $authorized = $this->slim->request()->params('authorized');
        if (empty($authorized)) {
            $this->slim->render($this->template, ['client_id' => $request->query('client_id', false)]);
            return;
        }

        $this->server->handleAuthorizeRequest($request, $response, $authorized === 'yes');

        MessageBridge::mapResponse($response, $this->slim->response());
    }

    /**
     * Register this route with the given Slim application and OAuth2 server
     *
     * @param Slim          $slim     The slim framework application instance.
     * @param OAuth2\Server $server   The oauth2 server imstance.
     * @param string        $template The template for /authorize.
     *
     * @return void
     */
    public static function register(Slim $slim, OAuth2\Server $server, $template = 'authorize.phtml')
    {
        $slim->map(self::ROUTE, new self($slim, $server, $template))->via('GET', 'POST')->name('authorize');
    }
}
