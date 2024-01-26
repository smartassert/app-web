<?php

declare(strict_types=1);

namespace App\Exception;

use App\Enum\ApiService;
use App\RedirectRoute\RedirectRoute;

class ApiException extends \Exception
{
    public function __construct(
        public readonly ApiService $apiService,
        public readonly \Throwable $exception,
        public readonly ?RedirectRoute $redirectRoute = null,
    ) {
        parent::__construct($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
    }
}
