<?php

declare(strict_types=1);

namespace App\Session;

class Store
{
    private string $data = '';

    public function set(string $data): void
    {
        $this->data = $data;
    }

    public function get(): string
    {
        return $this->data;
    }
}
