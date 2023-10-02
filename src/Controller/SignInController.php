<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\SignInErrorState;
use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use App\RefreshableToken\Encrypter;
use App\Security\UserCredentials;
use App\SignInRedirectResponse\Factory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\UsersClient;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class SignInController
{
    public function __construct(
        private Factory $signInRedirectResponseFactory,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function view(Request $request, TwigEnvironment $twig, Serializer $redirectRouteSerializer): Response
    {
        $email = $request->query->getString('email');
        $route = $request->query->getString('route');

        $error = $request->query->getString('error');
        if ('' !== $error && !SignInErrorState::is($error)) {
            $redirectUserIdentifier = '' === $email ? null : $email;
            $redirectRoute = '' === $route ? null : $redirectRouteSerializer->deserialize($route);

            return $this->signInRedirectResponseFactory->create(
                userIdentifier: $redirectUserIdentifier,
                error: null,
                route: $redirectRoute,
            );
        }

        $viewParameters = [
            'email' => $request->query->getString('email'),
            'route' => $request->query->get('route'),
            'error' => $error,
        ];

        return new Response($twig->render('sign_in/index.html.twig', $viewParameters));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NetworkExceptionInterface
     * @throws NonSuccessResponseException
     * @throws RequestExceptionInterface
     */
    public function handle(
        UserCredentials $userCredentials,
        RedirectRoute $redirectRoute,
        UsersClient $usersClient,
        Encrypter $tokenEncrypter,
        UrlGeneratorInterface $urlGenerator,
    ): Response {
        $userIdentifier = $userCredentials->userIdentifier;
        if (null === $userIdentifier) {
            return $this->signInRedirectResponseFactory->create(
                userIdentifier: null,
                error: SignInErrorState::EMAIL_EMPTY->value,
                route: $redirectRoute,
            );
        }

        $password = $userCredentials->password;
        if (null === $password) {
            return $this->signInRedirectResponseFactory->create(
                userIdentifier: $userIdentifier,
                error: SignInErrorState::PASSWORD_EMPTY->value,
                route: $redirectRoute,
            );
        }

        try {
            $token = $usersClient->createToken($userIdentifier, $password);

            $response = new Response(null, 302, [
                'location' => $urlGenerator->generate($redirectRoute->name, $redirectRoute->parameters),
                'content-type' => null,
            ]);
            $response->headers->setCookie(Cookie::create('token', $tokenEncrypter->encrypt($token)));

            return $response;
        } catch (UnauthorizedException) {
            return $this->signInRedirectResponseFactory->create(
                userIdentifier: $userIdentifier,
                error: SignInErrorState::UNAUTHORIZED->value,
                route: $redirectRoute,
            );
        }
    }
}
