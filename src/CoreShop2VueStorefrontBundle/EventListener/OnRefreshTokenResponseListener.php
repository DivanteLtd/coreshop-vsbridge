<?php

namespace CoreShop2VueStorefrontBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class OnRefreshTokenResponseListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getPathInfo() !== '/vsbridge/user/refresh') {
            return false;
        }

        $request->headers->replace(['Content-Type' => 'x-type']);

        $data = json_decode($request->getContent(), true);

        $request->request->add(['refresh_token' => $data['refreshToken']]);
    }

    /**
     * @param FilterResponseEvent $event
     * @return bool
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getPathInfo() !== '/vsbridge/user/refresh') {
            return false;
        }

        $data = json_decode($event->getResponse()->getContent(), true);

        $event->getResponse()->setContent(json_encode([
            'code' => 200,
            'result' => $data['result']
        ]));
    }
}
