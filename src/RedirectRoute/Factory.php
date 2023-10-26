<?php

declare(strict_types=1);

namespace App\RedirectRoute;

use App\Enum\Routes;
use Symfony\Component\HttpFoundation\Request;

class Factory
{
    public function createFromRequest(Request $request): RedirectRoute
    {
        $name = $request->attributes->get('_route');
        if (!is_string($name)) {
            return $this->getDefault();
        }

        if (Routes::LOG_OUT_NAME->value === $name) {
            return $this->getSignIn();
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

    public function getDefault(): RedirectRoute
    {
        return new RedirectRoute(Routes::DASHBOARD_NAME->value, []);
    }

    public function getSignIn(): RedirectRoute
    {
        return new RedirectRoute(Routes::SIGN_IN_VIEW_NAME->value, []);
    }
}
