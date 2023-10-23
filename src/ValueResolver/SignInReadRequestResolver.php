<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\RedirectRoute\Serializer;
use App\Request\SignInReadRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class SignInReadRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private Serializer $serializer,
    ) {
    }

    /**
     * @return SignInReadRequest[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (SignInReadRequest::class !== $argument->getType()) {
            return [];
        }

        $email = $request->query->getString('email');
        if ('' === $email) {
            $email = null;
        }

        $serializedRoute = $request->query->getString('route');
        $route = $this->serializer->deserialize($serializedRoute);

        $error = null;
        $session = $request->getSession();
        if ($session instanceof Session) {
            $errors = $session->getFlashBag()->get('error');
            $session->getFlashBag()->set('error', '');
            $error = $errors[0] ?? null;
            if (!is_string($error) || '' === $error) {
                $error = null;
            }
        }

        return [new SignInReadRequest($email, $route, $error)];
    }
}
