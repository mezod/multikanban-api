<?php

namespace multikanban\multikanban\Security\Authentication\Exception;

use multikanban\multikanban\Security\Authentication\ApiTokenListener;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class BadAuthHeaderTypeException extends AuthenticationException
{
    public function getMessageKey()
    {
        return sprintf(
            'Unknown Authorization header type = use "%s"',
            ApiTokenListener::AUTHORIZATION_HEADER_TOKEN_KEY
        );
    }

}
