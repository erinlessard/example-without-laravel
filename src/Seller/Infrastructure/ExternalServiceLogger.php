<?php

namespace Example\Seller\Infrastructure;

use Psr\Log\LoggerInterface;

class ExternalServiceLogger implements LoggerInterface
{
    public function emergency($message, array $context = []): void
    {
        // TODO: Implement emergency() method.
    }

    public function alert($message, array $context = []): void
    {
        // TODO: Implement alert() method.
    }

    public function critical($message, array $context = []): void
    {
        // TODO: Implement critical() method.
    }

    public function error($message, array $context = []): void
    {
        // TODO: Implement error() method.
    }

    public function warning($message, array $context = []): void
    {
        // TODO: Implement warning() method.
    }

    public function notice($message, array $context = []): void
    {
        // TODO: Implement notice() method.
    }

    public function info($message, array $context = []): void
    {
        // TODO: Implement info() method.
    }

    public function debug($message, array $context = []): void
    {
        // TODO: Implement debug() method.
    }

    public function log($level, $message, array $context = []): void
    {
        // TODO: Implement log() method.
    }
}