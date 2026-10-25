<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\NetworkActions\ActionResponse;
use Boci\HetznerLaravel\Responses\NetworkActions\ListActionsResponse;
use Boci\HetznerLaravel\Responses\Networks\CreateResponse;
use Boci\HetznerLaravel\Responses\Networks\DeleteResponse;
use Boci\HetznerLaravel\Responses\Networks\ListResponse;
use Boci\HetznerLaravel\Responses\Networks\MembersResponse;
use Boci\HetznerLaravel\Responses\Networks\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Networks\UpdateResponse;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Networks Test Suite
 *
 * This test suite covers all functionality related to the Networks resource,
 * including listing, creating, getting, updating, deleting networks and their actions.
 */
final class NetworksTest extends TestCase
{
    /**
     * Test listing the members of a network with fake data
     */
    public function test_can_list_network_members_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->networks()->members('123', ['type' => 'server']);

        $this->assertInstanceOf(MembersResponse::class, $response);
        $this->assertCount(2, $response->members());
        $this->assertEquals('server', $response->members()[0]['type']);
        $this->assertEquals('10.0.1.0/24', $response->members()[0]['subnet']);
        $this->assertEquals(2, $response->pagination()['total_entries']);

        $this->assertRequestWasMade($requests, 'networks', 'members', function ($request) {
            return $request['parameters']['networkId'] === '123' && $request['parameters']['type'] === 'server';
        });
    }

    /**
     * Test that repeated network member filters are sent as repeated query keys
     */
    public function test_network_members_sends_repeated_filters(): void
    {
        $history = [];
        $stack = \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([
            new Response(200, [], (string) json_encode(['members' => [], 'meta' => ['pagination' => ['total_entries' => 0]]])),
        ]));
        $stack->push(\GuzzleHttp\Middleware::history($history));

        $networks = new \Boci\HetznerLaravel\Resources\Networks(new \GuzzleHttp\Client(['handler' => $stack]));

        $response = $networks->members('123', ['type' => ['server', 'load_balancer'], 'status' => 'ok']);

        $this->assertSame([], $response->members());
        $this->assertSame('/v1/networks/123/members', $history[0]['request']->getUri()->getPath());
        $this->assertSame('type=server&type=load_balancer&status=ok', $history[0]['request']->getUri()->getQuery());
    }

    /**
     * Test listing networks with fake data
     */
    public function test_can_list_networks_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->networks()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertIsArray($response->networks());
        $this->assertCount(2, $response->networks());

        $networks = $response->networks();
        /** @var array<int, array<string, mixed>> $networks */
        $this->assertIsArray($networks[0]);
        $this->assertEquals(1, $networks[0]['id']);
        $this->assertEquals('mynet', $networks[0]['name']);
        $this->assertEquals('10.0.0.0/16', $networks[0]['ip_range']);
        $this->assertIsArray($networks[0]['subnets']);
        $this->assertIsArray($networks[0]['routes']);
        $this->assertIsArray($networks[0]['servers']);
        $this->assertIsArray($networks[0]['protection']);
        $this->assertIsArray($networks[0]['labels']);

        $this->assertIsArray($networks[1]);
        $this->assertEquals(2, $networks[1]['id']);
        $this->assertEquals('mynet2', $networks[1]['name']);
        $this->assertEquals('192.168.0.0/16', $networks[1]['ip_range']);

        $this->assertRequestWasMade($requests, 'networks', 'list');
    }

    /**
     * Test listing networks with custom response
     */
    public function test_can_list_networks_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'networks' => [
                    [
                        'id' => 42,
                        'name' => 'custom-network',
                        'ip_range' => '172.16.0.0/16',
                        'subnets' => [
                            [
                                'type' => 'cloud',
                                'ip_range' => '172.16.1.0/24',
                                'network_zone' => 'eu-central',
                                'gateway' => '172.16.1.1',
                            ],
                        ],
                        'routes' => [
                            [
                                'destination' => '172.20.1.0/24',
                                'gateway' => '172.16.1.1',
                            ],
                        ],
                        'servers' => [1234, 5678],
                        'protection' => [
                            'delete' => true,
                        ],
                        'labels' => [
                            'environment' => 'staging',
                            'team' => 'backend',
                        ],
                        'created' => '2016-01-30T23:50:00+00:00',
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->networks()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->networks());

        $networks = $response->networks();
        /** @var array<int, array<string, mixed>> $networks */
        $network = $networks[0];
        $this->assertEquals(42, $network['id']);
        $this->assertEquals('custom-network', $network['name']);
        $this->assertEquals('172.16.0.0/16', $network['ip_range']);
        $this->assertCount(2, $network['servers']);
        $this->assertTrue($network['protection']['delete']);
        $this->assertEquals('staging', $network['labels']['environment']);
        $this->assertEquals('backend', $network['labels']['team']);

        $this->assertRequestWasMade($requests, 'networks', 'list');
    }

    /**
     * Test listing networks with query parameters
     */
    public function test_can_list_networks_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'mynet',
        ];

        $response = $client->networks()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);

        $this->assertRequestWasMade($requests, 'networks', 'list');
    }

    /**
     * Test creating a network
     */
    public function test_can_create_network(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'new-network',
            'ip_range' => '10.0.0.0/16',
            'subnets' => [
                [
                    'type' => 'cloud',
                    'ip_range' => '10.0.1.0/24',
                    'network_zone' => 'eu-central',
                ],
            ],
            'labels' => [
                'environment' => 'production',
            ],
        ];

        $response = $client->networks()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $this->assertIsArray($response->network());
        $this->assertIsArray($response->action());

        $network = $response->network();
        $this->assertEquals(1, $network['id']);
        $this->assertEquals('new-network', $network['name']);
        $this->assertEquals('10.0.0.0/16', $network['ip_range']);

        $action = $response->action();
        $this->assertEquals(1, $action['id']);
        $this->assertEquals('create_network', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'networks', 'create');
    }

    /**
     * Test creating network with custom response
     */
    public function test_can_create_network_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'network' => [
                    'id' => 99,
                    'name' => 'custom-new-network',
                    'ip_range' => '192.168.100.0/24',
                    'subnets' => [
                        [
                            'type' => 'cloud',
                            'ip_range' => '192.168.100.0/24',
                            'network_zone' => 'eu-central',
                            'gateway' => '192.168.100.1',
                        ],
                    ],
                    'routes' => [],
                    'servers' => [],
                    'protection' => [
                        'delete' => false,
                    ],
                    'labels' => [
                        'environment' => 'development',
                    ],
                    'created' => '2016-01-30T23:50:00+00:00',
                ],
                'action' => [
                    'id' => 99,
                    'command' => 'create_network',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2016-01-30T23:50:00+00:00',
                    'finished' => '2016-01-30T23:51:00+00:00',
                    'resources' => [
                        [
                            'id' => 99,
                            'type' => 'network',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'custom-new-network',
            'ip_range' => '192.168.100.0/24',
        ];

        $response = $client->networks()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $network = $response->network();
        $action = $response->action();

        $this->assertEquals(99, $network['id']);
        $this->assertEquals('custom-new-network', $network['name']);
        $this->assertEquals('192.168.100.0/24', $network['ip_range']);

        $this->assertEquals(99, $action['id']);
        $this->assertEquals('create_network', $action['command']);
        $this->assertEquals('success', $action['status']);
        $this->assertEquals(100, $action['progress']);

        $this->assertRequestWasMade($requests, 'networks', 'create');
    }

    /**
     * Test getting a specific network
     */
    public function test_can_get_specific_network(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '42';
        $response = $client->networks()->retrieve($networkId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $this->assertIsArray($response->network());

        $network = $response->network();
        $this->assertEquals(42, $network['id']);
        $this->assertEquals('mynet', $network['name']);
        $this->assertEquals('10.0.0.0/16', $network['ip_range']);
        $this->assertIsArray($network['subnets']);
        $this->assertIsArray($network['routes']);
        $this->assertIsArray($network['servers']);
        $this->assertIsArray($network['protection']);
        $this->assertIsArray($network['labels']);

        $this->assertRequestWasMade($requests, 'networks', 'get');
    }

    /**
     * Test getting network with custom response
     */
    public function test_can_get_network_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'network' => [
                    'id' => 123,
                    'name' => 'detailed-network',
                    'ip_range' => '172.20.0.0/16',
                    'subnets' => [
                        [
                            'type' => 'cloud',
                            'ip_range' => '172.20.1.0/24',
                            'network_zone' => 'eu-central',
                            'gateway' => '172.20.1.1',
                        ],
                        [
                            'type' => 'cloud',
                            'ip_range' => '172.20.2.0/24',
                            'network_zone' => 'eu-central',
                            'gateway' => '172.20.2.1',
                        ],
                    ],
                    'routes' => [
                        [
                            'destination' => '172.30.1.0/24',
                            'gateway' => '172.20.1.1',
                        ],
                        [
                            'destination' => '172.30.2.0/24',
                            'gateway' => '172.20.2.1',
                        ],
                    ],
                    'servers' => [1111, 2222, 3333],
                    'protection' => [
                        'delete' => true,
                    ],
                    'labels' => [
                        'environment' => 'production',
                        'team' => 'infrastructure',
                        'project' => 'web-app',
                    ],
                    'created' => '2016-01-30T23:50:00+00:00',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '123';
        $response = $client->networks()->retrieve($networkId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $network = $response->network();

        $this->assertEquals(123, $network['id']);
        $this->assertEquals('detailed-network', $network['name']);
        $this->assertEquals('172.20.0.0/16', $network['ip_range']);
        $this->assertCount(2, $network['subnets']);
        $this->assertCount(2, $network['routes']);
        $this->assertCount(3, $network['servers']);
        $this->assertTrue($network['protection']['delete']);
        $this->assertEquals('production', $network['labels']['environment']);
        $this->assertEquals('infrastructure', $network['labels']['team']);
        $this->assertEquals('web-app', $network['labels']['project']);

        $this->assertRequestWasMade($requests, 'networks', 'get');
    }

    /**
     * Test updating a network
     */
    public function test_can_update_network(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '42';
        $parameters = [
            'name' => 'updated-network-name',
            'labels' => [
                'environment' => 'staging',
                'team' => 'frontend',
            ],
        ];

        $response = $client->networks()->update($networkId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);
        $this->assertIsArray($response->network());

        $network = $response->network();
        $this->assertEquals(42, $network['id']);
        $this->assertEquals('updated-network-name', $network['name']);
        $this->assertEquals('staging', $network['labels']['environment']);
        $this->assertEquals('frontend', $network['labels']['team']);

        $this->assertRequestWasMade($requests, 'networks', 'update');
    }

    /**
     * Test deleting a network
     */
    public function test_can_delete_network(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '42';
        $response = $client->networks()->delete($networkId);

        $this->assertInstanceOf(DeleteResponse::class, $response);
        $this->assertIsArray($response->action());

        $action = $response->action();
        $this->assertEquals(1, $action['id']);
        $this->assertEquals('delete_network', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'networks', 'delete');
    }

    /**
     * Test network response structure
     */
    public function test_network_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->networks()->list();
        $networks = $response->networks();

        foreach ($networks as $network) {
            // Test all network properties
            $this->assertIsInt($network['id']);
            $this->assertIsString($network['name']);
            $this->assertIsString($network['ip_range']);
            $this->assertIsArray($network['subnets']);
            $this->assertIsArray($network['routes']);
            $this->assertIsArray($network['servers']);
            $this->assertIsArray($network['protection']);
            $this->assertIsArray($network['labels']);
            $this->assertIsString($network['created']);

            // Test subnet structure
            foreach ($network['subnets'] as $subnet) {
                $this->assertIsString($subnet['type']);
                $this->assertIsString($subnet['ip_range']);
                $this->assertIsString($subnet['network_zone']);
                $this->assertIsString($subnet['gateway']);
            }

            // Test route structure
            foreach ($network['routes'] as $route) {
                $this->assertIsString($route['destination']);
                $this->assertIsString($route['gateway']);
            }

            // Test protection structure
            $this->assertIsBool($network['protection']['delete']);

            // Test labels structure
            foreach ($network['labels'] as $key => $value) {
                $this->assertIsString($key);
                $this->assertIsString($value);
            }
        }
    }

    /**
     * Test handling network API exception
     */
    public function test_can_handle_network_api_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Network not found', new Request('GET', '/networks/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Network not found');

        $client->networks()->retrieve('999');
    }

    /**
     * Test handling network list exception
     */
    public function test_can_handle_network_list_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Rate limit exceeded', new Request('GET', '/networks')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->networks()->list();
    }

    /**
     * Test handling mixed network response types
     */
    public function test_can_handle_mixed_network_response_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'networks' => [
                    [
                        'id' => 1,
                        'name' => 'mynet',
                        'ip_range' => '10.0.0.0/16',
                        'subnets' => [
                            [
                                'type' => 'cloud',
                                'ip_range' => '10.0.1.0/24',
                                'network_zone' => 'eu-central',
                                'gateway' => '10.0.1.1',
                            ],
                        ],
                        'routes' => [],
                        'servers' => [],
                        'protection' => [
                            'delete' => false,
                        ],
                        'labels' => [
                            'environment' => 'production',
                        ],
                        'created' => '2016-01-30T23:50:00+00:00',
                    ],
                ],
            ]) ?: ''),
            new RequestException('Network not found', new Request('GET', '/networks/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $listResponse = $client->networks()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->networks());

        // Second call should throw exception
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Network not found');
        $client->networks()->retrieve('999');
    }

    /**
     * Test using individual network fake
     */
    public function test_using_individual_network_fake(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networksFake = $client->networks();

        $response = $networksFake->list(['page' => 1, 'per_page' => 5]);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(2, $response->networks());

        $networksFake->assertSent(function (array $request) {
            return $request['resource'] === 'networks' &&
                   $request['method'] === 'list' &&
                   isset($request['parameters']['page']) &&
                   $request['parameters']['page'] === 1 &&
                   isset($request['parameters']['per_page']) &&
                   $request['parameters']['per_page'] === 5;
        });
    }

    /**
     * Test network fake assert not sent
     */
    public function test_network_fake_assert_not_sent(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networksFake = $client->networks();

        // No requests made yet
        $networksFake->assertNotSent();

        // Make a request
        $networksFake->list();

        // Now it should fail
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Requests were sent to networks.');
        $networksFake->assertNotSent();
    }

    /**
     * Test network workflow simulation
     */
    public function test_network_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List networks response
            new Response(200, [], json_encode([
                'networks' => [
                    [
                        'id' => 1,
                        'name' => 'mynet',
                        'ip_range' => '10.0.0.0/16',
                        'subnets' => [
                            [
                                'type' => 'cloud',
                                'ip_range' => '10.0.1.0/24',
                                'network_zone' => 'eu-central',
                                'gateway' => '10.0.1.1',
                            ],
                        ],
                        'routes' => [],
                        'servers' => [],
                        'protection' => [
                            'delete' => false,
                        ],
                        'labels' => [
                            'environment' => 'production',
                        ],
                        'created' => '2016-01-30T23:50:00+00:00',
                    ],
                ],
            ]) ?: ''),
            // Get specific network response
            new Response(200, [], json_encode([
                'network' => [
                    'id' => 1,
                    'name' => 'mynet',
                    'ip_range' => '10.0.0.0/16',
                    'subnets' => [
                        [
                            'type' => 'cloud',
                            'ip_range' => '10.0.1.0/24',
                            'network_zone' => 'eu-central',
                            'gateway' => '10.0.1.1',
                        ],
                    ],
                    'routes' => [],
                    'servers' => [],
                    'protection' => [
                        'delete' => false,
                    ],
                    'labels' => [
                        'environment' => 'production',
                    ],
                    'created' => '2016-01-30T23:50:00+00:00',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list networks, then get details of the first one
        $listResponse = $client->networks()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->networks());

        $networks = $listResponse->networks();
        /** @var array<int, array<string, mixed>> $networks */
        $firstNetwork = $networks[0];
        $networkId = (string) $firstNetwork['id'];

        $getResponse = $client->networks()->retrieve($networkId);
        $this->assertInstanceOf(RetrieveResponse::class, $getResponse);
        $this->assertEquals($firstNetwork['id'], $getResponse->network()['id']);
        $this->assertEquals($firstNetwork['name'], $getResponse->network()['name']);

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'networks', 'list');
        $this->assertRequestWasMade($requests, 'networks', 'get');
    }

    /**
     * Test network error response handling
     */
    public function test_network_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Network not found', new Request('GET', '/networks/nonexistent')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Network not found');
        $client->networks()->retrieve('nonexistent');
    }

    /**
     * Test network empty list response
     */
    public function test_network_empty_list_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'networks' => [],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->networks()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(0, $response->networks());
        $this->assertEmpty($response->networks());
    }

    /**
     * Test network IP range validation
     */
    public function test_network_ip_range_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->networks()->list();
        $networks = $response->networks();

        foreach ($networks as $network) {
            // Test that IP ranges are valid CIDR notation
            $this->assertMatchesRegularExpression('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2}$/', $network['ip_range']);

            // Test subnet IP ranges
            foreach ($network['subnets'] as $subnet) {
                $this->assertMatchesRegularExpression('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2}$/', $subnet['ip_range']);
                $this->assertMatchesRegularExpression('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $subnet['gateway']);
            }

            // Test route destinations and gateways
            foreach ($network['routes'] as $route) {
                $this->assertMatchesRegularExpression('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2}$/', $route['destination']);
                $this->assertMatchesRegularExpression('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $route['gateway']);
            }
        }
    }

    /**
     * Test network subnet validation
     */
    public function test_network_subnet_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->networks()->list();
        $networks = $response->networks();

        foreach ($networks as $network) {
            $subnets = $network['subnets'];
            $this->assertIsArray($subnets);

            foreach ($subnets as $subnet) {
                // Test subnet type
                $this->assertEquals('cloud', $subnet['type']);

                // Test network zone
                $this->assertContains($subnet['network_zone'], ['eu-central', 'eu-west', 'us-east', 'us-west']);

                // Test that subnet IP range is within network IP range
                $networkRange = $network['ip_range'];
                $subnetRange = $subnet['ip_range'];

                // Basic validation that subnet is smaller than network (higher CIDR number = smaller range)
                $networkCidr = (int) substr($networkRange, strpos($networkRange, '/') + 1);
                $subnetCidr = (int) substr($subnetRange, strpos($subnetRange, '/') + 1);
                $this->assertGreaterThanOrEqual($networkCidr, $subnetCidr);
            }
        }
    }

    /**
     * Test network actions - list actions
     */
    public function test_can_list_network_actions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '42';
        $response = $client->networks()->actions()->list($networkId);

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertIsArray($response->actions());
        $this->assertCount(2, $response->actions());

        $actions = $response->actions();
        /** @var array<int, array<string, mixed>> $actions */
        $this->assertIsArray($actions[0]);
        $this->assertEquals(1, $actions[0]['id']);
        $this->assertEquals('add_route', $actions[0]['command']);
        $this->assertEquals('running', $actions[0]['status']);

        $this->assertIsArray($actions[1]);
        $this->assertEquals(2, $actions[1]['id']);
        $this->assertEquals('add_subnet', $actions[1]['command']);
        $this->assertEquals('success', $actions[1]['status']);

        $this->assertRequestWasMade($requests, 'network_actions', 'list');
    }

    /**
     * Test network actions - get action
     */
    public function test_can_get_network_action(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '42';
        $actionId = '123';
        $response = $client->networks()->actions()->retrieve($networkId, $actionId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertIsArray($response->action());

        $action = $response->action();
        $this->assertEquals(1, $action['id']);
        $this->assertEquals('add_route', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'network_actions', 'get');
    }

    /**
     * Test network actions - add route
     */
    public function test_can_add_network_route(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '42';
        $parameters = [
            'destination' => '10.100.1.0/24',
            'gateway' => '10.0.1.1',
        ];

        $response = $client->networks()->actions()->addRoute($networkId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action['id']);
        $this->assertEquals('add_route', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'network_actions', 'add_route');
    }

    /**
     * Test network actions - add subnet
     */
    public function test_can_add_network_subnet(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '42';
        $parameters = [
            'type' => 'cloud',
            'ip_range' => '10.0.2.0/24',
            'network_zone' => 'eu-central',
        ];

        $response = $client->networks()->actions()->addSubnet($networkId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action['id']);
        $this->assertEquals('add_subnet', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'network_actions', 'add_subnet');
    }

    /**
     * Test network actions - change IP range
     */
    public function test_can_change_network_ip_range(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '42';
        $parameters = [
            'ip_range' => '10.0.0.0/8',
        ];

        $response = $client->networks()->actions()->changeIpRange($networkId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action['id']);
        $this->assertEquals('change_ip_range', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'network_actions', 'change_ip_range');
    }

    /**
     * Test network actions - change protection
     */
    public function test_can_change_network_protection(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '42';
        $parameters = [
            'delete' => true,
        ];

        $response = $client->networks()->actions()->changeProtection($networkId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action['id']);
        $this->assertEquals('change_protection', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'network_actions', 'change_protection');
    }

    /**
     * Test network actions - delete route
     */
    public function test_can_delete_network_route(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '42';
        $routeId = '123';

        $response = $client->networks()->actions()->deleteRoute($networkId, $routeId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action['id']);
        $this->assertEquals('delete_route', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'network_actions', 'delete_route');
    }

    /**
     * Test network actions - delete subnet
     */
    public function test_can_delete_network_subnet(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $networkId = '42';
        $subnetId = '456';

        $response = $client->networks()->actions()->deleteSubnet($networkId, $subnetId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action['id']);
        $this->assertEquals('delete_subnet', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'network_actions', 'delete_subnet');
    }

    /**
     * Test network actions workflow simulation
     */
    public function test_network_actions_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List actions response
            new Response(200, [], json_encode([
                'actions' => [
                    [
                        'id' => 1,
                        'command' => 'add_route',
                        'status' => 'running',
                        'progress' => 0,
                        'started' => '2016-01-30T23:50:00+00:00',
                        'finished' => null,
                        'resources' => [
                            [
                                'id' => 1,
                                'type' => 'network',
                            ],
                        ],
                        'error' => null,
                    ],
                ],
            ]) ?: ''),
            // Get specific action response
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 1,
                    'command' => 'add_route',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2016-01-30T23:50:00+00:00',
                    'finished' => '2016-01-30T23:51:00+00:00',
                    'resources' => [
                        [
                            'id' => 1,
                            'type' => 'network',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list network actions, then get details of the first one
        $listResponse = $client->networks()->actions()->list('42');
        $this->assertInstanceOf(ListActionsResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->actions());

        $actions = $listResponse->actions();
        /** @var array<int, array<string, mixed>> $actions */
        $firstAction = $actions[0];
        $actionId = (string) $firstAction['id'];

        $getResponse = $client->networks()->actions()->retrieve('42', $actionId);
        $this->assertInstanceOf(ActionResponse::class, $getResponse);
        $this->assertEquals($firstAction['id'], $getResponse->action()['id']);
        $this->assertEquals($firstAction['command'], $getResponse->action()['command']);

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'network_actions', 'list');
        $this->assertRequestWasMade($requests, 'network_actions', 'get');
    }

    /**
     * Test network action error response handling
     */
    public function test_network_action_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Network action not found', new Request('GET', '/networks/42/actions/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Network action not found');
        $client->networks()->actions()->retrieve('42', '999');
    }

    /**
     * Test network action response structure
     */
    public function test_network_action_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->networks()->actions()->list('42');
        $actions = $response->actions();

        foreach ($actions as $action) {
            // Test all action properties
            $this->assertIsInt($action['id']);
            $this->assertIsString($action['command']);
            $this->assertIsString($action['status']);
            $this->assertIsInt($action['progress']);
            $this->assertIsString($action['started']);
            $this->assertIsArray($action['resources']);
            $this->assertNull($action['error']);

            // Test action commands
            $this->assertContains($action['command'], [
                'add_route',
                'add_subnet',
                'change_ip_range',
                'change_protection',
                'delete_route',
                'delete_subnet',
            ]);

            // Test action statuses
            $this->assertContains($action['status'], ['running', 'success', 'error']);

            // Test progress range
            $this->assertGreaterThanOrEqual(0, $action['progress']);
            $this->assertLessThanOrEqual(100, $action['progress']);

            // Test resources structure
            foreach ($action['resources'] as $resource) {
                $this->assertIsInt($resource['id']);
                $this->assertIsString($resource['type']);
                $this->assertEquals('network', $resource['type']);
            }
        }
    }
}
