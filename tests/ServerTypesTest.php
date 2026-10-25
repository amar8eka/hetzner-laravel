<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\ServerTypes\ListResponse;
use Boci\HetznerLaravel\Responses\ServerTypes\RetrieveResponse;
use Boci\HetznerLaravel\Responses\ServerTypes\ServerType;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Server Types Test Suite
 *
 * This test suite covers all functionality related to the Server Types resource,
 * including listing and getting server types.
 */
final class ServerTypesTest extends TestCase
{
    /**
     * Test listing server types with fake data
     */
    public function test_can_list_server_types_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->serverTypes()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertIsArray($response->serverTypes());
        $this->assertCount(3, $response->serverTypes());

        $serverTypes = $response->serverTypes();
        $this->assertInstanceOf(ServerType::class, $serverTypes[0]);
        $this->assertEquals(1, $serverTypes[0]->id());
        $this->assertEquals('cx11', $serverTypes[0]->name());
        $this->assertEquals('CX11', $serverTypes[0]->description());
        $this->assertEquals(1, $serverTypes[0]->cores());
        $this->assertEquals(4.0, $serverTypes[0]->memory());
        $this->assertEquals(20, $serverTypes[0]->disk());
        $this->assertIsArray($serverTypes[0]->prices());
        $this->assertEquals('local', $serverTypes[0]->storageType());
        $this->assertEquals('shared', $serverTypes[0]->cpuType());

        $this->assertInstanceOf(ServerType::class, $serverTypes[1]);
        $this->assertEquals(2, $serverTypes[1]->id());
        $this->assertEquals('cx21', $serverTypes[1]->name());
        $this->assertEquals('CX21', $serverTypes[1]->description());
        $this->assertEquals(2, $serverTypes[1]->cores());
        $this->assertEquals(8.0, $serverTypes[1]->memory());
        $this->assertEquals(40, $serverTypes[1]->disk());

        $this->assertInstanceOf(ServerType::class, $serverTypes[2]);
        $this->assertEquals(3, $serverTypes[2]->id());
        $this->assertEquals('cx31', $serverTypes[2]->name());
        $this->assertEquals('CX31', $serverTypes[2]->description());
        $this->assertEquals(2, $serverTypes[2]->cores());
        $this->assertEquals(8.0, $serverTypes[2]->memory());
        $this->assertEquals(80, $serverTypes[2]->disk());

