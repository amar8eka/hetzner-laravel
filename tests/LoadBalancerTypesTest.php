<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\LoadBalancerTypes\ListResponse;
use Boci\HetznerLaravel\Responses\LoadBalancerTypes\LoadBalancerType;
use Boci\HetznerLaravel\Responses\LoadBalancerTypes\RetrieveResponse;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Load Balancer Types Test Suite
 *
 * This test suite covers all functionality related to the Load Balancer Types resource,
 * including listing and retrieving load balancer types.
 */
final class LoadBalancerTypesTest extends TestCase
{
    /**
     * Test listing load balancer types with fake data
     */
    public function test_can_list_load_balancer_types_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->loadBalancerTypes()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertIsArray($response->loadBalancerTypes());
        $this->assertCount(2, $response->loadBalancerTypes());

        $loadBalancerTypes = $response->loadBalancerTypes();
        $this->assertInstanceOf(LoadBalancerType::class, $loadBalancerTypes[0]);
        $this->assertEquals(1, $loadBalancerTypes[0]->id());
        $this->assertEquals('lb11', $loadBalancerTypes[0]->name());
        $this->assertEquals('Load Balancer 11', $loadBalancerTypes[0]->description());
        $this->assertEquals(1000, $loadBalancerTypes[0]->maxConnections());
        $this->assertEquals(10, $loadBalancerTypes[0]->maxServices());
        $this->assertEquals(10, $loadBalancerTypes[0]->maxTargets());
        $this->assertEquals(10, $loadBalancerTypes[0]->maxAssignedCertificates());

        $this->assertInstanceOf(LoadBalancerType::class, $loadBalancerTypes[1]);
        $this->assertEquals(2, $loadBalancerTypes[1]->id());
        $this->assertEquals('lb21', $loadBalancerTypes[1]->name());
        $this->assertEquals('Load Balancer 21', $loadBalancerTypes[1]->description());
        $this->assertEquals(2000, $loadBalancerTypes[1]->maxConnections());
        $this->assertEquals(20, $loadBalancerTypes[1]->maxServices());
        $this->assertEquals(20, $loadBalancerTypes[1]->maxTargets());
        $this->assertEquals(20, $loadBalancerTypes[1]->maxAssignedCertificates());

        $pagination = $response->pagination();
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(25, $pagination['per_page']);
        $this->assertEquals(2, $pagination['total']);
        $this->assertEquals(1, $pagination['last_page']);
        $this->assertFalse($pagination['has_more_pages']);

