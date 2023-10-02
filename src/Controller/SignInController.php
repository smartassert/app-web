<?php

declare(strict_types=1);

namespace App\Controller;

use App\RedirectRoute\RedirectRoute;
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
        return new Response($this->twig->render(
            'sign_in/index.html.twig',
            [
                'email' => $request->query->get('email'),
                'route' => $request->query->get('route'),
            ]
        ));
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
    ): Response {
        $userIdentifier = $userCredentials->userIdentifier;
        if (null === $userIdentifier) {
            $this->addFlash('empty-user-identifier', null);

            return $this->createSignInRedirectResponse();
        }

        $password = $userCredentials->password;
        if (null === $password) {
            $this->addFlash('empty-password', null);

            return $this->createSignInRedirectResponse($userIdentifier);
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
            $this->addFlash('unauthorized', null);

            return $this->createSignInRedirectResponse($userIdentifier);
        }
    }

    private function createSignInRedirectResponse(?string $userIdentifier = null): Response
    {
        $routeParameters = [];
        if (is_string($userIdentifier)) {
            $routeParameters['email'] = $userIdentifier;
        }

        return new Response(null, 302, [
            'location' => $this->urlGenerator->generate('sign_in_view', $routeParameters),
            'content-type' => null,
        ]);
    }
}
