<?php

namespace CoreShop2VueStorefrontBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class OnAuthenticationFailureListener
{
    public function attachModifyResponse(AuthenticationFailureEvent $event)
    {
        $event->setResponse(new JsonResponse([
            'code' => 500,
            'result' => 'You did not sign in correctly or your account is temporarily disabled'
        ]));
    }
}
