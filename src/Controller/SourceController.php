<?php

declare(strict_types=1);

namespace App\Controller;

use App\Response\RedirectResponse;
use App\Security\ApiKey;
use SmartAssert\ApiClient\SourceClient;
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
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route('/sources', name: 'sources', methods: ['GET'])]
    public function index(ApiKey $apiKey): Response
    {
        return new Response($this->twig->render('source/index.html.twig', [
            'sources' => $this->sourceClient->list($apiKey->key),
        ]));
    }

    #[Route('/sources/file', name: 'sources_add_file_source', methods: ['POST'])]
    public function addFileSource(): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('sources'));
    }
}
