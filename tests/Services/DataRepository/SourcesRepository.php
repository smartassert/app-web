<?php

declare(strict_types=1);

namespace App\Tests\Services\DataRepository;

class SourcesRepository extends DataRepository
{
    public function __construct()
    {
        parent::__construct('pgsql:host=localhost;port=5432;dbname=sources;user=postgres;password=password!');
    }

    public function removeAllSources(): void
    {
        $this->removeAllFor(['file_source', 'git_source', 'source']);
    }

    public function removeAllSuites(): void
    {
        $this->removeAllSources();
        $this->removeAllFor(['suite']);
    }
}
