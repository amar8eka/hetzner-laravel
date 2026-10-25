<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\LoadBalancerActions\ActionResponse;
use Boci\HetznerLaravel\Responses\LoadBalancerActions\ListActionsResponse;
use Boci\HetznerLaravel\Responses\LoadBalancers\CreateResponse;
use Boci\HetznerLaravel\Responses\LoadBalancers\DeleteResponse;
use Boci\HetznerLaravel\Responses\LoadBalancers\ListResponse;
use Boci\HetznerLaravel\Responses\LoadBalancers\LoadBalancer;
use Boci\HetznerLaravel\Responses\LoadBalancers\LoadBalancerMetrics;
use Boci\HetznerLaravel\Responses\LoadBalancers\MetricsResponse;
use Boci\HetznerLaravel\Responses\LoadBalancers\RetrieveResponse;
use Boci\HetznerLaravel\Responses\LoadBalancers\UpdateResponse;
use Boci\HetznerLaravel\Responses\ServerActions\Action;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Load Balancers Test Suite
 *
 * This test suite covers all functionality related to the Load Balancers resource,
 * including listing, creating, getting, updating, deleting load balancers, and managing load balancer actions.
 */
final class LoadBalancersTest extends TestCase
{
    /**
     * Test listing load balancers with fake data
     */
    public function test_can_list_load_balancers_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->loadBalancers()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertIsArray($response->loadBalancers());
        $this->assertCount(1, $response->loadBalancers());

        $loadBalancer = $response->loadBalancers()[0];
        $this->assertInstanceOf(LoadBalancer::class, $loadBalancer);
        $this->assertEquals(1, $loadBalancer->id());
        $this->assertEquals('test-load-balancer', $loadBalancer->name());
        $this->assertEquals('1.2.3.4', $loadBalancer->publicNet()['ipv4']['ip']);
        $this->assertEquals('2001:db8::1', $loadBalancer->publicNet()['ipv6']['ip']);

        $pagination = $response->pagination();
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(25, $pagination['per_page']);
        $this->assertEquals(1, $pagination['total']);
        $this->assertEquals(1, $pagination['last_page']);
        $this->assertFalse($pagination['has_more_pages']);

