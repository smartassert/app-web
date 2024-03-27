<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ApiService;
use App\Exception\ApiException;
use App\FormError\Factory;
use App\Request\SuiteCreateRequest;
use App\Request\SuiteUpdateRequest;
use App\Response\RedirectResponse;
use App\Security\ApiKey;
use App\SessionStore\RequestPayloadStore;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\JobCoordinatorClient;
use SmartAssert\ApiClient\SourceClient;
use SmartAssert\ApiClient\SuiteClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    ) {
    }

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
        } catch (ClientException $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new Response($this->twig->render(
            'suite/index.html.twig',
            [
                'form_error' => $formErrorFactory->create(),
                'sources' => $sources,
                'suites' => $suites,
                'suite_create_request' => $this->requestPayloadStore->get(SuiteCreateRequest::class),
            ]
        ));
    }

    /**
     * @throws ApiException
     */
    #[Route('/suites', name: 'suite_create', methods: ['POST'])]
    public function create(ApiKey $apiKey, SuiteCreateRequest $request): Response
    {
        $response = new RedirectResponse($this->urlGenerator->generate('suites'));

        try {
            $this->suiteClient->create($apiKey->key, $request->sourceId, $request->label, $request->tests);
        } catch (\Throwable $e) {
            $this->requestPayloadStore->set($request);

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
        } catch (ClientException $e) {
            throw new ApiException(ApiService::SOURCES, $e);
        }

        return new Response($this->twig->render(
            'suite/view.html.twig',
            [
                'form_error' => $formErrorFactory->create(),
                'sources' => $sources,
                'suite_update_request' => $this->requestPayloadStore->get(SuiteUpdateRequest::class),
                'suite' => $suite,
                'jobs' => $jobs,
            ]
        ));
    }

    /**
     * @throws ApiException
     */
    #[Route('/suite/{id<[A-Z90-9]{26}>}', name: 'suite_update', methods: ['POST'])]
    public function update(ApiKey $apiKey, SuiteUpdateRequest $request): Response
    {
        $response = new RedirectResponse($this->urlGenerator->generate('suite_view', ['id' => $request->id]));

        try {
            $this->suiteClient->update(
                $apiKey->key,
                $request->id,
                $request->sourceId,
                $request->label,
                $request->tests
            );
        } catch (\Throwable $e) {
            $this->requestPayloadStore->set($request);

            throw new ApiException(ApiService::SOURCES, $e, $response);
        }

        return $response;
    }

    #[Route('/suite/{id<[A-Z90-9]{26}>}/run', name: 'suite_run', methods: ['POST'])]
    public function run(string $id): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('suite_view', ['id' => $id]));
    }
}
