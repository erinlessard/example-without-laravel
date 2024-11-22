<?php

declare(strict_types=1);

namespace Example\Seller\Infrastructure\Api\Rest;

use Example\Seller\Application\Command\CreateSeller\CreateSellerCommand;
use Example\Seller\Application\Command\CreateSeller\CreateSellerCommandHandler;
use Example\Seller\Application\Query\ListSellers\ListSellersQuery;
use Example\Seller\Application\Query\ListSellers\ListSellersQueryHandler;
use Example\Seller\Application\Query\ShowSeller\ShowSellerQuery;
use Example\Seller\Application\Query\ShowSeller\ShowSellerQueryHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

readonly class ApiController
{
    public function __construct(
        // I'd not normally inject a container here, but slim doesn't seem to (easily) support injecting specific classes into controller methods
        private ContainerInterface $container,
        private LoggerInterface $logger,
    ) {
    }

    public function show(RequestInterface $request, ResponseInterface $response, string $seller_id): ResponseInterface
    {
        /** @var ShowSellerQueryHandler $handler */
        $handler = $this->container->get(ShowSellerQueryHandler::class);

        try {
            $dto = $handler->handle(new ShowSellerQuery($seller_id));
        } catch (\Exception $exception) {
            // report($e) // if we were using Laravel instead of some thrown together non-functional implementation
            // actual serious exceptions would go to something like Sentry, Datadog or New Relic
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            return $response->withStatus(500);
        }

        if ($dto) {
            $response->getBody()->write(json_encode($dto));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }

        return $response->withStatus(404);
    }

    public function list(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /** @var ListSellersQueryHandler $handler */
        $handler = $this->container->get(ListSellersQueryHandler::class);

        try {
            $dto = $handler->handle(new ListSellersQuery());
        } catch (\Exception $exception) {
            // report($e) // if we were using Laravel instead of some thrown together non-functional implementation
            // actual serious exceptions would go to something like Sentry, Datadog or New Relic
            $this->logger->error($$exception->getMessage(), ['exception' => $exception]);

            return $response->withStatus(500);
        }

        $response->getBody()->write(json_encode($dto));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function create(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $jsonBody = $request->getParsedBody();

        // we could do basic input validation here. front end has likely already done some, but it can't be trusted!
        // where to actually do this is a difficult question. If we just pass input into the command object,
        // any type errors will result in an exception and we can't gracefully inform the user what's wrong
        // using whatever validation library that the framework that's handling routing/controllers offers is probably easiest

        // let's pretend there's some actual validation occurring here...
        if (! isset($jsonBody['name'])) {
            $response->getBody()->write(json_encode(['error' => 'name must be a string.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (! is_string($jsonBody['name'])) {
            $response->getBody()->write(json_encode(['error' => 'name must be a string.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        /** @var CreateSellerCommandHandler $handler */
        $handler = $this->container->get(CreateSellerCommandHandler::class);

        try {
            $sellerId = $handler->handle(new CreateSellerCommand(
                name: $jsonBody['name'],
                description: $jsonBody['description'],
                isActive: $jsonBody['is_active'],
                soldProductTypes: $jsonBody['sold_product_types'],
                payoutAmount: $jsonBody['payout_amount'],
                payoutCurrency: $jsonBody['payout_currency'],
            ));

            $response = $response->withStatus(201);
            $response->getBody()->write(
                (string) json_encode(['seller_id' => $sellerId]));
        } catch (\DomainException $domainException) {
            $response = $response->withStatus(400);
            $response->getBody()->write($domainException->getMessage());
        } catch (\Exception $exception) {
            // Don't directly output a regular exception as it'll have sensitive information
            // report($e) // if we were using Laravel instead of some thrown together non-functional implementation
            // actual serious exceptions would go to something like Sentry, Datadog or New Relic
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            return $response->withStatus(500);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