        $this->assertRequestWasMade($requests, 'server_types', 'list');
    }

    /**
     * Test listing server types with custom response
     */
    public function test_can_list_server_types_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'server_types' => [
                    [
                        'id' => 42,
                        'name' => 'cx41',
                        'description' => 'CX41',
                        'cores' => 4,
                        'memory' => 16.0,
                        'disk' => 160,
                        'prices' => [
                            [
                                'location' => 'fsn1',
                                'price_hourly' => [
                                    'net' => '4.0000000000',
                                    'gross' => '4.7600000000000000',
                                ],
                                'price_monthly' => [
                                    'net' => '4.0000000000',
                                    'gross' => '4.7600000000000000',
                                ],
                            ],
                        ],
                        'storage_type' => 'local',
                        'cpu_type' => 'shared',
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

        $response = $client->serverTypes()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->serverTypes());

        $serverType = $response->serverTypes()[0];
        $this->assertEquals(42, $serverType->id());
        $this->assertEquals('cx41', $serverType->name());
        $this->assertEquals('CX41', $serverType->description());
        $this->assertEquals(4, $serverType->cores());
        $this->assertEquals(16.0, $serverType->memory());
        $this->assertEquals(160, $serverType->disk());
        $this->assertEquals('local', $serverType->storageType());
        $this->assertEquals('shared', $serverType->cpuType());

        $this->assertRequestWasMade($requests, 'server_types', 'list');
    }

    /**
     * Test listing server types with query parameters
     */
    public function test_can_list_server_types_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'cx11',
        ];

        $response = $client->serverTypes()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);

        $this->assertRequestWasMade($requests, 'server_types', 'list');
    }

    /**
     * Test getting a specific server type
     */
    public function test_can_get_specific_server_type(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $serverTypeId = '42';
        $response = $client->serverTypes()->retrieve($serverTypeId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $this->assertInstanceOf(ServerType::class, $response->serverType());

        $serverType = $response->serverType();
        $this->assertEquals(42, $serverType->id());
        $this->assertEquals('cx11', $serverType->name());
        $this->assertEquals('CX11', $serverType->description());
        $this->assertEquals(1, $serverType->cores());
        $this->assertEquals(4.0, $serverType->memory());
        $this->assertEquals(20, $serverType->disk());
        $this->assertIsArray($serverType->prices());
        $this->assertEquals('local', $serverType->storageType());
        $this->assertEquals('shared', $serverType->cpuType());

        $this->assertRequestWasMade($requests, 'server_types', 'retrieve');
    }

    /**
     * Test getting server type with custom response
     */
    public function test_can_get_server_type_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'server_type' => [
                    'id' => 123,
                    'name' => 'cx51',
                    'description' => 'CX51',
                    'cores' => 8,
                    'memory' => 32.0,
                    'disk' => 320,
                    'prices' => [
                        [
                            'location' => 'fsn1',
                            'price_hourly' => [
                                'net' => '8.0000000000',
                                'gross' => '9.5200000000000000',
                            ],
                            'price_monthly' => [
                                'net' => '8.0000000000',
                                'gross' => '9.5200000000000000',
                            ],
                        ],
                        [
                            'location' => 'nbg1',
                            'price_hourly' => [
                                'net' => '8.0000000000',
                                'gross' => '9.5200000000000000',
                            ],
                            'price_monthly' => [
                                'net' => '8.0000000000',
                                'gross' => '9.5200000000000000',
                            ],
                        ],
                    ],
                    'storage_type' => 'local',
                    'cpu_type' => 'shared',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $serverTypeId = '123';
        $response = $client->serverTypes()->retrieve($serverTypeId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $serverType = $response->serverType();

        $this->assertEquals(123, $serverType->id());
        $this->assertEquals('cx51', $serverType->name());
        $this->assertEquals('CX51', $serverType->description());
        $this->assertEquals(8, $serverType->cores());
        $this->assertEquals(32.0, $serverType->memory());
        $this->assertEquals(320, $serverType->disk());
        $this->assertCount(2, $serverType->prices());
        $this->assertEquals('local', $serverType->storageType());
        $this->assertEquals('shared', $serverType->cpuType());

        $this->assertRequestWasMade($requests, 'server_types', 'retrieve');
    }

    /**
     * Test server type response structure
     */
    public function test_server_type_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->serverTypes()->list();
        $serverTypes = $response->serverTypes();

        foreach ($serverTypes as $serverType) {
            // Test all server type properties
            $this->assertIsInt($serverType->id());
            $this->assertIsString($serverType->name());
            $this->assertIsString($serverType->description());
            $this->assertIsInt($serverType->cores());
            $this->assertIsFloat($serverType->memory());
            $this->assertIsInt($serverType->disk());
            $this->assertIsArray($serverType->prices());
            $this->assertIsString($serverType->storageType());
            $this->assertIsString($serverType->cpuType());

            // Test server type names
            $this->assertMatchesRegularExpression('/^cx\d+$/', $serverType->name());

            // Test cores and memory values
            $this->assertGreaterThan(0, $serverType->cores());
            $this->assertGreaterThan(0, $serverType->memory());
            $this->assertGreaterThan(0, $serverType->disk());

            // Test storage and CPU types
            $this->assertContains($serverType->storageType(), ['local', 'network']);
            $this->assertContains($serverType->cpuType(), ['shared', 'dedicated']);

            // Test prices structure
            foreach ($serverType->prices() as $price) {
                $this->assertIsString($price['location']);
                $this->assertIsArray($price['price_hourly']);
                $this->assertIsArray($price['price_monthly']);
                $this->assertArrayHasKey('net', $price['price_hourly']);
                $this->assertArrayHasKey('gross', $price['price_hourly']);
                $this->assertArrayHasKey('net', $price['price_monthly']);
                $this->assertArrayHasKey('gross', $price['price_monthly']);
            }
        }
    }

    /**
     * Test handling server type API exception
     */
    public function test_can_handle_server_type_api_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Server type not found', new Request('GET', '/server_types/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Server type not found');

        $client->serverTypes()->retrieve('999');
    }

    /**
     * Test handling server type list exception
     */
    public function test_can_handle_server_type_list_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Rate limit exceeded', new Request('GET', '/server_types')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->serverTypes()->list();
    }

    /**
     * Test handling mixed server type response types
     */
    public function test_can_handle_mixed_server_type_response_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'server_types' => [
                    [
                        'id' => 1,
                        'name' => 'cx11',
                        'description' => 'CX11',
                        'cores' => 1,
                        'memory' => 4.0,
                        'disk' => 20,
                        'prices' => [
                            [
                                'location' => 'fsn1',
                                'price_hourly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000000000',
                                ],
                                'price_monthly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000000000',
                                ],
                            ],
                        ],
                        'storage_type' => 'local',
                        'cpu_type' => 'shared',
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
            new RequestException('Server type not found', new Request('GET', '/server_types/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $listResponse = $client->serverTypes()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->serverTypes());

        // Second call should throw exception
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Server type not found');
        $client->serverTypes()->retrieve('999');
    }

    /**
     * Test using individual server type fake
     */
    public function test_using_individual_server_type_fake(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $serverTypesFake = $client->serverTypes();

        $response = $serverTypesFake->list(['page' => 1, 'per_page' => 5]);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(3, $response->serverTypes());

        $serverTypesFake->assertSent(function (array $request) {
            return $request['resource'] === 'server_types' &&
                   $request['method'] === 'list' &&
                   isset($request['parameters']['page']) &&
                   $request['parameters']['page'] === 1 &&
                   isset($request['parameters']['per_page']) &&
                   $request['parameters']['per_page'] === 5;
        });
    }

    /**
     * Test server type fake assert not sent
     */
    public function test_server_type_fake_assert_not_sent(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $serverTypesFake = $client->serverTypes();

        // No requests made yet
        $serverTypesFake->assertNotSent();

        // Make a request
        $serverTypesFake->list();

        // Now it should fail
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Requests were sent to server_types.');
        $serverTypesFake->assertNotSent();
    }

    /**
     * Test server type workflow simulation
     */
    public function test_server_type_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List server types response
            new Response(200, [], json_encode([
                'server_types' => [
                    [
                        'id' => 1,
                        'name' => 'cx11',
                        'description' => 'CX11',
                        'cores' => 1,
                        'memory' => 4.0,
                        'disk' => 20,
                        'prices' => [
                            [
                                'location' => 'fsn1',
                                'price_hourly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000000000',
                                ],
                                'price_monthly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000000000',
                                ],
                            ],
                        ],
                        'storage_type' => 'local',
                        'cpu_type' => 'shared',
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
            // Get specific server type response
            new Response(200, [], json_encode([
                'server_type' => [
                    'id' => 1,
                    'name' => 'cx11',
                    'description' => 'CX11',
                    'cores' => 1,
                    'memory' => 4.0,
                    'disk' => 20,
                    'prices' => [
                        [
                            'location' => 'fsn1',
                            'price_hourly' => [
                                'net' => '1.0000000000',
                                'gross' => '1.1900000000000000',
                            ],
                            'price_monthly' => [
                                'net' => '1.0000000000',
                                'gross' => '1.1900000000000000',
                            ],
                        ],
                    ],
                    'storage_type' => 'local',
                    'cpu_type' => 'shared',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list server types, then get details of the first one
        $listResponse = $client->serverTypes()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->serverTypes());

        $firstServerType = $listResponse->serverTypes()[0];
        $serverTypeId = (string) $firstServerType->id();

        $getResponse = $client->serverTypes()->retrieve($serverTypeId);
        $this->assertInstanceOf(RetrieveResponse::class, $getResponse);
        $this->assertEquals($firstServerType->id(), $getResponse->serverType()->id());
        $this->assertEquals($firstServerType->name(), $getResponse->serverType()->name());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'server_types', 'list');
        $this->assertRequestWasMade($requests, 'server_types', 'retrieve');
    }

    /**
     * Test server type error response handling
     */
    public function test_server_type_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Server type not found', new Request('GET', '/server_types/nonexistent')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Server type not found');
        $client->serverTypes()->retrieve('nonexistent');
    }

    /**
     * Test server type empty list response
     */
    public function test_server_type_empty_list_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'server_types' => [],
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

        $response = $client->serverTypes()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(0, $response->serverTypes());
        $this->assertEmpty($response->serverTypes());
    }

    /**
     * Test server type pagination
     */
    public function test_server_type_pagination(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->serverTypes()->list();
        $pagination = $response->pagination();

        // Test pagination structure
        $this->assertIsArray($pagination);
        $this->assertArrayHasKey('current_page', $pagination);
        $this->assertArrayHasKey('per_page', $pagination);
        $this->assertArrayHasKey('total', $pagination);
        $this->assertArrayHasKey('last_page', $pagination);
        $this->assertArrayHasKey('from', $pagination);
        $this->assertArrayHasKey('to', $pagination);
        $this->assertArrayHasKey('has_more_pages', $pagination);
        $this->assertArrayHasKey('links', $pagination);

        // Test pagination values
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(25, $pagination['per_page']);
        $this->assertEquals(3, $pagination['total']);
        $this->assertEquals(1, $pagination['last_page']);
        $this->assertFalse($pagination['has_more_pages']);

        // Test links structure
        $this->assertIsArray($pagination['links']);
        $this->assertArrayHasKey('first', $pagination['links']);
        $this->assertArrayHasKey('last', $pagination['links']);
        $this->assertArrayHasKey('prev', $pagination['links']);
        $this->assertArrayHasKey('next', $pagination['links']);
    }

    /**
     * Test server type to array conversion
     */
    public function test_server_type_to_array_conversion(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->serverTypes()->list();
        $serverTypes = $response->serverTypes();

        foreach ($serverTypes as $serverType) {
            $array = $serverType->toArray();

            $this->assertIsArray($array);
            $this->assertArrayHasKey('id', $array);
            $this->assertArrayHasKey('name', $array);
            $this->assertArrayHasKey('description', $array);
            $this->assertArrayHasKey('cores', $array);
            $this->assertArrayHasKey('memory', $array);
            $this->assertArrayHasKey('disk', $array);
            $this->assertArrayHasKey('prices', $array);
            $this->assertArrayHasKey('storage_type', $array);
            $this->assertArrayHasKey('cpu_type', $array);

            $this->assertEquals($serverType->id(), $array['id']);
            $this->assertEquals($serverType->name(), $array['name']);
            $this->assertEquals($serverType->description(), $array['description']);
            $this->assertEquals($serverType->cores(), $array['cores']);
            $this->assertEquals($serverType->memory(), $array['memory']);
            $this->assertEquals($serverType->disk(), $array['disk']);
        }
    }

    /**
     * Test server type pricing structure
     */
    public function test_server_type_pricing_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->serverTypes()->list();
        $serverTypes = $response->serverTypes();

        foreach ($serverTypes as $serverType) {
            $prices = $serverType->prices();
            $this->assertIsArray($prices);

            foreach ($prices as $price) {
                $this->assertIsString($price['location']);
                $this->assertIsArray($price['price_hourly']);
                $this->assertIsArray($price['price_monthly']);

                // Test hourly pricing
                $this->assertArrayHasKey('net', $price['price_hourly']);
                $this->assertArrayHasKey('gross', $price['price_hourly']);
                $this->assertIsString($price['price_hourly']['net']);
                $this->assertIsString($price['price_hourly']['gross']);

                // Test monthly pricing
                $this->assertArrayHasKey('net', $price['price_monthly']);
                $this->assertArrayHasKey('gross', $price['price_monthly']);
                $this->assertIsString($price['price_monthly']['net']);
                $this->assertIsString($price['price_monthly']['gross']);

                // Test that gross price is higher than net price (includes VAT)
                $hourlyNet = (float) $price['price_hourly']['net'];
                $hourlyGross = (float) $price['price_hourly']['gross'];
                $this->assertGreaterThan($hourlyNet, $hourlyGross);

                $monthlyNet = (float) $price['price_monthly']['net'];
                $monthlyGross = (float) $price['price_monthly']['gross'];
                $this->assertGreaterThan($monthlyNet, $monthlyGross);
            }
        }
    }

    /**
     * Test server type resource specifications
     */
    public function test_server_type_resource_specifications(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->serverTypes()->list();
        $serverTypes = $response->serverTypes();

        foreach ($serverTypes as $serverType) {
            // Test that cores, memory, and disk are reasonable values
            $this->assertGreaterThanOrEqual(1, $serverType->cores());
            $this->assertLessThanOrEqual(32, $serverType->cores());

            $this->assertGreaterThanOrEqual(1.0, $serverType->memory());
            $this->assertLessThanOrEqual(128.0, $serverType->memory());

            $this->assertGreaterThanOrEqual(20, $serverType->disk());
            $this->assertLessThanOrEqual(1024, $serverType->disk());

            // Test that memory is a multiple of 0.5 (common in cloud providers)
            $this->assertEquals(0, fmod($serverType->memory() * 2, 1));
        }
    }

    /**
     * Test server type naming convention
     */
    public function test_server_type_naming_convention(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->serverTypes()->list();
        $serverTypes = $response->serverTypes();

        foreach ($serverTypes as $serverType) {
            // Test that server type names follow the expected pattern
            $this->assertMatchesRegularExpression('/^cx\d+$/', $serverType->name());

            // Test that description matches the name
            $this->assertEquals(strtoupper($serverType->name()), $serverType->description());
        }
    }
}
