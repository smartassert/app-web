<?php

declare(strict_types=1);

namespace App\Security\TokenAuthentication;

use App\RedirectRoute\Factory as RedirectRouteFactory;
use App\RefreshableToken\Encrypter;
use App\Response\RedirectResponseFactory;
use App\Security\RequestTokenExtractor;
use App\Security\User;
use SmartAssert\ApiClient\Exception\Http\HttpClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\UsersClient;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

readonly class Authenticator implements AuthenticatorInterface
{
    public function __construct(
        private RequestTokenExtractor $tokenExtractor,
        private UsersClient $usersClient,
        private RedirectRouteFactory $redirectRouteFactory,
        private RedirectResponseFactory $redirectResponseFactory,
        private FirewallMap $firewallMap,
        private Encrypter $tokenEncrypter,
        private HttpMessageFactoryInterface $psrHttpFactory,
    ) {
    }

    public function supports(Request $request): bool
    {
        $firewallConfig = $this->firewallMap->getFirewallConfig($request);

        return $firewallConfig instanceof FirewallConfig && 'secured' === $firewallConfig->getName();
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new PostAuthenticationToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
    }

    /**
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function authenticate(Request $request): Passport
    {
        $token = $this->tokenExtractor->extract(
            $this->psrHttpFactory->createRequest($request)
        );

        if (null === $token) {
            throw new AuthenticationCredentialsNotFoundException();
        }

        try {
            $remoteUser = $this->usersClient->verifyToken($token->token);
            $user = new User($remoteUser->userIdentifier, $token);
        } catch (UnauthorizedException) {
            try {
                $newToken = $this->usersClient->refreshToken($token->refreshToken);
                $request->cookies->set('token', $this->tokenEncrypter->encrypt($newToken));

                return $this->authenticate($request);
            } catch (UnauthorizedException) {
                throw new BadCredentialsException();
            }
        }

        return new SelfValidatingPassport(new UserBadge(
            $user->getUserIdentifier(),
            function () use ($user) {
                return $user;
            }
        ));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->redirectResponseFactory->createForSignIn(
            userIdentifier: null,
            route: $this->redirectRouteFactory->createFromRequest($request)
        );
    }
}
