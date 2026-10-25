<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\FirewallActions\ActionResponse;
use Boci\HetznerLaravel\Responses\FirewallActions\ListActionsResponse;
use Boci\HetznerLaravel\Responses\Firewalls\CreateResponse;
use Boci\HetznerLaravel\Responses\Firewalls\DeleteResponse;
use Boci\HetznerLaravel\Responses\Firewalls\Firewall;
use Boci\HetznerLaravel\Responses\Firewalls\ListResponse;
use Boci\HetznerLaravel\Responses\Firewalls\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Firewalls\UpdateResponse;
use Boci\HetznerLaravel\Testing\FirewallsFake;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Psr7\Response;

/**
 * Firewalls Test Suite
 *
 * This test suite covers all functionality related to the Firewalls resource,
 * including listing, creating, getting, updating, deleting firewalls, and
 * managing firewall actions.
 */
final class FirewallsTest extends TestCase
{
    /**
     * Test listing firewalls with fake data
     */
    public function test_can_list_firewalls_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->firewalls()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->firewalls());

        $firewalls = $response->firewalls();
        $this->assertInstanceOf(Firewall::class, $firewalls[0]);
        $this->assertEquals(1, $firewalls[0]->id());
        $this->assertEquals('test-firewall-1', $firewalls[0]->name());

        $pagination = $response->pagination();
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(25, $pagination['per_page']);
        $this->assertEquals(1, $pagination['total']);

        $this->assertRequestWasMade($requests, 'firewalls', 'list');
    }

    /**
     * Test listing firewalls with custom response
     */
    public function test_can_list_firewalls_with_custom_response(): void
    {
        $customData = [
            'firewalls' => [
                [
                    'id' => 1,
                    'name' => 'web-firewall',
                    'created' => '2023-01-01T00:00:00+00:00',
                    'rules' => [
                        [
                            'direction' => 'in',
                            'source_ips' => ['0.0.0.0/0'],
                            'destination_ips' => [],
                            'source_ports' => [],
                            'destination_ports' => ['80', '443'],
                            'protocol' => 'tcp',
                            'action' => 'accept',
                            'description' => 'Allow HTTP/HTTPS',
                        ],
                    ],
                    'applied_to' => [
                        [
                            'type' => 'server',
                            'server' => [
                                'id' => 1,
                                'name' => 'web-server',
                            ],
                        ],
                    ],
                    'labels' => ['environment' => 'production'],
                ],
                [
                    'id' => 2,
                    'name' => 'db-firewall',
                    'created' => '2023-01-01T01:00:00+00:00',
                    'rules' => [
                        [
                            'direction' => 'in',
                            'source_ips' => ['10.0.0.0/8'],
                            'destination_ips' => [],
                            'source_ports' => [],
                            'destination_ports' => ['3306'],
                            'protocol' => 'tcp',
                            'action' => 'accept',
                            'description' => 'Allow MySQL from internal network',
                        ],
                    ],
                    'applied_to' => [],
                    'labels' => ['environment' => 'production', 'type' => 'database'],
                ],
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'per_page' => 25,
                    'previous_page' => null,
                    'next_page' => null,
                    'last_page' => 1,
                    'total_entries' => 2,
                ],
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->firewalls()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(2, $response->firewalls());

        $firewalls = $response->firewalls();
        $this->assertEquals('web-firewall', $firewalls[0]->name());
        $this->assertEquals('db-firewall', $firewalls[1]->name());

        $rules = $firewalls[0]->rules();
        $this->assertCount(1, $rules);
        /** @var array<int, array<string, mixed>> $rules */
        $this->assertEquals('in', $rules[0]['direction']);
        $this->assertEquals('accept', $rules[0]['action']);

        $this->assertRequestWasMade($requests, 'firewalls', 'list');
    }

    /**
     * Test listing firewalls with query parameters
     */
    public function test_can_list_firewalls_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'web-firewall',
        ];

        $response = $client->firewalls()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertRequestWasMade($requests, 'firewalls', 'list');
    }

    /**
     * Test creating a firewall
     */
    public function test_can_create_firewall(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'test-firewall',
            'rules' => [
                [
                    'direction' => 'in',
                    'source_ips' => ['0.0.0.0/0'],
                    'destination_ips' => [],
                    'source_ports' => [],
                    'destination_ports' => ['80', '443'],
                    'protocol' => 'tcp',
                    'action' => 'accept',
                    'description' => 'Allow HTTP/HTTPS',
                ],
            ],
            'labels' => ['environment' => 'test'],
        ];

        $response = $client->firewalls()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);

        $firewall = $response->firewall();
        $this->assertInstanceOf(Firewall::class, $firewall);
        $this->assertEquals(1, $firewall->id());
        $this->assertEquals('test-firewall', $firewall->name());

        $action = $response->action();
        $this->assertIsArray($action);
        $this->assertEquals('create_firewall', $action['command']);

        $this->assertRequestWasMade($requests, 'firewalls', 'create');
    }

    /**
     * Test creating a firewall with custom response
     */
    public function test_can_create_firewall_with_custom_response(): void
    {
        $customData = [
            'firewall' => [
                'id' => 123,
                'name' => 'custom-firewall',
                'created' => '2023-01-01T12:00:00+00:00',
                'rules' => [
                    [
                        'direction' => 'in',
                        'source_ips' => ['192.168.1.0/24'],
                        'destination_ips' => [],
                        'source_ports' => [],
                        'destination_ports' => ['22'],
                        'protocol' => 'tcp',
                        'action' => 'accept',
                        'description' => 'Allow SSH from local network',
                    ],
                ],
                'applied_to' => [],
                'labels' => ['environment' => 'development'],
            ],
            'action' => [
                'id' => 456,
                'command' => 'create_firewall',
                'status' => 'success',
                'progress' => 100,
                'started' => '2023-01-01T12:00:00+00:00',
                'finished' => '2023-01-01T12:00:01+00:00',
                'resources' => [
                    [
                        'id' => 123,
                        'type' => 'firewall',
                    ],
                ],
                'error' => null,
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'custom-firewall',
            'rules' => [
                [
                    'direction' => 'in',
                    'source_ips' => ['192.168.1.0/24'],
                    'destination_ips' => [],
                    'source_ports' => [],
                    'destination_ports' => ['22'],
                    'protocol' => 'tcp',
                    'action' => 'accept',
                    'description' => 'Allow SSH from local network',
                ],
            ],
        ];

        $response = $client->firewalls()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);

        $firewall = $response->firewall();
        $this->assertEquals(123, $firewall->id());
        $this->assertEquals('custom-firewall', $firewall->name());

        $rules = $firewall->rules();
        $this->assertCount(1, $rules);
        /** @var array<int, array<string, mixed>> $rules */
        $this->assertEquals('192.168.1.0/24', $rules[0]['source_ips'][0]);

        $this->assertRequestWasMade($requests, 'firewalls', 'create');
    }

    /**
     * Test getting a specific firewall
     */
    public function test_can_get_specific_firewall(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $firewallId = '123';
        $response = $client->firewalls()->retrieve($firewallId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);

        $firewall = $response->firewall();
        $this->assertInstanceOf(Firewall::class, $firewall);
        $this->assertEquals(123, $firewall->id());
        $this->assertEquals('test-firewall-123', $firewall->name());

        $this->assertRequestWasMade($requests, 'firewalls', 'get');
    }

    /**
     * Test getting a specific firewall with custom response
     */
    public function test_can_get_specific_firewall_with_custom_response(): void
    {
        $customData = [
            'firewall' => [
                'id' => 456,
                'name' => 'production-firewall',
                'created' => '2023-01-01T10:00:00+00:00',
                'rules' => [
                    [
                        'direction' => 'in',
                        'source_ips' => ['0.0.0.0/0'],
                        'destination_ips' => [],
                        'source_ports' => [],
                        'destination_ports' => ['80', '443', '22'],
                        'protocol' => 'tcp',
                        'action' => 'accept',
                        'description' => 'Allow web and SSH',
                    ],
                    [
                        'direction' => 'in',
                        'source_ips' => ['0.0.0.0/0'],
                        'destination_ips' => [],
                        'source_ports' => [],
                        'destination_ports' => [],
                        'protocol' => 'icmp',
                        'action' => 'accept',
                        'description' => 'Allow ICMP',
                    ],
                ],
                'applied_to' => [
                    [
                        'type' => 'server',
                        'server' => [
                            'id' => 789,
                            'name' => 'production-server',
                        ],
                    ],
                ],
                'labels' => ['environment' => 'production', 'team' => 'backend'],
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $firewallId = '456';
        $response = $client->firewalls()->retrieve($firewallId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);

        $firewall = $response->firewall();
        $this->assertEquals(456, $firewall->id());
        $this->assertEquals('production-firewall', $firewall->name());

        $rules = $firewall->rules();
        $this->assertCount(2, $rules);
        /** @var array<int, array<string, mixed>> $rules */
        $this->assertEquals('tcp', $rules[0]['protocol']);
        $this->assertEquals('icmp', $rules[1]['protocol']);

        $appliedTo = $firewall->appliedTo();
        $this->assertCount(1, $appliedTo);
        /** @var array<int, array<string, mixed>> $appliedTo */
        $this->assertEquals('server', $appliedTo[0]['type']);
        $this->assertEquals(789, $appliedTo[0]['server']['id']);

        $this->assertRequestWasMade($requests, 'firewalls', 'get');
    }

    /**
     * Test updating a firewall
     */
    public function test_can_update_firewall(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $firewallId = '123';
        $parameters = [
            'name' => 'updated-firewall-name',
            'labels' => ['environment' => 'staging'],
        ];

        $response = $client->firewalls()->update($firewallId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);

        $firewall = $response->firewall();
        $this->assertInstanceOf(Firewall::class, $firewall);
        $this->assertEquals(123, $firewall->id());
        $this->assertEquals('updated-firewall-name', $firewall->name());

        $this->assertRequestWasMade($requests, 'firewalls', 'update');
    }

    /**
     * Test updating a firewall with custom response
     */
    public function test_can_update_firewall_with_custom_response(): void
    {
        $customData = [
            'firewall' => [
                'id' => 789,
                'name' => 'updated-production-firewall',
                'created' => '2023-01-01T10:00:00+00:00',
                'rules' => [
                    [
                        'direction' => 'in',
                        'source_ips' => ['0.0.0.0/0'],
                        'destination_ips' => [],
                        'source_ports' => [],
                        'destination_ports' => ['80', '443'],
                        'protocol' => 'tcp',
                        'action' => 'accept',
                        'description' => 'Allow HTTP/HTTPS only',
                    ],
                ],
                'applied_to' => [],
                'labels' => ['environment' => 'production', 'updated' => 'true'],
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $firewallId = '789';
        $parameters = [
            'name' => 'updated-production-firewall',
            'rules' => [
                [
                    'direction' => 'in',
                    'source_ips' => ['0.0.0.0/0'],
                    'destination_ips' => [],
                    'source_ports' => [],
                    'destination_ports' => ['80', '443'],
                    'protocol' => 'tcp',
                    'action' => 'accept',
                    'description' => 'Allow HTTP/HTTPS only',
                ],
            ],
            'labels' => ['environment' => 'production', 'updated' => 'true'],
        ];

        $response = $client->firewalls()->update($firewallId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);

        $firewall = $response->firewall();
        $this->assertEquals(789, $firewall->id());
        $this->assertEquals('updated-production-firewall', $firewall->name());

        $labels = $firewall->labels();
        $this->assertEquals('production', $labels['environment']);
        $this->assertEquals('true', $labels['updated']);

        $this->assertRequestWasMade($requests, 'firewalls', 'update');
    }

    /**
     * Test deleting a firewall
     */
    public function test_can_delete_firewall(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $firewallId = '123';
        $response = $client->firewalls()->delete($firewallId);

        $this->assertInstanceOf(DeleteResponse::class, $response);

        $this->assertRequestWasMade($requests, 'firewalls', 'delete');
    }

    /**
     * Test firewall response structure validation
     */
    public function test_firewall_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->firewalls()->list();
        $firewalls = $response->firewalls();

        foreach ($firewalls as $firewall) {
            $this->assertIsInt($firewall->id());
            $this->assertIsString($firewall->name());
            $this->assertIsString($firewall->created());
            $this->assertIsArray($firewall->rules());
            $this->assertIsArray($firewall->appliedTo());
            $this->assertIsArray($firewall->labels());
        }

        // Test pagination structure
        $pagination = $response->pagination();
        $this->assertArrayHasKey('current_page', $pagination);
        $this->assertArrayHasKey('per_page', $pagination);
        $this->assertArrayHasKey('total', $pagination);
        $this->assertArrayHasKey('last_page', $pagination);
        $this->assertArrayHasKey('has_more_pages', $pagination);
        $this->assertArrayHasKey('links', $pagination);
    }

    /**
     * Test firewall rules structure
     */
    public function test_firewall_rules_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->firewalls()->list();
        $firewalls = $response->firewalls();

        foreach ($firewalls as $firewall) {
            $rules = $firewall->rules();
            foreach ($rules as $rule) {
                $this->assertArrayHasKey('direction', $rule);
                $this->assertArrayHasKey('source_ips', $rule);
                $this->assertArrayHasKey('destination_ips', $rule);
                $this->assertArrayHasKey('source_ports', $rule);
                $this->assertArrayHasKey('destination_ports', $rule);
                $this->assertArrayHasKey('protocol', $rule);
                $this->assertArrayHasKey('action', $rule);
                $this->assertArrayHasKey('description', $rule);

                $this->assertContains($rule['direction'], ['in', 'out']);
                $this->assertContains($rule['action'], ['accept', 'drop']);
                $this->assertContains($rule['protocol'], ['tcp', 'udp', 'icmp']);
            }
        }
    }

    /**
     * Test handling API exceptions
     */
    public function test_can_handle_firewalls_api_exceptions(): void
    {
        $exception = new \Exception('API connection failed');

        $requests = [];
        $responses = [$exception];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API connection failed');

        $client->firewalls()->list();
    }

    /**
     * Test handling error responses
     */
    public function test_can_handle_firewalls_error_responses(): void
    {
        $errorResponse = new Response(400, [], json_encode([
            'error' => [
                'code' => 'invalid_request',
                'message' => 'Invalid firewall parameters provided',
            ],
        ]) ?: '');

        $requests = [];
        $responses = [$errorResponse];
        $client = $this->fakeClient($responses, $requests);

        // The fake client will return the error response as-is
        $response = $client->firewalls()->create(['name' => 'invalid']);
        $this->assertInstanceOf(CreateResponse::class, $response);

        $this->assertRequestWasMade($requests, 'firewalls', 'create');
    }

    /**
     * Test using individual firewalls fake
     */
    public function test_using_individual_firewalls_fake(): void
    {
        $responses = [];
        $requests = [];

        $firewallsFake = new FirewallsFake($responses, $requests);

        // Test various firewall operations
        $firewallsFake->list();
        $firewallsFake->create(['name' => 'test-firewall']);
        $firewallsFake->retrieve('123');
        $firewallsFake->update('123', ['name' => 'updated']);
        $firewallsFake->delete('123');

        // Assert all requests were made
        $this->assertCount(5, $requests);

        // Test resource-specific assertions
        $firewallsFake->assertSent(function ($request) {
            return $request['method'] === 'list';
        });

        $firewallsFake->assertSent(function ($request) {
            return $request['method'] === 'create' &&
                   $request['parameters']['name'] === 'test-firewall';
        });

        $firewallsFake->assertSent(function ($request) {
            return $request['method'] === 'get' &&
                   $request['parameters']['firewallId'] === '123';
        });
    }

    /**
     * Test mixed response types for firewalls
     */
    public function test_can_handle_mixed_firewalls_response_types(): void
    {
        $responses = [
            new Response(200, [], json_encode([
                'firewalls' => [
                    [
                        'id' => 1,
                        'name' => 'test-firewall',
                        'created' => '2023-01-01T00:00:00+00:00',
                        'rules' => [],
                        'applied_to' => [],
                        'labels' => [],
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
            new \Exception('Network timeout'),
        ];

        $requests = [];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $response = $client->firewalls()->list();
        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->firewalls());

        // Second call should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Network timeout');
        $client->firewalls()->retrieve('456');
    }

    /**
     * Test firewalls with complex parameters
     */
    public function test_can_list_firewalls_with_complex_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 3,
            'per_page' => 50,
            'name' => 'production',
            'sort' => 'id:desc',
        ];

        $response = $client->firewalls()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertRequestWasMade($requests, 'firewalls', 'list');
    }

    /**
     * Test firewall workflow simulation
     */
    public function test_firewall_workflow_simulation(): void
    {
        $responses = [
            // Create firewall
            new Response(200, [], json_encode([
                'firewall' => [
                    'id' => 1,
                    'name' => 'web-firewall',
                    'created' => '2023-01-01T10:00:00+00:00',
                    'rules' => [
                        [
                            'direction' => 'in',
                            'source_ips' => ['0.0.0.0/0'],
                            'destination_ips' => [],
                            'source_ports' => [],
                            'destination_ports' => ['80', '443'],
                            'protocol' => 'tcp',
                            'action' => 'accept',
                            'description' => 'Allow HTTP/HTTPS',
                        ],
                    ],
                    'applied_to' => [],
                    'labels' => [],
                ],
                'action' => [
                    'id' => 1,
                    'command' => 'create_firewall',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2023-01-01T10:00:00+00:00',
                    'finished' => '2023-01-01T10:00:01+00:00',
                    'resources' => [['id' => 1, 'type' => 'firewall']],
                    'error' => null,
                ],
            ]) ?: ''),
            // Get firewall
            new Response(200, [], json_encode([
                'firewall' => [
                    'id' => 1,
                    'name' => 'web-firewall',
                    'created' => '2023-01-01T10:00:00+00:00',
                    'rules' => [
                        [
                            'direction' => 'in',
                            'source_ips' => ['0.0.0.0/0'],
                            'destination_ips' => [],
                            'source_ports' => [],
                            'destination_ports' => ['80', '443'],
                            'protocol' => 'tcp',
                            'action' => 'accept',
                            'description' => 'Allow HTTP/HTTPS',
                        ],
                    ],
                    'applied_to' => [],
                    'labels' => [],
                ],
            ]) ?: ''),
            // Update firewall
            new Response(200, [], json_encode([
                'firewall' => [
                    'id' => 1,
                    'name' => 'updated-web-firewall',
                    'created' => '2023-01-01T10:00:00+00:00',
                    'rules' => [
                        [
                            'direction' => 'in',
                            'source_ips' => ['0.0.0.0/0'],
                            'destination_ips' => [],
                            'source_ports' => [],
                            'destination_ports' => ['80', '443'],
                            'protocol' => 'tcp',
                            'action' => 'accept',
                            'description' => 'Allow HTTP/HTTPS',
                        ],
                    ],
                    'applied_to' => [],
                    'labels' => ['environment' => 'production'],
                ],
            ]) ?: ''),
        ];

        $requests = [];
        $client = $this->fakeClient($responses, $requests);

        // Simulate workflow: create firewall, get details, then update
        $createResponse = $client->firewalls()->create([
            'name' => 'web-firewall',
            'rules' => [
                [
                    'direction' => 'in',
                    'source_ips' => ['0.0.0.0/0'],
                    'destination_ips' => [],
                    'source_ports' => [],
                    'destination_ports' => ['80', '443'],
                    'protocol' => 'tcp',
                    'action' => 'accept',
                    'description' => 'Allow HTTP/HTTPS',
                ],
            ],
        ]);

        $firewall = $createResponse->firewall();
        $this->assertEquals(1, $firewall->id());
        $this->assertEquals('web-firewall', $firewall->name());

        $getResponse = $client->firewalls()->retrieve('1');
        $retrievedFirewall = $getResponse->firewall();
        $this->assertEquals($firewall->id(), $retrievedFirewall->id());

        $updateResponse = $client->firewalls()->update('1', [
            'name' => 'updated-web-firewall',
            'labels' => ['environment' => 'production'],
        ]);
        $updatedFirewall = $updateResponse->firewall();
        $this->assertEquals('updated-web-firewall', $updatedFirewall->name());

        // Verify all requests were made
        $this->assertRequestWasMade($requests, 'firewalls', 'create');
        $this->assertRequestWasMade($requests, 'firewalls', 'get');
        $this->assertRequestWasMade($requests, 'firewalls', 'update');
    }

    /**
     * Test firewalls resource assertions
     */
    public function test_firewalls_resource_assertions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $client->firewalls()->list(['name' => 'production']);
        $client->firewalls()->create(['name' => 'test-firewall']);
        $client->firewalls()->retrieve('999');
        $client->firewalls()->update('999', ['name' => 'updated']);
        $client->firewalls()->delete('999');

        // Test that specific requests were made
        $this->assertRequestWasMade($requests, 'firewalls', 'list');
        $this->assertRequestWasMade($requests, 'firewalls', 'create');
        $this->assertRequestWasMade($requests, 'firewalls', 'get');
        $this->assertRequestWasMade($requests, 'firewalls', 'update');
        $this->assertRequestWasMade($requests, 'firewalls', 'delete');

        // Test that no other requests were made
        $this->assertNoRequestWasMade($requests, 'servers');
        $this->assertNoRequestWasMade($requests, 'images');
    }

    /**
     * Test firewalls with specific firewall data
     */
    public function test_firewalls_with_specific_firewall_data(): void
    {
        $customData = [
            'firewall' => [
                'id' => 200,
                'name' => 'database-firewall',
                'created' => '2023-01-01T15:00:00+00:00',
                'rules' => [
                    [
                        'direction' => 'in',
                        'source_ips' => ['10.0.0.0/8', '172.16.0.0/12'],
                        'destination_ips' => [],
                        'source_ports' => [],
                        'destination_ports' => ['3306', '5432'],
                        'protocol' => 'tcp',
                        'action' => 'accept',
                        'description' => 'Allow database connections from private networks',
                    ],
                    [
                        'direction' => 'in',
                        'source_ips' => ['0.0.0.0/0'],
                        'destination_ips' => [],
                        'source_ports' => [],
                        'destination_ports' => [],
                        'protocol' => 'tcp',
                        'action' => 'drop',
                        'description' => 'Drop all other connections',
                    ],
                ],
                'applied_to' => [
                    [
                        'type' => 'server',
                        'server' => [
                            'id' => 100,
                            'name' => 'database-server',
                        ],
                    ],
                ],
                'labels' => ['environment' => 'production', 'type' => 'database', 'team' => 'backend'],
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->firewalls()->retrieve('200');
        $firewall = $response->firewall();

        $this->assertEquals(200, $firewall->id());
        $this->assertEquals('database-firewall', $firewall->name());
        $this->assertEquals('2023-01-01T15:00:00+00:00', $firewall->created());

        $rules = $firewall->rules();
        $this->assertCount(2, $rules);
        /** @var array<int, array<string, mixed>> $rules */
        $this->assertEquals('accept', $rules[0]['action']);
        $this->assertEquals('drop', $rules[1]['action']);
        $this->assertContains('3306', $rules[0]['destination_ports']);
        $this->assertContains('5432', $rules[0]['destination_ports']);

        $appliedTo = $firewall->appliedTo();
        $this->assertCount(1, $appliedTo);
        /** @var array<int, array<string, mixed>> $appliedTo */
        $this->assertEquals('server', $appliedTo[0]['type']);
        $this->assertEquals(100, $appliedTo[0]['server']['id']);

        $labels = $firewall->labels();
        $this->assertEquals('production', $labels['environment']);
        $this->assertEquals('database', $labels['type']);
        $this->assertEquals('backend', $labels['team']);

        $this->assertRequestWasMade($requests, 'firewalls', 'get');
    }

    // ===== FIREWALL ACTIONS TESTS =====

    /**
     * Test listing firewall actions
     */
    public function test_can_list_firewall_actions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $firewallId = '123';
        $response = $client->firewalls()->actions()->list($firewallId);

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertCount(2, $response->actions());

        $actions = $response->actions();
        $this->assertIsArray($actions);
        /** @var array<int, array<string, mixed>> $actions */
        $this->assertEquals('apply_to_resources', $actions[0]['command']);
        $this->assertEquals('success', $actions[0]['status']);

        $this->assertRequestWasMade($requests, 'firewall_actions', 'list');
    }

    /**
     * Test getting a specific firewall action
     */
    public function test_can_get_specific_firewall_action(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $firewallId = '123';
        $actionId = '456';
        $response = $client->firewalls()->actions()->retrieve($firewallId, $actionId);

        $this->assertInstanceOf(ActionResponse::class, $response);

        $action = $response->action();
        $this->assertIsArray($action);
        $this->assertEquals(456, $action['id']);
        $this->assertEquals('apply_to_resources', $action['command']);
        $this->assertEquals('success', $action['status']);

        $this->assertRequestWasMade($requests, 'firewall_actions', 'get');
    }

    /**
     * Test applying firewall to resources
     */
    public function test_can_apply_firewall_to_resources(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $firewallId = '123';
        $parameters = [
            'apply_to' => [
                [
                    'type' => 'server',
                    'server' => [
                        'id' => 789,
                    ],
                ],
            ],
        ];

        $response = $client->firewalls()->actions()->applyToResources($firewallId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);

        $action = $response->action();
        $this->assertEquals('apply_to_resources', $action['command']);
        $this->assertEquals('success', $action['status']);

        $this->assertRequestWasMade($requests, 'firewall_actions', 'apply_to_resources');
    }

    /**
     * Test removing firewall from resources
     */
    public function test_can_remove_firewall_from_resources(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $firewallId = '123';
        $parameters = [
            'remove_from' => [
                [
                    'type' => 'server',
                    'server' => [
                        'id' => 789,
                    ],
                ],
            ],
        ];

        $response = $client->firewalls()->actions()->removeFromResources($firewallId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);

        $action = $response->action();
        $this->assertEquals('remove_from_resources', $action['command']);
        $this->assertEquals('success', $action['status']);

        $this->assertRequestWasMade($requests, 'firewall_actions', 'remove_from_resources');
    }

    /**
     * Test setting firewall rules
     */
    public function test_can_set_firewall_rules(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $firewallId = '123';
        $parameters = [
            'rules' => [
                [
                    'direction' => 'in',
                    'source_ips' => ['0.0.0.0/0'],
                    'destination_ips' => [],
                    'source_ports' => [],
                    'destination_ports' => ['80', '443'],
                    'protocol' => 'tcp',
                    'action' => 'accept',
                    'description' => 'Allow HTTP/HTTPS',
                ],
            ],
        ];

        $response = $client->firewalls()->actions()->setRules($firewallId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);

        $action = $response->action();
        $this->assertEquals('set_rules', $action['command']);
        $this->assertEquals('success', $action['status']);

        $this->assertRequestWasMade($requests, 'firewall_actions', 'set_rules');
    }

    /**
     * Test firewall actions with custom responses
     */
    public function test_firewall_actions_with_custom_responses(): void
    {
        $customData = [
            'action' => [
                'id' => 999,
                'command' => 'apply_to_resources',
                'status' => 'running',
                'progress' => 50,
                'started' => '2023-01-01T16:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 123,
                        'type' => 'firewall',
                    ],
                    [
                        'id' => 789,
                        'type' => 'server',
                    ],
                ],
                'error' => null,
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->firewalls()->actions()->applyToResources('123', [
            'apply_to' => [
                [
                    'type' => 'server',
                    'server' => ['id' => 789],
                ],
            ],
        ]);

        $this->assertInstanceOf(ActionResponse::class, $response);

        $action = $response->action();
        $this->assertEquals(999, $action['id']);
        $this->assertEquals('apply_to_resources', $action['command']);
        $this->assertEquals('running', $action['status']);
        $this->assertEquals(50, $action['progress']);
        $this->assertNull($action['finished']);

        $resources = $action['resources'];
        $this->assertCount(2, $resources);
        $this->assertEquals(123, $resources[0]['id']);
        $this->assertEquals('firewall', $resources[0]['type']);
        $this->assertEquals(789, $resources[1]['id']);
        $this->assertEquals('server', $resources[1]['type']);

        $this->assertRequestWasMade($requests, 'firewall_actions', 'apply_to_resources');
    }

    /**
     * Test complete firewall management workflow
     */
    public function test_complete_firewall_management_workflow(): void
    {
        $responses = [
            // Create firewall
            new Response(200, [], json_encode([
                'firewall' => [
                    'id' => 1,
                    'name' => 'web-firewall',
                    'created' => '2023-01-01T10:00:00+00:00',
                    'rules' => [
                        [
                            'direction' => 'in',
                            'source_ips' => ['0.0.0.0/0'],
                            'destination_ips' => [],
                            'source_ports' => [],
                            'destination_ports' => ['80', '443'],
                            'protocol' => 'tcp',
                            'action' => 'accept',
                            'description' => 'Allow HTTP/HTTPS',
                        ],
                    ],
                    'applied_to' => [],
                    'labels' => [],
                ],
                'action' => [
                    'id' => 1,
                    'command' => 'create_firewall',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2023-01-01T10:00:00+00:00',
                    'finished' => '2023-01-01T10:00:01+00:00',
                    'resources' => [['id' => 1, 'type' => 'firewall']],
                    'error' => null,
                ],
            ]) ?: ''),
            // Apply to resources
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 2,
                    'command' => 'apply_to_resources',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2023-01-01T10:01:00+00:00',
                    'finished' => '2023-01-01T10:01:01+00:00',
                    'resources' => [
                        ['id' => 1, 'type' => 'firewall'],
                        ['id' => 789, 'type' => 'server'],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
            // Set rules
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 3,
                    'command' => 'set_rules',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2023-01-01T10:02:00+00:00',
                    'finished' => '2023-01-01T10:02:01+00:00',
                    'resources' => [['id' => 1, 'type' => 'firewall']],
                    'error' => null,
                ],
            ]) ?: ''),
            // Remove from resources
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 4,
                    'command' => 'remove_from_resources',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2023-01-01T10:03:00+00:00',
                    'finished' => '2023-01-01T10:03:01+00:00',
                    'resources' => [
                        ['id' => 1, 'type' => 'firewall'],
                        ['id' => 789, 'type' => 'server'],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
            // Delete firewall
            new Response(200, [], json_encode([]) ?: ''),
        ];

        $requests = [];
        $client = $this->fakeClient($responses, $requests);

        // Complete workflow: create, apply, set rules, remove, delete
        $createResponse = $client->firewalls()->create([
            'name' => 'web-firewall',
            'rules' => [
                [
                    'direction' => 'in',
                    'source_ips' => ['0.0.0.0/0'],
                    'destination_ips' => [],
                    'source_ports' => [],
                    'destination_ports' => ['80', '443'],
                    'protocol' => 'tcp',
                    'action' => 'accept',
                    'description' => 'Allow HTTP/HTTPS',
                ],
            ],
        ]);

        $firewall = $createResponse->firewall();
        $this->assertEquals(1, $firewall->id());

        $applyResponse = $client->firewalls()->actions()->applyToResources('1', [
            'apply_to' => [
                [
                    'type' => 'server',
                    'server' => ['id' => 789],
                ],
            ],
        ]);
        $this->assertEquals('apply_to_resources', $applyResponse->action()['command']);

        $setRulesResponse = $client->firewalls()->actions()->setRules('1', [
            'rules' => [
                [
                    'direction' => 'in',
                    'source_ips' => ['0.0.0.0/0'],
                    'destination_ips' => [],
                    'source_ports' => [],
                    'destination_ports' => ['80', '443'],
                    'protocol' => 'tcp',
                    'action' => 'accept',
                    'description' => 'Allow HTTP/HTTPS',
                ],
            ],
        ]);
        $this->assertEquals('set_rules', $setRulesResponse->action()['command']);

        $removeResponse = $client->firewalls()->actions()->removeFromResources('1', [
            'remove_from' => [
                [
                    'type' => 'server',
                    'server' => ['id' => 789],
                ],
            ],
        ]);
        $this->assertEquals('remove_from_resources', $removeResponse->action()['command']);

        $deleteResponse = $client->firewalls()->delete('1');
        $this->assertInstanceOf(DeleteResponse::class, $deleteResponse);

        // Verify all requests were made
        $this->assertRequestWasMade($requests, 'firewalls', 'create');
        $this->assertRequestWasMade($requests, 'firewall_actions', 'apply_to_resources');
        $this->assertRequestWasMade($requests, 'firewall_actions', 'set_rules');
        $this->assertRequestWasMade($requests, 'firewall_actions', 'remove_from_resources');
        $this->assertRequestWasMade($requests, 'firewalls', 'delete');
    }
}
