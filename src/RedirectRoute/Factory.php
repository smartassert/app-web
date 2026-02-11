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

        $redirectRouteParameters = [];
        if ('GET' === $request->getMethod()) {
            foreach ($request->query as $key => $value) {
                if (is_string($key) && (is_string($value) || is_int($value))) {
                    $redirectRouteParameters[$key] = $value;
                }
            }
        }

        foreach ($request->attributes as $key => $value) {
            if (is_string($key) && !str_starts_with($key, '_') && (is_string($value) || is_int($value))) {
                $redirectRouteParameters[$key] = $value;
            }
        }

        return new RedirectRoute($name, $redirectRouteParameters);
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
