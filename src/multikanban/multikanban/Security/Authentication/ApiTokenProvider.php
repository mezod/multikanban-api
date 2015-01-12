<?php

namespace multikanban\multikanban\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use multikanban\multikanban\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Responsible for looking up the ApiToken in the database based off of
 * the token string found in ApiTokenListener. If it's found, the related
 * User object is found and authenticated.
 */
class ApiTokenProvider implements AuthenticationProviderInterface
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Looks up the token and loads the user based on it
     *
     * @param TokenInterface $token
     * @return ApiAuthToken|TokenInterface
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @throws \Exception
     */
    public function authenticate(TokenInterface $token)
    {
        // the actual token string value from the header - e.g. ABCDEFG
        $tokenString = $token->getCredentials();

        // find the user object in the database based on the TokenString
        $user = $this->userRepository->findOneByToken($tokenString);

        if (!$user) {
            throw new BadCredentialsException('Invalid token');
        }

        $authenticatedToken = new ApiAuthToken($user->getRoles());
        $authenticatedToken->setUser($user);
        $authenticatedToken->setAuthenticated(true);

        return $authenticatedToken;
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token A TokenInterface instance
     *
     * @return Boolean true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiAuthToken;
    }

} 