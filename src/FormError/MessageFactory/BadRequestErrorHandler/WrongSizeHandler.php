<?php

declare(strict_types=1);

namespace App\FormError\MessageFactory\BadRequestErrorHandler;

use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;

class WrongSizeHandler implements TypeHandlerInterface
{
    public function create(string $type, BadRequestErrorInterface $error): ?string
    {
        if ('wrong_size' !== $type) {
            return null;
        }

        $size = $error->getParameter()->getRequirements()?->getSize();
        if (null === $size) {
            return 'This value is too small or too large.';
        }

        $minimum = $size->getMinimum();
        $maximum = $size->getMaximum();

        if (0 === $minimum && null === $maximum) {
            return 'This value is too small or too large.';
        }

        if (0 === $minimum && is_int($maximum)) {
            return 'This value can be no longer than ' . $maximum . ' characters long.';
        }

        return 'This value must be between ' . $minimum . ' and ' . $maximum . ' characters long.';
    }
}
