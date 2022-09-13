<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class ApiValidationSubscriber implements EventSubscriberInterface{
    public function onKernelRequest(RequestEvent $e){
        if(!$e->isMainRequest()){
            return;
        }

        $req = $e->getRequest();

        if($req->isMethodSafe(false)){
            return;
        }

        if($req->headers->get('Content-Type') != 'application/json'){
            $res = new JsonResponse(['message' => 'Invalid content type'], 415);
            $e->setResponse($res);
            return;
        }
    }

    public static function getSubscribedEvents()
    {
        return[
            'kernel.request' => 'onKernelRequest'
        ];
    }
}