<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\Response;

class RedirectResponse extends Response
{
    public function __construct(string $url)
    {
        parent::__construct(
            null,
            302,
            [
                'content-type' => null,
                'location' => $url,
            ]
        );
    }
}
