<?php

namespace CoreShop2VueStorefrontBundle\Tests\EventListener;

use CoreShop2VueStorefrontBundle\EventListener\OnAuthenticationSuccessListener;
use CoreShop2VueStorefrontBundle\Tests\MockeryTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class OnAuthenticationSuccessListenerTest extends MockeryTestCase
{
    /** @test */
    public function itShouldModifySuccessResponseBody()
    {
        $event = mock(AuthenticationSuccessEvent::class);
        $token = 'foobazz2232323';
        $refreshToken = '22322xccxc44334';

        $data = ['token' => $token, 'refresh_token' => $refreshToken];

        $event->shouldReceive('getData')->once()->andReturn($data);

        $responseData = ['code' => 200, 'result' => $token, 'meta' => ['refreshToken' => $refreshToken]];
        $event->shouldReceive('setData')->once()->withArgs([$responseData])->andReturnNull();

        $this->listener->attachModifyResponse($event);

        $this->assertTrue(true);
    }

    public function setUp()
    {
        $this->listener = new OnAuthenticationSuccessListener();
    }

    /** @var OnAuthenticationSuccessListener */
    private $listener;
}
