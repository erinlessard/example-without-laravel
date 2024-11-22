<?php

declare(strict_types=1);

namespace Example\Seller\Infrastructure\Api\Rest;

use Example\Seller\Application\Command\CreateSeller\CreateSellerCommand;
use Example\Seller\Application\Command\CreateSeller\CreateSellerCommandHandler;
use Example\Seller\Application\Query\ShowSeller\ShowSellerQuery;
use Example\Seller\Application\Query\ShowSeller\ShowSellerQueryHandler;
use Slim\App;

require_once __DIR__ . '/../../../../vendor/autoload.php';

/** @var App $app */
$app = require_once __DIR__ . '/../../../bootstrap.php';

$args = getopt('', ['name:', 'product:', 'payout:']);

/** @var CreateSellerCommandHandler $createSellerCommandHandler */
$createSellerCommandHandler = $app->getContainer()->get(CreateSellerCommandHandler::class);
$sellerId = $createSellerCommandHandler->handle(new CreateSellerCommand(
    name: $args['name'],
    description: 'test description',
    isActive: true,
    soldProductTypes: $args['product'],
    payoutAmount: (int) $args['payout'],
    payoutCurrency: 'GBP',
));

/** @var ShowSellerQueryHandler $showSellerQueryHandler */
$showSellerQueryHandler = $app->getContainer()->get(ShowSellerQueryHandler::class);
$dto = $showSellerQueryHandler->handle(new ShowSellerQuery($sellerId));

echo json_encode($dto, JSON_PRETTY_PRINT);
