<?php

declare(strict_types=1);

namespace App\Security\CredentialAuthentication;

use App\Exception\BadCredentialsException;
use App\Exception\PasswordMissingException;
use App\Exception\SignInExceptionInterface;
use App\Exception\UserIdentifierMissingException;
use App\RedirectRoute\Serializer;
use App\Response\RedirectResponse;
use App\Response\RedirectResponseFactory;
use App\Security\ApiKeyBadge;
use App\Security\User;
use SmartAssert\ApiClient\Exception\Http\FailedRequestException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;
use SmartAssert\ApiClient\Exception\IncompleteResponseDataException;
use SmartAssert\ApiClient\UsersClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

readonly class Authenticator implements AuthenticatorInterface
{
    public function __construct(
        private UsersClient $usersClient,
        private RedirectResponseFactory $redirectResponseFactory,
        private UrlGeneratorInterface $urlGenerator,
        private Serializer $serializer,
    ) {
    }

    public function supports(Request $request): bool
    {
        return 'sign_in_handle' === $request->attributes->get('_route');
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $token = new PostAuthenticationToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());

        $apiKeyBadge = $passport->getBadge(ApiKeyBadge::class);
        if ($apiKeyBadge instanceof ApiKeyBadge) {
            $token->setAttribute('api_key', $apiKeyBadge->apiKey);
        }

        return $token;
    }

    /**
     * @throws HttpException
     * @throws IncompleteResponseDataException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws FailedRequestException
     */
    public function authenticate(Request $request): Passport
    {
        $userIdentifier = $request->request->get('user-identifier');
        if (!is_string($userIdentifier) || '' === $userIdentifier) {
            throw new UserIdentifierMissingException();
        }

        $password = $request->request->get('password');
        if (!is_string($password) || '' === $password) {
            throw new PasswordMissingException($userIdentifier);
        }

        try {
            $token = $this->usersClient->createToken($userIdentifier, $password);
            $apiKey = $this->usersClient->getApiKey($token->token);
            $user = new User($userIdentifier, $token);
        } catch (UnauthorizedException) {
            throw new BadCredentialsException($userIdentifier);
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $user->getUserIdentifier(),
                function () use ($user) {
                    return $user;
                }
            ),
            [
                new ApiKeyBadge($apiKey->key),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        if ($token->hasAttribute('api_key')) {
            $request->getSession()->set('api_key', $token->getAttribute('api_key'));
        }

        $redirectRoute = $this->serializer->deserialize($request->request->getString('route'));

        return new RedirectResponse(
            $this->urlGenerator->generate($redirectRoute->name, $redirectRoute->parameters)
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($exception instanceof SignInExceptionInterface) {
            $session = $request->getSession();
            if ($session instanceof Session) {
                $session->getFlashBag()->set('error', $exception->getErrorState()->value);
            }

            return $this->redirectResponseFactory->createForSignIn(
                userIdentifier: $exception->getUserIdentifier(),
                route: $this->serializer->deserialize($request->request->getString('route')),
            );
        }

        return null;
    }
}
