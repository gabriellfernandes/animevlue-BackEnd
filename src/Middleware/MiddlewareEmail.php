<?php

namespace App\Middleware;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MiddlewareEmail
{
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function validateEmailToken($authorizationHeader, User $entity = new User()): array
    {
        if ($authorizationHeader && preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            $token = $matches[1];
            $jwtUserToken = new JWTUserToken();
            $jwtUserToken->setRawToken($token);
            $decodedToken = $this->jwtManager->decode($jwtUserToken);
            $emailToken = $decodedToken['username'];
        }

        return ['valid' => $entity->getEmail() == $emailToken, 'email' => $emailToken];
    }
}
