<?php

declare(strict_types=1);

use Example\Seller\Infrastructure\Api\Rest\ApiController;
use Example\Seller\Infrastructure\ExternalServiceLogger;
use Example\Seller\Infrastructure\Persistence\InMemoryStorage;
use Example\Seller\Infrastructure\Persistence\StorageInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponseArgs;

$container = new \DI\Container([
    StorageInterface::class => \DI\autowire(InMemoryStorage::class),
    LoggerInterface::class => \DI\autowire(ExternalServiceLogger::class),
]);

$container->set(\Psr\Http\Message\ServerRequestFactoryInterface::class, function (ContainerInterface $container) {
    return new \Nyholm\Psr7\Factory\Psr17Factory();
});

$app = AppFactory::create(container: $container);
$routeCollector = $app->getRouteCollector();
$routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());
$app->post('/api/sellers', [ApiController::class, 'create']);
$app->get('/api/sellers/{seller_id}', [ApiController::class, 'show']);
$app->get('/api/sellers', [ApiController::class, 'list']);

return $app;