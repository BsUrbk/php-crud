<?php

declare(strict_types=1);

namespace App\Util;

use App\Entity\RefreshToken;
use App\Exception\ExistingUserInstantiationException;
use Symfony\Component\HttpFoundation\Request;
use Exception;

trait AuthorizationTrait
{
    /**
     * Denies access if incoming request is already authorized
     */
    public function denyAuthorizedRequest(Request $request): void {
        if($request->cookies->get(RefreshToken::BEARER)) {
            throw new ExistingUserInstantiationException();
        }
    }

    public function denyUnauthorizedRequest(Request $request): void {
        if(null === $request->cookies->get(RefreshToken::REFRESH)){
            throw new Exception('User is not logged in', 400);
        }
    }
}