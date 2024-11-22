<?php

declare(strict_types=1);

namespace Tests\Seller\Infrastructure;

use Example\Seller\Application\Command\CreateSeller\CreateSellerCommand;
use Example\Seller\Application\Command\CreateSeller\CreateSellerCommandHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Tests\Traits\UsesContainer;

class ApiControllerTest extends TestCase
{
    use UsesContainer;

    /** @test  */
    public function test_create_seller_returns_seller_id_on_200(): void
    {
        $request = $this->createRequest(method: 'POST', uri: '/api/sellers', data: [
            'name' => 'test name',
            'description' => 'test',
            'is_active' => true,
            'sold_product_types' => 'digital',
            'payout_amount' => 20000,
            'payout_currency' => 'GBP',
        ]);

        $response = $this->app->handle($request);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type')[0]);
        $body = (string) $response->getBody();
        $this->assertJson($body);
        $body = json_decode($body, true);
        $this->assertArrayHasKey('seller_id', $body);
        $this->assertIsString($body['seller_id']);
    }

    public function test_create_seller_form_validation_fails_if_name_is_not_set_and_string(): void
    {
        $request = $this->createRequest(method: 'POST', uri: '/api/sellers', data: [
//            'name' => 'test name',
            'description' => 'test',
            'is_active' => true,
            'sold_product_types' => 'digital',
            'payout_amount' => 20000,
            'payout_currency' => 'GBP',
        ]);

        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'name must be a string.']), $body);
    }

    /** @test */
    public function test_create_seller_returns_400_error_on_domain_exception(): void
    {
        $request = $this->createRequest(method: 'POST', uri: 'api/sellers', data: [
            'name' => 'test name',
            'description' => 'test',
            'is_active' => true,
            'sold_product_types' => 'digital',
            'payout_amount' => 20000,
            'payout_currency' => 'USD', // invalid currency
        ]);

        $response = $this->app->handle($request);
        $body = (string) $response->getBody();
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Currency not supported', $body);
    }

    /** @test */
    public function test_show_seller_query_returns_seller_json_on_200(): void
    {
        /** @var CreateSellerCommandHandler $handler */
        $handler = $this->app->getContainer()->get(CreateSellerCommandHandler::class);
        $seller_id = $handler->handle(new CreateSellerCommand(
            name: 'test name',
            description: 'test description',
            isActive: true,
            soldProductTypes: 'digital',
            payoutAmount: 500000, // 5000.00
            payoutCurrency: 'GBP',
        ));

        $request = $this->createRequest(method: 'GET', uri: 'api/sellers/' . $seller_id);
        $response = $this->app->handle($request);
        $this->assertequals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertJson($body);

        $body = json_decode($body, true);
        $this->assertArrayHasKey('id', $body);
        $this->assertEquals($seller_id, $body['id']);
        $this->assertArrayHasKey('name', $body);
        $this->assertEquals('test name', $body['name']);
        $this->assertArrayHasKey('description', $body);
        $this->assertEquals('test description', $body['description']);
        $this->assertArrayHasKey('is_active', $body);
        $this->assertTrue($body['is_active']);
        $this->assertArrayHasKey('sold_product_type', $body);
        $this->assertEquals('digital', $body['sold_product_type']);
        $this->assertArrayHasKey('payout_amount', $body);
        $this->assertEquals(500000, $body['payout_amount']);
        $this->assertArrayHasKey('payout_currency', $body);
        $this->assertEquals('GBP', $body['payout_currency']);
    }

    public function test_show_seller_returns_404_error_on_not_found(): void
    {
        $request = $this->createRequest(method: 'GET', uri: 'api/sellers/ggdi-g892h-invalid-id');
        $response = $this->app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_list_sellers_returns_sellers_json_on_200(): void
    {
        /** @var CreateSellerCommandHandler $handler */
        $handler = $this->app->getContainer()->get(CreateSellerCommandHandler::class);
        $sellerIds = [];
        for ($i = 0; $i < 5; ++$i) {
            $sellerIds[] = $handler->handle(new CreateSellerCommand(
                name: 'test name',
                description: 'test description',
                isActive: true,
                soldProductTypes: 'digital',
                payoutAmount: 500000, // 5000.00
                payoutCurrency: 'GBP',
            ));
        }

        $request = $this->createRequest(method: 'GET', uri: 'api/sellers');
        $response = $this->app->handle($request);
        $this->assertequals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertJson($body);
        $body = json_decode($body, true);
        $this->assertCount(5, $body);
        $this->assertEquals($sellerIds[0], $body[0]['id']);
        $this->assertEquals($sellerIds[1], $body[1]['id']);
        $this->assertEquals($sellerIds[2], $body[2]['id']);
        $this->assertEquals($sellerIds[3], $body[3]['id']);
        $this->assertEquals($sellerIds[4], $body[4]['id']);
    }

    /** @test */
    public function test_list_sellers_returns_empty_json_if_no_sellers_found(): void
    {
        $request = $this->createRequest(method: 'GET', uri: 'api/sellers');
        $response = $this->app->handle($request);
        $this->assertequals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertJson($body);
        $body = json_decode($body, true);
        $this->assertEmpty($body);
    }

    private function createRequest(string $method, string $uri, array $data = null): \Psr\Http\Message\ServerRequestInterface
    {
        /** @var ServerRequestFactoryInterface $requestFactory */
        $requestFactory = $this->app->getContainer()->get(ServerRequestFactoryInterface::class);
        $request = $requestFactory->createServerRequest($method, $uri);
        if ($data !== null) {
            $request = $request->withParsedBody($data)->withHeader('Content-Type', 'application/json');
        }

        return $request;
    }
}
