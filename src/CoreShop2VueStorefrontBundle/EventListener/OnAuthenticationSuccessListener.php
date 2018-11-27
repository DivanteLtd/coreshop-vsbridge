<?php

namespace CoreShop2VueStorefrontBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class OnAuthenticationSuccessListener
{
    public function attachModifyResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();

        $event->setData([
            'code' => 200,
            'result' => $data['token'],
            'meta' => ['refreshToken' => $data['refresh_token']]
        ]);
    }
}
