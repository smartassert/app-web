<?php

declare(strict_types=1);

namespace App\RedirectRoute;

use Symfony\Component\HttpFoundation\Request;

class Factory
{
    public function createFromRequest(Request $request): RedirectRoute
    {
        $name = $request->attributes->get('_route');
        if (!is_string($name)) {
            $name = null;
        }

        $queryParameters = [];
        if ('GET' === $request->getMethod()) {
            foreach ($request->query as $key => $value) {
                if (is_string($key) && (is_string($value) || is_int($value))) {
                    $queryParameters[$key] = $value;
                }
            }
        }

        return new RedirectRoute($name, $queryParameters);
    }
}
