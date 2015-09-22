<?php

namespace Chadicus\Slim\OAuth2\Routes;

use Slim\Slim;

final class ReceiveCode
{
    const ROUTE = '/receive-code';

    /**
     * The slim framework application instance.
     *
     * @var Slim $slim
     */
    private $slim;

    /**
     * The template for /receive-code
     *
     * @var string
     */
    private $template;

    /**
     * Construct a new instance of ReceiveCode route.
     *
     * @param Slim   $slim     The slim framework application instance.
     * @param string $template The template for /receive-code
     */
    public function __construct(Slim $slim, $template = 'receive-code.phtml')
    {
        $this->slim = $slim;
        $this->template = $template;
    }

    /**
     * Call this class as a function.
     *
     * @return void
     */
    public function __invoke()
    {
        $this->slim->render($this->template, ['code' => $this->slim->request()->params('code')]);
    }

    /**
     * Register this route with the given Slim application and OAuth2 server
     *
     * @param Slim   $slim     The slim framework application instance.
     * @param string $template The template for /receive-code
     *
     * @return void
     */
    public static function register(Slim $slim, $template = 'receive-code.phtml')
    {
        $slim->map(self::ROUTE, new self($slim, $template))->via('GET', 'POST')->name('receive-code');
    }
}
