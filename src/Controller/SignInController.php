<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\SignInErrorState;
use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use App\RefreshableToken\Encrypter;
use App\Security\UserCredentials;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SignInController extends AbstractController
{
    public function __construct(
        private readonly TwigEnvironment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function view(Request $request): Response
    {
        $email = $request->query->getString('email');
        $route = $request->query->getString('route');

        $error = $request->query->getString('error');
        if ('' !== $error && !$this->isErrorState($error)) {
            $routeParameters = [];
            if ('' !== $email) {
                $routeParameters['email'] = $email;
            }

            if ('' !== $route) {
                $routeParameters['route'] = $route;
            }

            return new Response(null, 302, [
                'location' => $this->urlGenerator->generate('sign_in_view', $routeParameters),
            ]);
        }

        $viewParameters = [
            'email' => $request->query->getString('email'),
            'route' => $request->query->get('route'),
            'error' => $error,
        ];

        return new Response($this->twig->render('sign_in/index.html.twig', $viewParameters));
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
        Serializer $redirectRouteSerializer,
    ): Response {
        $userIdentifier = $userCredentials->userIdentifier;
        if (null === $userIdentifier) {
            return $this->createSignInRedirectResponse(
                errorState: SignInErrorState::EMAIL_EMPTY,
                redirectRoute: $redirectRouteSerializer->serialize($redirectRoute),
            );
        }

        $password = $userCredentials->password;
        if (null === $password) {
            return $this->createSignInRedirectResponse(
                userIdentifier: $userIdentifier,
                errorState: SignInErrorState::PASSWORD_EMPTY,
                redirectRoute: $redirectRouteSerializer->serialize($redirectRoute),
            );
        }

        try {
            $token = $usersClient->createToken($userIdentifier, $password);

            $response = new Response(null, 302, [
                'location' => $this->urlGenerator->generate($redirectRoute->name, $redirectRoute->parameters),
                'content-type' => null,
            ]);
            $response->headers->setCookie(Cookie::create('token', $tokenEncrypter->encrypt($token)));

            return $response;
        } catch (UnauthorizedException) {
            return $this->createSignInRedirectResponse(
                userIdentifier: $userIdentifier,
                errorState: SignInErrorState::UNAUTHORIZED,
                redirectRoute: $redirectRouteSerializer->serialize($redirectRoute),
            );
        }
    }

    private function createSignInRedirectResponse(
        ?string $userIdentifier = null,
        ?SignInErrorState $errorState = null,
        ?string $redirectRoute = null,
    ): Response {
        $routeParameters = [];
        if (is_string($userIdentifier) && '' !== $userIdentifier) {
            $routeParameters['email'] = $userIdentifier;
        }

        if (null !== $errorState) {
            $routeParameters['error'] = $errorState->value;
        }

        if (null !== $redirectRoute) {
            $routeParameters['route'] = $redirectRoute;
        }

        return new Response(null, 302, [
            'location' => $this->urlGenerator->generate('sign_in_view', $routeParameters),
            'content-type' => null,
        ]);
    }

    private function isErrorState(string $error): bool
    {
        foreach (SignInErrorState::cases() as $errorState) {
            if ($error === $errorState->value) {
                return true;
            }
        }

        return false;
    }
}
