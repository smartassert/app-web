<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Exception\ApiException;
use App\FormError\Factory;
use App\Request\SuiteWriteRequest;
use App\Response\RedirectResponse;
use App\Security\ApiKey;
use App\SessionStore\RequestPayloadStore;
use SmartAssert\ApiClient\Exception\ClientExceptionInterface;
use SmartAssert\ApiClient\JobCoordinatorClient;
use SmartAssert\ApiClient\SourceClient;
use SmartAssert\ApiClient\SuiteClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class SuiteController
{
    public function __construct(
        private TwigEnvironment $twig,
        private SourceClient $sourceClient,
        private SuiteClient $suiteClient,
        private UrlGeneratorInterface $urlGenerator,
        private RequestPayloadStore $requestPayloadStore,
        private JobCoordinatorClient $jobCoordinatorClient,
    ) {}

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws ApiException
     */
    #[Route('/suites', name: 'suites', methods: ['GET'])]
    public function index(ApiKey $apiKey, Factory $formErrorFactory): Response
    {
        try {
            $sources = $this->sourceClient->list($apiKey->key);
            $suites = $this->suiteClient->list($apiKey->key);
        } catch (ClientExceptionInterface $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new Response($this->twig->render(
            'suite/index.html.twig',
            [
                'form_error' => $formErrorFactory->create(),
                'sources' => $sources,
                'suites' => $suites,
                'suite_create_request' => $this->requestPayloadStore->get(
                    SuiteWriteRequest::class,
                    'suite_create_request',
                ),
            ]
        ));
    }

    /**
     * @throws ApiException
     */
    #[Route('/suites', name: 'suite_create', methods: ['POST'])]
    public function create(ApiKey $apiKey, SuiteWriteRequest $request): Response
    {
        $response = new RedirectResponse($this->urlGenerator->generate('suites'));

        try {
            $this->suiteClient->create($apiKey->key, $request->sourceId, $request->label, $request->tests);
        } catch (\Throwable $e) {
            $this->requestPayloadStore->set($request, 'suite_create_request');

            throw new ApiException(ApiService::SOURCES, $e, $response);
        }

        return $response;
    }

    /**
     * @param non-empty-string $id
     *
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws ApiException
     */
    #[Route('/suite/{id<[A-Z90-9]{26}>}', name: 'suite_view', methods: ['GET'])]
    public function view(ApiKey $apiKey, Factory $formErrorFactory, string $id): Response
    {
        try {
            $sources = $this->sourceClient->list($apiKey->key);
            $suite = $this->suiteClient->get($apiKey->key, $id);
            $jobs = $this->jobCoordinatorClient->list($apiKey->key, $id);
        } catch (ClientExceptionInterface $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new Response($this->twig->render(
            'suite/view.html.twig',
            [
                'form_error' => $formErrorFactory->create(),
                'sources' => $sources,
                'suite_update_request' => $this->requestPayloadStore->get(
                    SuiteWriteRequest::class,
                    'suite_update_request',
                ),
                'suite' => $suite,
                'jobs' => $jobs,
            ]
        ));
    }

    /**
     * @throws ApiException
     */
    #[Route('/suite/{id<[A-Z90-9]{26}>}', name: 'suite_update', methods: ['POST'])]
    public function update(ApiKey $apiKey, SuiteWriteRequest $request): Response
    {
        $response = new RedirectResponse($this->urlGenerator->generate('suite_view', ['id' => $request->id]));

        try {
            $this->suiteClient->update(
                $apiKey->key,
                (string) $request->id,
                $request->sourceId,
                $request->label,
                $request->tests
            );
        } catch (\Throwable $e) {
            $this->requestPayloadStore->set($request, 'suite_update_request');

            throw new ApiException(ApiService::SOURCES, $e, $response);
        }

        return $response;
    }
}
