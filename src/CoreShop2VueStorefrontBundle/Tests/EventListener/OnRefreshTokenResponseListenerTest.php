<?php

namespace CoreShop2VueStorefrontBundle\Tests\EventListener;

use CoreShop2VueStorefrontBundle\EventListener\OnRefreshTokenResponseListener;
use CoreShop2VueStorefrontBundle\Tests\MockeryTestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class OnRefreshTokenResponseListenerTest extends MockeryTestCase
{
    /** @test */
    public function itShouldAddRefreshTokenToRequest()
    {
        $responseEvent = mock(GetResponseEvent::class);
        $request = mock(Request::class);
        $headerBag = mock(HeaderBag::class);
        $parameterBag = mock(ParameterBag::class);

        $request->shouldReceive('getPathInfo')->once()->andReturn('/vsbridge/user/refresh');

        $request->headers = $headerBag;
        $headerBag->shouldReceive('replace')->with(['Content-Type' => 'x-type'])->once();

        $refreshToken = 'fazzBaarrRefresh';
        $content = "{\"refreshToken\":\"$refreshToken\"}";
        $request->shouldReceive('getContent')->once()->andReturn($content);

        $request->request = $parameterBag;
        $parameterBag->shouldReceive('add')->once()->with(['refresh_token' => $refreshToken]);

        $responseEvent->shouldReceive('getRequest')->once()->andReturn($request);

        $this->listener->onKernelRequest($responseEvent);

        $this->assertTrue(true);
    }

    /** @test */
    public function itShouldNotAddRefreshTokenForOtherRequestThanRefresh()
    {
        $responseEvent = mock(GetResponseEvent::class);
        $request = mock(Request::class);
        $request->shouldReceive('getPathInfo')->once()->andReturn('/some/other/endpoint');

        $responseEvent->shouldReceive('getRequest')->once()->andReturn($request);

        $this->assertFalse($this->listener->onKernelRequest($responseEvent));
    }

    public function setUp()
    {
        $this->listener = new OnRefreshTokenResponseListener();
    }

    /** @var OnRefreshTokenResponseListener $listener */
    private $listener;
}
