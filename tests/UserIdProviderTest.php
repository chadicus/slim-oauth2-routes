<?php

namespace ChadicusTest\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Routes\UserIdProvider;
use Zend\Diactoros\ServerRequest;

/**
 * @coversDefaultClass \Chadicus\Slim\OAuth2\Routes\UserIdProvider
 */
final class UserIdProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verify basic behavior of getUserId()
     *
     * @test
     * @covers ::getUserId
     *
     * @return void
     */
    public function getUserId()
    {
        $request = new ServerRequest([], [], null, 'GET', 'php://input', [], [], ['user_id' => 'the user id']);
        $this->assertSame('the user id', (new UserIdProvider())->getUserId($request));
    }

    /**
     * Verify behavior of getUserId() when no user_id param is given.
     *
     * @test
     * @covers ::getUserId
     *
     * @return void
     */
    public function getUserIdParamNotFound()
    {
        $this->assertNull((new UserIdProvider())->getUserId(new ServerRequest()));
    }
}
