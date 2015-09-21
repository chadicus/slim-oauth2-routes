<?php

namespace Chadicus\Slim\OAuth2\Routes;

use Slim\Slim;

class ReceiveCode
{
    const ROUTE = '/receive-code';

    private $slim;
    private $template;

    public function __construct(Slim $slim, $template = null)
    {
        $this->slim = $slim;
        $this->template = $template ?: self::getDefaultTemplate();
    }

    public function __invoke()
    {
        $this->slim->render($this->template, ['code' => $this->slim->request()->params('code')]);
    }

    /**
     * Register this route with the given Slim application and OAuth2 server
     *
     * @param Slim $slim   The slim framework application instance.
     *
     * @return void
     */
    public static function register(Slim $slim)
    {
        $slim->map(self::ROUTE, new self($slim))->via('GET', 'POST')->name('receive-code');
    }

    private static function getDefaultTemplate()
    {
        return __DIR__ . '/../templates/receive-code.phtml';
    }
}
