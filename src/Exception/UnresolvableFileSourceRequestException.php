<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Request;

class UnresolvableFileSourceRequestException extends \Exception
{
    public function __construct(
        public Request $request,
    ) {
        parent::__construct();
    }
}
