<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Exception\ApiException;
use App\Response\RedirectResponse;
use App\Security\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\JobCoordinatorClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class JobController
{
    public function __construct(
        private TwigEnvironment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private JobCoordinatorClient $jobCoordinatorClient,
    ) {
    }

    /**
     * @param non-empty-string $suiteId
     *
     * @throws ApiException
     */
    #[Route('/job/{suiteId<[A-Z90-9]{26}>}', name: 'job_create', methods: ['POST'])]
    public function create(ApiKey $apiKey, string $suiteId): Response
    {
        try {
            $job = $this->jobCoordinatorClient->create($apiKey->key, $suiteId, 600);
        } catch (ClientException $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new RedirectResponse($this->urlGenerator->generate('job_view', ['id' => $job->summary->id]));
    }

    /**
     * @param non-empty-string $id
     *
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws ApiException
     */
    #[Route('/job/{id<[A-Z90-9]{26}>}', name: 'job_view', methods: ['GET'])]
    public function view(ApiKey $apiKey, string $id): Response
    {
        try {
            $job = $this->jobCoordinatorClient->get($apiKey->key, $id);
        } catch (ClientException $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new Response($this->twig->render(
            'job/view.html.twig',
            [
                'job' => $job,
            ]
        ));
    }
}