        $this->assertRequestWasMade($requests, 'load_balancers', 'list');
    }

    /**
     * Test listing load balancers with custom response
     */
    public function test_can_list_load_balancers_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'load_balancers' => [
                    [
                        'id' => 42,
                        'name' => 'production-lb',
                        'public_net' => [
                            'ipv4' => [
                                'ip' => '5.6.7.8',
                                'dns_ptr' => 'prod-lb.example.com',
                            ],
                            'ipv6' => [
                                'ip' => '2001:db8::2',
                                'dns_ptr' => 'prod-lb.example.com',
                            ],
                        ],
                        'private_net' => [],
                        'location' => [
                            'id' => 1,
                            'name' => 'nbg1',
                            'description' => 'Nuremberg 1',
                            'country' => 'DE',
                            'city' => 'Nuremberg',
                            'latitude' => 49.4521,
                            'longitude' => 11.0767,
                            'network_zone' => 'eu-central',
                        ],
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
                        'algorithm' => [
                            'type' => 'round_robin',
                        ],
                        'included_traffic' => 1000,
                        'ingoing_traffic' => 100,
                        'outgoing_traffic' => 200,
                        'services' => [],
                        'targets' => [],
                        'protection' => [
                            'delete' => false,
                        ],
                        'labels' => [],
                        'created' => '2023-01-01T00:00:00+00:00',
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

        $response = $client->loadBalancers()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->loadBalancers());

        $loadBalancer = $response->loadBalancers()[0];
        $this->assertEquals(42, $loadBalancer->id());
        $this->assertEquals('production-lb', $loadBalancer->name());
        $this->assertEquals('5.6.7.8', $loadBalancer->publicNet()['ipv4']['ip']);
        $this->assertEquals('2001:db8::2', $loadBalancer->publicNet()['ipv6']['ip']);

        $this->assertRequestWasMade($requests, 'load_balancers', 'list');
    }

    /**
     * Test listing load balancers with query parameters
     */
    public function test_can_list_load_balancers_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'production',
            'label_selector' => 'env=prod',
        ];

        $response = $client->loadBalancers()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);

        $this->assertRequestWasMade($requests, 'load_balancers', 'list');
    }

    /**
     * Test creating a load balancer
     */
    public function test_can_create_load_balancer(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'new-load-balancer',
            'load_balancer_type' => 'lb11',
            'location' => 'nbg1',
            'algorithm' => [
                'type' => 'round_robin',
            ],
            'services' => [
                [
                    'protocol' => 'http',
                    'listen_port' => 80,
                    'destination_port' => 80,
                    'proxyprotocol' => false,
                    'health_check' => [
                        'protocol' => 'http',
                        'port' => 80,
                        'path' => '/health',
                        'interval' => 15,
                        'timeout' => 10,
                        'retries' => 3,
                    ],
                ],
            ],
            'targets' => [
                [
                    'type' => 'server',
                    'server' => [
                        'id' => 1,
                    ],
                    'use_private_ip' => false,
                ],
            ],
            'labels' => [
                'env' => 'production',
            ],
        ];

        $response = $client->loadBalancers()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $this->assertInstanceOf(LoadBalancer::class, $response->loadBalancer());
        $this->assertInstanceOf(Action::class, $response->action());

        $loadBalancer = $response->loadBalancer();
        $this->assertEquals(1, $loadBalancer->id());
        $this->assertEquals('new-load-balancer', $loadBalancer->name());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('create_load_balancer', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'load_balancers', 'create');
    }

    /**
     * Test getting a specific load balancer
     */
    public function test_can_get_specific_load_balancer(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $response = $client->loadBalancers()->retrieve($loadBalancerId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $this->assertInstanceOf(LoadBalancer::class, $response->loadBalancer());

        $loadBalancer = $response->loadBalancer();
        $this->assertEquals(42, $loadBalancer->id());
        $this->assertEquals('test-load-balancer', $loadBalancer->name());

        $this->assertRequestWasMade($requests, 'load_balancers', 'get');
    }

    /**
     * Test updating a load balancer
     */
    public function test_can_update_load_balancer(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $parameters = [
            'name' => 'updated-load-balancer',
            'labels' => [
                'env' => 'staging',
                'version' => '2.0',
            ],
        ];

        $response = $client->loadBalancers()->update($loadBalancerId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);
        $this->assertInstanceOf(LoadBalancer::class, $response->loadBalancer());

        $loadBalancer = $response->loadBalancer();
        $this->assertEquals(42, $loadBalancer->id());
        $this->assertEquals('updated-load-balancer', $loadBalancer->name());

        $this->assertRequestWasMade($requests, 'load_balancers', 'update');
    }

    /**
     * Test deleting a load balancer
     */
    public function test_can_delete_load_balancer(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $response = $client->loadBalancers()->delete($loadBalancerId);

        $this->assertInstanceOf(DeleteResponse::class, $response);
        $this->assertInstanceOf(Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('delete_load_balancer', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'load_balancers', 'delete');
    }

    /**
     * Test getting load balancer metrics
     */
    public function test_can_get_load_balancer_metrics(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $parameters = [
            'type' => 'connections_per_second,requests_per_second',
            'start' => '2023-01-01T00:00:00+00:00',
            'end' => '2023-01-01T01:00:00+00:00',
        ];

        $response = $client->loadBalancers()->metrics($loadBalancerId, $parameters);

        $this->assertInstanceOf(MetricsResponse::class, $response);
        $this->assertInstanceOf(LoadBalancerMetrics::class, $response->metrics());

        $metrics = $response->metrics();
        $this->assertIsArray($metrics->toArray());
        $this->assertArrayHasKey('start', $metrics->toArray());
        $this->assertArrayHasKey('end', $metrics->toArray());
        $this->assertArrayHasKey('step', $metrics->toArray());
        $this->assertArrayHasKey('time_series', $metrics->toArray());

        $this->assertRequestWasMade($requests, 'load_balancers', 'metrics');
    }

    /**
     * Test load balancer response structure
     */
    public function test_load_balancer_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->loadBalancers()->list();
        $loadBalancer = $response->loadBalancers()[0];

        // Test all load balancer properties
        $this->assertIsInt($loadBalancer->id());
        $this->assertIsString($loadBalancer->name());
        $this->assertIsArray($loadBalancer->publicNet());
        $this->assertIsArray($loadBalancer->privateNet());
        $this->assertIsArray($loadBalancer->location());
        $this->assertIsArray($loadBalancer->loadBalancerType());
        $this->assertIsArray($loadBalancer->algorithm());
        $this->assertIsInt($loadBalancer->includedTraffic());
        $this->assertIsInt($loadBalancer->ingoingTraffic());
        $this->assertIsInt($loadBalancer->outgoingTraffic());
        $this->assertIsArray($loadBalancer->services());
        $this->assertIsArray($loadBalancer->targets());
        $this->assertIsArray($loadBalancer->protection());
        $this->assertIsArray($loadBalancer->labels());
        $this->assertIsString($loadBalancer->created());

        // Test algorithm types
        $this->assertContains($loadBalancer->algorithm()['type'], ['round_robin', 'least_connections']);

        // Test toArray method
        $loadBalancerArray = $loadBalancer->toArray();
        $this->assertIsArray($loadBalancerArray);
        $this->assertArrayHasKey('id', $loadBalancerArray);
        $this->assertArrayHasKey('name', $loadBalancerArray);
        $this->assertArrayHasKey('public_net', $loadBalancerArray);
        $this->assertArrayHasKey('location', $loadBalancerArray);
    }

    /**
     * Test handling load balancer API exception
     */
    public function test_can_handle_load_balancer_api_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Load balancer not found', new Request('GET', '/load_balancers/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Load balancer not found');

        $client->loadBalancers()->retrieve('999');
    }

    /**
     * Test handling load balancer list exception
     */
    public function test_can_handle_load_balancer_list_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Rate limit exceeded', new Request('GET', '/load_balancers')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->loadBalancers()->list();
    }

    /**
     * Test handling mixed load balancer response types
     */
    public function test_can_handle_mixed_load_balancer_response_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'load_balancers' => [
                    [
                        'id' => 1,
                        'name' => 'test-lb',
                        'public_net' => [
                            'ipv4' => [
                                'ip' => '1.2.3.4',
                                'dns_ptr' => 'lb.example.com',
                            ],
                            'ipv6' => [
                                'ip' => '2001:db8::1',
                                'dns_ptr' => 'lb.example.com',
                            ],
                        ],
                        'private_net' => [],
                        'location' => [
                            'id' => 1,
                            'name' => 'nbg1',
                            'description' => 'Nuremberg 1',
                            'country' => 'DE',
                            'city' => 'Nuremberg',
                            'latitude' => 49.4521,
                            'longitude' => 11.0767,
                            'network_zone' => 'eu-central',
                        ],
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
                        'algorithm' => [
                            'type' => 'round_robin',
                        ],
                        'included_traffic' => 1000,
                        'ingoing_traffic' => 100,
                        'outgoing_traffic' => 200,
                        'services' => [],
                        'targets' => [],
                        'protection' => [
                            'delete' => false,
                        ],
                        'labels' => [],
                        'created' => '2023-01-01T00:00:00+00:00',
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
            new RequestException('Load balancer not found', new Request('GET', '/load_balancers/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $listResponse = $client->loadBalancers()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->loadBalancers());

        // Second call should throw exception
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Load balancer not found');
        $client->loadBalancers()->retrieve('999');
    }

    /**
     * Test using individual load balancer fake
     */
    public function test_using_individual_load_balancer_fake(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancersFake = $client->loadBalancers();

        $response = $loadBalancersFake->list(['page' => 1, 'per_page' => 5]);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->loadBalancers());

        $loadBalancersFake->assertSent(function (array $request) {
            return $request['resource'] === 'load_balancers' &&
                   $request['method'] === 'list' &&
                   isset($request['parameters']['page']) &&
                   $request['parameters']['page'] === 1 &&
                   isset($request['parameters']['per_page']) &&
                   $request['parameters']['per_page'] === 5;
        });
    }

    /**
     * Test load balancer fake assert not sent
     */
    public function test_load_balancer_fake_assert_not_sent(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancersFake = $client->loadBalancers();

        // No requests made yet
        $loadBalancersFake->assertNotSent();

        // Make a request
        $loadBalancersFake->list();

        // Now it should fail
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Requests were sent to load_balancers.');
        $loadBalancersFake->assertNotSent();
    }

    /**
     * Test load balancer pagination structure
     */
    public function test_load_balancer_pagination_structure(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'load_balancers' => [
                    [
                        'id' => 1,
                        'name' => 'test-lb',
                        'public_net' => [
                            'ipv4' => [
                                'ip' => '1.2.3.4',
                                'dns_ptr' => 'lb.example.com',
                            ],
                            'ipv6' => [
                                'ip' => '2001:db8::1',
                                'dns_ptr' => 'lb.example.com',
                            ],
                        ],
                        'private_net' => [],
                        'location' => [
                            'id' => 1,
                            'name' => 'nbg1',
                            'description' => 'Nuremberg 1',
                            'country' => 'DE',
                            'city' => 'Nuremberg',
                            'latitude' => 49.4521,
                            'longitude' => 11.0767,
                            'network_zone' => 'eu-central',
                        ],
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
                        'algorithm' => [
                            'type' => 'round_robin',
                        ],
                        'included_traffic' => 1000,
                        'ingoing_traffic' => 100,
                        'outgoing_traffic' => 200,
                        'services' => [],
                        'targets' => [],
                        'protection' => [
                            'delete' => false,
                        ],
                        'labels' => [],
                        'created' => '2023-01-01T00:00:00+00:00',
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

        $response = $client->loadBalancers()->list();
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
     * Test load balancer workflow simulation
     */
    public function test_load_balancer_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List load balancers response
            new Response(200, [], json_encode([
                'load_balancers' => [
                    [
                        'id' => 1,
                        'name' => 'test-load-balancer',
                        'public_net' => [
                            'ipv4' => [
                                'ip' => '1.2.3.4',
                                'dns_ptr' => 'lb.example.com',
                            ],
                            'ipv6' => [
                                'ip' => '2001:db8::1',
                                'dns_ptr' => 'lb.example.com',
                            ],
                        ],
                        'private_net' => [],
                        'location' => [
                            'id' => 1,
                            'name' => 'nbg1',
                            'description' => 'Nuremberg 1',
                            'country' => 'DE',
                            'city' => 'Nuremberg',
                            'latitude' => 49.4521,
                            'longitude' => 11.0767,
                            'network_zone' => 'eu-central',
                        ],
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
                        'algorithm' => [
                            'type' => 'round_robin',
                        ],
                        'included_traffic' => 1000,
                        'ingoing_traffic' => 100,
                        'outgoing_traffic' => 200,
                        'services' => [],
                        'targets' => [],
                        'protection' => [
                            'delete' => false,
                        ],
                        'labels' => [],
                        'created' => '2023-01-01T00:00:00+00:00',
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
            // Get specific load balancer response
            new Response(200, [], json_encode([
                'load_balancer' => [
                    'id' => 1,
                    'name' => 'test-load-balancer',
                    'public_net' => [
                        'ipv4' => [
                            'ip' => '1.2.3.4',
                            'dns_ptr' => 'lb.example.com',
                        ],
                        'ipv6' => [
                            'ip' => '2001:db8::1',
                            'dns_ptr' => 'lb.example.com',
                        ],
                    ],
                    'private_net' => [],
                    'location' => [
                        'id' => 1,
                        'name' => 'nbg1',
                        'description' => 'Nuremberg 1',
                        'country' => 'DE',
                        'city' => 'Nuremberg',
                        'latitude' => 49.4521,
                        'longitude' => 11.0767,
                        'network_zone' => 'eu-central',
                    ],
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
                    'algorithm' => [
                        'type' => 'round_robin',
                    ],
                    'included_traffic' => 1000,
                    'ingoing_traffic' => 100,
                    'outgoing_traffic' => 200,
                    'services' => [],
                    'targets' => [],
                    'protection' => [
                        'delete' => false,
                    ],
                    'labels' => [],
                    'created' => '2023-01-01T00:00:00+00:00',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list load balancers, then get details of the first one
        $listResponse = $client->loadBalancers()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->loadBalancers());

        $firstLoadBalancer = $listResponse->loadBalancers()[0];
        $loadBalancerId = (string) $firstLoadBalancer->id();

        $getResponse = $client->loadBalancers()->retrieve($loadBalancerId);
        $this->assertInstanceOf(RetrieveResponse::class, $getResponse);
        $this->assertEquals($firstLoadBalancer->id(), $getResponse->loadBalancer()->id());
        $this->assertEquals($firstLoadBalancer->name(), $getResponse->loadBalancer()->name());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'load_balancers', 'list');
        $this->assertRequestWasMade($requests, 'load_balancers', 'get');
    }

    /**
     * Test load balancer error response handling
     */
    public function test_load_balancer_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Load balancer not found', new Request('GET', '/load_balancers/nonexistent')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Load balancer not found');
        $client->loadBalancers()->retrieve('nonexistent');
    }

    /**
     * Test load balancer empty list response
     */
    public function test_load_balancer_empty_list_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'load_balancers' => [],
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

        $response = $client->loadBalancers()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(0, $response->loadBalancers());
        $this->assertEmpty($response->loadBalancers());

        $pagination = $response->pagination();
        $this->assertEquals(0, $pagination['total']);
        $this->assertEquals(0, $pagination['to']);
    }

    /**
     * Test load balancer actions - list actions
     */
    public function test_can_list_load_balancer_actions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $response = $client->loadBalancers()->actions()->list($loadBalancerId);

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertIsArray($response->actions());
        $this->assertCount(2, $response->actions());

        $actions = $response->actions();
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\LoadBalancerActions\Action::class, $actions[0]);
        $this->assertEquals(1, $actions[0]->id());
        $this->assertEquals('add_service', $actions[0]->command());
        $this->assertEquals('success', $actions[0]->status());

        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\LoadBalancerActions\Action::class, $actions[1]);
        $this->assertEquals(2, $actions[1]->id());
        $this->assertEquals('add_target', $actions[1]->command());
        $this->assertEquals('running', $actions[1]->status());

        $this->assertRequestWasMade($requests, 'load_balancer_actions', 'list');
    }

    /**
     * Test load balancer actions - get specific action
     */
    public function test_can_get_load_balancer_action(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $actionId = '123';
        $response = $client->loadBalancers()->actions()->retrieve($loadBalancerId, $actionId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\LoadBalancerActions\Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(123, $action->id());
        $this->assertEquals('get_action', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'load_balancer_actions', 'get');
    }

    /**
     * Test load balancer actions - add service
     */
    public function test_can_add_load_balancer_service(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $parameters = [
            'protocol' => 'http',
            'listen_port' => 80,
            'destination_port' => 80,
            'proxyprotocol' => false,
            'health_check' => [
                'protocol' => 'http',
                'port' => 80,
                'path' => '/health',
                'interval' => 15,
                'timeout' => 10,
                'retries' => 3,
            ],
        ];

        $response = $client->loadBalancers()->actions()->addService($loadBalancerId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\LoadBalancerActions\Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('add_service', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'load_balancer_actions', 'add_service');
    }

    /**
     * Test load balancer actions - add target
     */
    public function test_can_add_load_balancer_tarretrieve(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $parameters = [
            'type' => 'server',
            'server' => [
                'id' => 1,
            ],
            'use_private_ip' => false,
        ];

        $response = $client->loadBalancers()->actions()->addTarretrieve($loadBalancerId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\LoadBalancerActions\Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('add_target', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'load_balancer_actions', 'add_target');
    }

    /**
     * Test load balancer actions - change algorithm
     */
    public function test_can_change_load_balancer_algorithm(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $parameters = [
            'type' => 'least_connections',
        ];

        $response = $client->loadBalancers()->actions()->changeAlgorithm($loadBalancerId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\LoadBalancerActions\Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('change_algorithm', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'load_balancer_actions', 'change_algorithm');
    }

    /**
     * Test load balancer actions - change protection
     */
    public function test_can_change_load_balancer_protection(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $parameters = [
            'delete' => true,
        ];

        $response = $client->loadBalancers()->actions()->changeProtection($loadBalancerId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\LoadBalancerActions\Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('change_protection', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'load_balancer_actions', 'change_protection');
    }

    /**
     * Test load balancer actions - attach to network
     */
    public function test_can_attach_load_balancer_to_network(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $parameters = [
            'network' => 1,
            'ip' => '10.0.0.1',
        ];

        $response = $client->loadBalancers()->actions()->attachToNetwork($loadBalancerId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\LoadBalancerActions\Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('attach_to_network', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'load_balancer_actions', 'attach_to_network');
    }

    /**
     * Test load balancer actions - enable public interface
     */
    public function test_can_enable_load_balancer_public_interface(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $response = $client->loadBalancers()->actions()->enablePublicInterface($loadBalancerId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\LoadBalancerActions\Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('enable_public_interface', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'load_balancer_actions', 'enable_public_interface');
    }

    /**
     * Test load balancer actions - disable public interface
     */
    public function test_can_disable_load_balancer_public_interface(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $loadBalancerId = '42';
        $response = $client->loadBalancers()->actions()->disablePublicInterface($loadBalancerId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\LoadBalancerActions\Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('disable_public_interface', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'load_balancer_actions', 'disable_public_interface');
    }
}
