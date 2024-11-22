<?php

declare(strict_types=1);

namespace Tests\Traits;

use Slim\App;

trait UsesContainer
{
    protected App $app;

    protected function setUp(): void
    {
        // Not the nicest way to reset container state, and using a hardcoded path no less!
        $this->app = require __DIR__ . '/../../src/bootstrap.php';
    }
}
