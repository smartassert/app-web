<?php

declare(strict_types=1);

namespace App\Controller;

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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SignInController extends AbstractController
{
    public function __construct(
        private readonly TwigEnvironment $twig,
        private readonly RouterInterface $router,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws ClientExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NetworkExceptionInterface
     * @throws NonSuccessResponseException
     * @throws RequestExceptionInterface
     */
    #[Route('/sign-in/', name: 'sign-in', methods: ['GET', 'POST'])]
    public function index(UserCredentials $userCredentials, Request $request, UsersClient $usersClient): Response
    {
        if (Request::METHOD_POST === $request->getMethod()) {
            $response = new Response(
                null,
                302,
                [
                    'location' => $this->router->generate('sign-in'),
                    'content-type' => null,
                ]
            );

            $userIdentifier = $userCredentials->userIdentifier;
            if (null === $userIdentifier) {
                $this->addFlash('error', 'empty-user-identifier');

                return $response;
            }

            $password = $userCredentials->password;
            if (null === $password) {
                return $response;
            }

            try {
                $token = $usersClient->createToken($userIdentifier, $password);
                $response->headers->setCookie(Cookie::create('token', $token->token));
            } catch (UnauthorizedException) {
            }

            return $response;
        }

        return new Response($this->twig->render('sign_in/index.html.twig'));
    }
}
