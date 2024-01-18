<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\Routes;
use App\Response\RedirectResponse;
use App\Security\ApiKey;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Http\HttpClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\SourceClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class SourceController
{
    public function __construct(
        private TwigEnvironment $twig,
        private SourceClient $sourceClient,
        private FileSourceClient $fileSourceClient,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route('/sources', name: Routes::SOURCES_NAME->value, methods: ['GET'])]
    public function index(ApiKey $apiKey): Response
    {
        return new Response($this->twig->render('source/index.html.twig', [
            'sources' => $this->sourceClient->list($apiKey->key),
        ]));
    }

    /**
     * @throws UnauthorizedException
     * @throws ErrorException
     * @throws IncompleteDataException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnexpectedContentTypeException
     * @throws HttpClientException
     * @throws UnexpectedDataException
     */
    #[Route('/sources/file', name: 'sources_add_file_source', methods: ['POST'])]
    public function addFileSource(ApiKey $apiKey, Request $request): Response
    {
        $this->fileSourceClient->create($apiKey->key, $request->request->getString('label'));

        return new RedirectResponse($this->urlGenerator->generate('sources'));
    }
}
