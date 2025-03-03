<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Service\JwtService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JwtAuthenticator extends AbstractAuthenticator
{
    private $jwtService;
    private $userRepository;

    public function __construct(JwtService $jwtService, UserRepository $userRepository)
    {
        $this->jwtService = $jwtService;
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request): bool
    {
        if (preg_match('#^/(login|register)#', $request->getPathInfo())) {
            return false;
        }

        return $request->cookies->has('token');
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->cookies->get('token');

        $parsedToken = $this->jwtService->parseToken($token);

        if (!$parsedToken) {
            throw new AuthenticationException('JWT token is invalid');
        }

        $userId = $parsedToken->claims()->get('uid');
        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        return new SelfValidatingPassport(new UserBadge($userId, function () use ($user) {
            return $user;
        }));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?JsonResponse
    {
        return new JsonResponse(['error' => $exception->getMessage()], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?JsonResponse
    {
        return null;
    }

    public function start(): JsonResponse
    {
        return new JsonResponse(['error' => 'Auth required'], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