        $this->assertRequestWasMade($requests, 'load_balancer_types', 'list');
    }

    /**
     * Test listing load balancer types with custom response
     */
    public function test_can_list_load_balancer_types_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'load_balancer_types' => [
                    [
                        'id' => 42,
                        'name' => 'lb42',
                        'description' => 'Load Balancer 42',
                        'max_connections' => 5000,
                        'max_services' => 50,
                        'max_targets' => 50,
                        'max_assigned_certificates' => 50,
                        'prices' => [
                            [
                                'location' => 'nbg1',
                                'price_hourly' => [
                                    'net' => '5.0000000000',
                                    'gross' => '5.9500000000',
                                ],
                                'price_monthly' => [
                                    'net' => '5.0000000000',
                                    'gross' => '5.9500000000',
                                ],
                            ],
                        ],
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 1,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->loadBalancerTypes()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->loadBalancerTypes());

        $loadBalancerType = $response->loadBalancerTypes()[0];
        $this->assertEquals(42, $loadBalancerType->id());
        $this->assertEquals('lb42', $loadBalancerType->name());
        $this->assertEquals('Load Balancer 42', $loadBalancerType->description());
        $this->assertEquals(5000, $loadBalancerType->maxConnections());
        $this->assertEquals(50, $loadBalancerType->maxServices());
        $this->assertEquals(50, $loadBalancerType->maxTargets());
        $this->assertEquals(50, $loadBalancerType->maxAssignedCertificates());

        $prices = $loadBalancerType->prices();
        $this->assertIsArray($prices);
        $this->assertCount(1, $prices);
        /** @var array<int, array<string, mixed>> $prices */
        $this->assertEquals('nbg1', $prices[0]['location']);
        $this->assertEquals('5.0000000000', $prices[0]['price_hourly']['net']);
        $this->assertEquals('5.9500000000', $prices[0]['price_hourly']['gross']);

        $this->assertRequestWasMade($requests, 'load_balancer_types', 'list');
    }

    /**
     * Test listing load balancer types with query parameters
     */
    public function test_can_list_load_balancer_types_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'lb11',
        ];

        $response = $client->loadBalancerTypes()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);

        $this->assertRequestWasMade($requests, 'load_balancer_types', 'list');
    }

    /**
     * Test getting a specific load balancer type
     */
    public function test_can_get_specific_load_balancer_type(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerTypeId = '42';
        $response = $client->loadBalancerTypes()->retrieve($loadBalancerTypeId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $this->assertInstanceOf(LoadBalancerType::class, $response->loadBalancerType());

        $loadBalancerType = $response->loadBalancerType();
        $this->assertEquals(42, $loadBalancerType->id());
        $this->assertEquals('lb11', $loadBalancerType->name());
        $this->assertEquals('Load Balancer 11', $loadBalancerType->description());
        $this->assertEquals(1000, $loadBalancerType->maxConnections());
        $this->assertEquals(10, $loadBalancerType->maxServices());
        $this->assertEquals(10, $loadBalancerType->maxTargets());
        $this->assertEquals(10, $loadBalancerType->maxAssignedCertificates());

        $this->assertRequestWasMade($requests, 'load_balancer_types', 'get');
    }

    /**
     * Test getting load balancer type with custom response
     */
    public function test_can_get_load_balancer_type_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'load_balancer_type' => [
                    'id' => 99,
                    'name' => 'lb99',
                    'description' => 'Load Balancer 99',
                    'max_connections' => 10000,
                    'max_services' => 100,
                    'max_targets' => 100,
                    'max_assigned_certificates' => 100,
                    'prices' => [
                        [
                            'location' => 'nbg1',
                            'price_hourly' => [
                                'net' => '10.0000000000',
                                'gross' => '11.9000000000',
                            ],
                            'price_monthly' => [
                                'net' => '10.0000000000',
                                'gross' => '11.9000000000',
                            ],
                        ],
                        [
                            'location' => 'fsn1',
                            'price_hourly' => [
                                'net' => '10.0000000000',
                                'gross' => '11.9000000000',
                            ],
                            'price_monthly' => [
                                'net' => '10.0000000000',
                                'gross' => '11.9000000000',
                            ],
                        ],
                        [
                            'location' => 'hel1',
                            'price_hourly' => [
                                'net' => '10.0000000000',
                                'gross' => '11.9000000000',
                            ],
                            'price_monthly' => [
                                'net' => '10.0000000000',
                                'gross' => '11.9000000000',
                            ],
                        ],
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerTypeId = '99';
        $response = $client->loadBalancerTypes()->retrieve($loadBalancerTypeId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $loadBalancerType = $response->loadBalancerType();

        $this->assertEquals(99, $loadBalancerType->id());
        $this->assertEquals('lb99', $loadBalancerType->name());
        $this->assertEquals('Load Balancer 99', $loadBalancerType->description());
        $this->assertEquals(10000, $loadBalancerType->maxConnections());
        $this->assertEquals(100, $loadBalancerType->maxServices());
        $this->assertEquals(100, $loadBalancerType->maxTargets());
        $this->assertEquals(100, $loadBalancerType->maxAssignedCertificates());

        $prices = $loadBalancerType->prices();
        $this->assertIsArray($prices);
        $this->assertCount(3, $prices);
        /** @var array<int, array<string, mixed>> $prices */
        $this->assertEquals('nbg1', $prices[0]['location']);
        $this->assertEquals('fsn1', $prices[1]['location']);
        $this->assertEquals('hel1', $prices[2]['location']);

        $this->assertRequestWasMade($requests, 'load_balancer_types', 'get');
    }

    /**
     * Test load balancer type response structure
     */
    public function test_load_balancer_type_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->loadBalancerTypes()->list();
        $loadBalancerType = $response->loadBalancerTypes()[0];

        // Test all load balancer type properties
        $this->assertIsInt($loadBalancerType->id());
        $this->assertIsString($loadBalancerType->name());
        $this->assertIsString($loadBalancerType->description());
        $this->assertIsInt($loadBalancerType->maxConnections());
        $this->assertIsInt($loadBalancerType->maxServices());
        $this->assertIsInt($loadBalancerType->maxTargets());
        $this->assertIsInt($loadBalancerType->maxAssignedCertificates());
        $this->assertIsArray($loadBalancerType->prices());

        // Test load balancer type names
        $this->assertContains($loadBalancerType->name(), ['lb11', 'lb21']);

        // Test toArray method
        $loadBalancerTypeArray = $loadBalancerType->toArray();
        $this->assertIsArray($loadBalancerTypeArray);
        $this->assertArrayHasKey('id', $loadBalancerTypeArray);
        $this->assertArrayHasKey('name', $loadBalancerTypeArray);
        $this->assertArrayHasKey('description', $loadBalancerTypeArray);
        $this->assertArrayHasKey('max_connections', $loadBalancerTypeArray);
        $this->assertArrayHasKey('max_services', $loadBalancerTypeArray);
        $this->assertArrayHasKey('max_targets', $loadBalancerTypeArray);
        $this->assertArrayHasKey('max_assigned_certificates', $loadBalancerTypeArray);
        $this->assertArrayHasKey('prices', $loadBalancerTypeArray);
    }

    /**
     * Test handling load balancer type API exception
     */
    public function test_can_handle_load_balancer_type_api_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Load balancer type not found', new Request('GET', '/load_balancer_types/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Load balancer type not found');

        $client->loadBalancerTypes()->retrieve('999');
    }

    /**
     * Test handling load balancer type list exception
     */
    public function test_can_handle_load_balancer_type_list_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Rate limit exceeded', new Request('GET', '/load_balancer_types')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->loadBalancerTypes()->list();
    }

    /**
     * Test handling mixed load balancer type response types
     */
    public function test_can_handle_mixed_load_balancer_type_response_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'load_balancer_types' => [
                    [
                        'id' => 1,
                        'name' => 'lb11',
                        'description' => 'Load Balancer 11',
                        'max_connections' => 1000,
                        'max_services' => 10,
                        'max_targets' => 10,
                        'max_assigned_certificates' => 10,
                        'prices' => [
                            [
                                'location' => 'nbg1',
                                'price_hourly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000',
                                ],
                                'price_monthly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000',
                                ],
                            ],
                        ],
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 1,
                    ],
                ],
            ]) ?: ''),
            new RequestException('Load balancer type not found', new Request('GET', '/load_balancer_types/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $listResponse = $client->loadBalancerTypes()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->loadBalancerTypes());

        // Second call should throw exception
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Load balancer type not found');
        $client->loadBalancerTypes()->retrieve('999');
    }

    /**
     * Test using individual load balancer type fake
     */
    public function test_using_individual_load_balancer_type_fake(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerTypesFake = $client->loadBalancerTypes();

        $response = $loadBalancerTypesFake->list(['page' => 1, 'per_page' => 5]);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(2, $response->loadBalancerTypes());

        $loadBalancerTypesFake->assertSent(function (array $request) {
            return $request['resource'] === 'load_balancer_types' &&
                   $request['method'] === 'list' &&
                   isset($request['parameters']['page']) &&
                   $request['parameters']['page'] === 1 &&
                   isset($request['parameters']['per_page']) &&
                   $request['parameters']['per_page'] === 5;
        });
    }

    /**
     * Test load balancer type fake assert not sent
     */
    public function test_load_balancer_type_fake_assert_not_sent(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerTypesFake = $client->loadBalancerTypes();

        // No requests made yet
        $loadBalancerTypesFake->assertNotSent();

        // Make a request
        $loadBalancerTypesFake->list();

        // Now it should fail
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Requests were sent to load_balancer_types.');
        $loadBalancerTypesFake->assertNotSent();
    }

    /**
     * Test load balancer type pagination structure
     */
    public function test_load_balancer_type_pagination_structure(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'load_balancer_types' => [
                    [
                        'id' => 1,
                        'name' => 'lb11',
                        'description' => 'Load Balancer 11',
                        'max_connections' => 1000,
                        'max_services' => 10,
                        'max_targets' => 10,
                        'max_assigned_certificates' => 10,
                        'prices' => [
                            [
                                'location' => 'nbg1',
                                'price_hourly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000',
                                ],
                                'price_monthly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000',
                                ],
                            ],
                        ],
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'page' => 2,
                        'per_page' => 10,
                        'previous_page' => 1,
                        'next_page' => 3,
                        'last_page' => 5,
                        'total_entries' => 50,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->loadBalancerTypes()->list();
        $pagination = $response->pagination();

        $this->assertEquals(2, $pagination['current_page']);
        $this->assertEquals(10, $pagination['per_page']);
        $this->assertEquals(50, $pagination['total']);
        $this->assertEquals(5, $pagination['last_page']);
        $this->assertEquals(11, $pagination['from']); // (2-1) * 10 + 1
        $this->assertEquals(20, $pagination['to']); // min(2 * 10, 50)
        $this->assertTrue($pagination['has_more_pages']);

        $this->assertArrayHasKey('links', $pagination);
        $this->assertArrayHasKey('first', $pagination['links']);
        $this->assertArrayHasKey('last', $pagination['links']);
        $this->assertArrayHasKey('prev', $pagination['links']);
        $this->assertArrayHasKey('next', $pagination['links']);
    }

    /**
     * Test load balancer type pricing structure
     */
    public function test_load_balancer_type_pricing_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->loadBalancerTypes()->list();
        $loadBalancerType = $response->loadBalancerTypes()[0];

        $prices = $loadBalancerType->prices();
        $this->assertIsArray($prices);
        $this->assertCount(2, $prices);

        // Test first price entry
        /** @var array<int, array<string, mixed>> $prices */
        $firstPrice = $prices[0];
        $this->assertArrayHasKey('location', $firstPrice);
        $this->assertArrayHasKey('price_hourly', $firstPrice);
        $this->assertArrayHasKey('price_monthly', $firstPrice);
        $this->assertEquals('nbg1', $firstPrice['location']);

        $hourlyPrice = $firstPrice['price_hourly'];
        $this->assertArrayHasKey('net', $hourlyPrice);
        $this->assertArrayHasKey('gross', $hourlyPrice);
        $this->assertEquals('1.0000000000', $hourlyPrice['net']);
        $this->assertEquals('1.1900000000', $hourlyPrice['gross']);

        $monthlyPrice = $firstPrice['price_monthly'];
        $this->assertArrayHasKey('net', $monthlyPrice);
        $this->assertArrayHasKey('gross', $monthlyPrice);
        $this->assertEquals('1.0000000000', $monthlyPrice['net']);
        $this->assertEquals('1.1900000000', $monthlyPrice['gross']);

        // Test second price entry
        $secondPrice = $prices[1];
        $this->assertEquals('fsn1', $secondPrice['location']);
    }

    /**
     * Test load balancer type workflow simulation
     */
    public function test_load_balancer_type_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List load balancer types response
            new Response(200, [], json_encode([
                'load_balancer_types' => [
                    [
                        'id' => 1,
                        'name' => 'lb11',
                        'description' => 'Load Balancer 11',
                        'max_connections' => 1000,
                        'max_services' => 10,
                        'max_targets' => 10,
                        'max_assigned_certificates' => 10,
                        'prices' => [
                            [
                                'location' => 'nbg1',
                                'price_hourly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000',
                                ],
                                'price_monthly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000',
                                ],
                            ],
                        ],
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 1,
                    ],
                ],
            ]) ?: ''),
            // Get specific load balancer type response
            new Response(200, [], json_encode([
                'load_balancer_type' => [
                    'id' => 1,
                    'name' => 'lb11',
                    'description' => 'Load Balancer 11',
                    'max_connections' => 1000,
                    'max_services' => 10,
                    'max_targets' => 10,
                    'max_assigned_certificates' => 10,
                    'prices' => [
                        [
                            'location' => 'nbg1',
                            'price_hourly' => [
                                'net' => '1.0000000000',
                                'gross' => '1.1900000000',
                            ],
                            'price_monthly' => [
                                'net' => '1.0000000000',
                                'gross' => '1.1900000000',
                            ],
                        ],
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list load balancer types, then get details of the first one
        $listResponse = $client->loadBalancerTypes()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->loadBalancerTypes());

        $firstLoadBalancerType = $listResponse->loadBalancerTypes()[0];
        $loadBalancerTypeId = (string) $firstLoadBalancerType->id();

        $getResponse = $client->loadBalancerTypes()->retrieve($loadBalancerTypeId);
        $this->assertInstanceOf(RetrieveResponse::class, $getResponse);
        $this->assertEquals($firstLoadBalancerType->id(), $getResponse->loadBalancerType()->id());
        $this->assertEquals($firstLoadBalancerType->name(), $getResponse->loadBalancerType()->name());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'load_balancer_types', 'list');
        $this->assertRequestWasMade($requests, 'load_balancer_types', 'get');
    }

    /**
     * Test load balancer type error response handling
     */
    public function test_load_balancer_type_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Load balancer type not found', new Request('GET', '/load_balancer_types/nonexistent')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Load balancer type not found');
        $client->loadBalancerTypes()->retrieve('nonexistent');
    }

    /**
     * Test load balancer type empty list response
     */
    public function test_load_balancer_type_empty_list_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'load_balancer_types' => [],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 0,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->loadBalancerTypes()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(0, $response->loadBalancerTypes());
        $this->assertEmpty($response->loadBalancerTypes());

        $pagination = $response->pagination();
        $this->assertEquals(0, $pagination['total']);
        $this->assertEquals(0, $pagination['to']);
    }

    /**
     * Test load balancer type specifications validation
     */
    public function test_load_balancer_type_specifications_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->loadBalancerTypes()->list();
        $loadBalancerTypes = $response->loadBalancerTypes();

        foreach ($loadBalancerTypes as $loadBalancerType) {
            // Test that all specifications are positive integers
            $this->assertGreaterThan(0, $loadBalancerType->maxConnections());
            $this->assertGreaterThan(0, $loadBalancerType->maxServices());
            $this->assertGreaterThan(0, $loadBalancerType->maxTargets());
            $this->assertGreaterThan(0, $loadBalancerType->maxAssignedCertificates());

            // Test that names follow expected pattern
            $this->assertMatchesRegularExpression('/^lb\d+$/', $loadBalancerType->name());

            // Test that descriptions are not empty
            $this->assertNotEmpty($loadBalancerType->description());
            $this->assertStringContainsString('Load Balancer', $loadBalancerType->description());
        }
    }

    /**
     * Test load balancer type price validation
     */
    public function test_load_balancer_type_price_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->loadBalancerTypes()->list();
        $loadBalancerType = $response->loadBalancerTypes()[0];

        $prices = $loadBalancerType->prices();
        $this->assertIsArray($prices);
        $this->assertNotEmpty($prices);

        foreach ($prices as $price) {
            // Test price structure
            $this->assertArrayHasKey('location', $price);
            $this->assertArrayHasKey('price_hourly', $price);
            $this->assertArrayHasKey('price_monthly', $price);

            // Test location format
            $this->assertMatchesRegularExpression('/^[a-z]{3}\d+$/', $price['location']);

            // Test hourly price structure
            $hourlyPrice = $price['price_hourly'];
            $this->assertArrayHasKey('net', $hourlyPrice);
            $this->assertArrayHasKey('gross', $hourlyPrice);
            $this->assertIsNumeric($hourlyPrice['net']);
            $this->assertIsNumeric($hourlyPrice['gross']);
            $this->assertGreaterThan(0, (float) $hourlyPrice['net']);
            $this->assertGreaterThan(0, (float) $hourlyPrice['gross']);

            // Test monthly price structure
            $monthlyPrice = $price['price_monthly'];
            $this->assertArrayHasKey('net', $monthlyPrice);
            $this->assertArrayHasKey('gross', $monthlyPrice);
            $this->assertIsNumeric($monthlyPrice['net']);
            $this->assertIsNumeric($monthlyPrice['gross']);
            $this->assertGreaterThan(0, (float) $monthlyPrice['net']);
            $this->assertGreaterThan(0, (float) $monthlyPrice['gross']);
        }
    }
}
