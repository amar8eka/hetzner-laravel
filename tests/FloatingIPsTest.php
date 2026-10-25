<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\FloatingIPActions\ActionResponse;
use Boci\HetznerLaravel\Responses\FloatingIPActions\ListActionsResponse;
use Boci\HetznerLaravel\Responses\FloatingIPs\CreateResponse;
use Boci\HetznerLaravel\Responses\FloatingIPs\DeleteResponse;
use Boci\HetznerLaravel\Responses\FloatingIPs\FloatingIP;
use Boci\HetznerLaravel\Responses\FloatingIPs\ListResponse;
use Boci\HetznerLaravel\Responses\FloatingIPs\RetrieveResponse;
use Boci\HetznerLaravel\Responses\FloatingIPs\UpdateResponse;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Floating IPs Test Suite
 *
 * This test suite covers all functionality related to the Floating IPs resource,
 * including listing, creating, getting, updating, deleting floating IPs and their actions.
 */
final class FloatingIPsTest extends TestCase
{
    /**
     * Test listing floating IPs with fake data
     */
    public function test_can_list_floating_ips_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->floatingIPs()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertIsArray($response->floatingIps());
        $this->assertCount(1, $response->floatingIps());

        $floatingIps = $response->floatingIps();
        $this->assertInstanceOf(FloatingIP::class, $floatingIps[0]);
        $this->assertEquals(1, $floatingIps[0]->id());
        $this->assertEquals('test-floating-ip-1', $floatingIps[0]->name());
        $this->assertEquals('Test Floating IP', $floatingIps[0]->description());
        $this->assertEquals('1.2.3.4', $floatingIps[0]->ip());
        $this->assertEquals('ipv4', $floatingIps[0]->type());
        $this->assertEquals(1, $floatingIps[0]->server());
        $this->assertFalse($floatingIps[0]->blocked());
        $this->assertIsArray($floatingIps[0]->dnsPtr());
        $this->assertIsArray($floatingIps[0]->homeLocation());
        $this->assertIsArray($floatingIps[0]->protection());
        $this->assertIsArray($floatingIps[0]->labels());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $floatingIps[0]->created());

        $this->assertRequestWasMade($requests, 'floating_ips', 'list');
    }

    /**
     * Test listing floating IPs with custom response
     */
    public function test_can_list_floating_ips_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'floating_ips' => [
                    [
                        'id' => 42,
                        'name' => 'custom-floating-ip',
                        'description' => 'Custom Floating IP',
                        'ip' => '192.168.1.100',
                        'type' => 'ipv4',
                        'server' => 123,
                        'dns_ptr' => [
                            [
                                'ip' => '192.168.1.100',
                                'dns_ptr' => 'custom.example.com',
                            ],
                        ],
                        'home_location' => [
                            'id' => 1,
                            'name' => 'fsn1',
                            'description' => 'Falkenstein DC Park 1',
                            'country' => 'DE',
                            'city' => 'Falkenstein',
                            'latitude' => 50.4762,
                            'longitude' => 12.3707,
                            'network_zone' => 'eu-central',
                        ],
                        'blocked' => false,
                        'protection' => [
                            'delete' => true,
                        ],
                        'labels' => [
                            'environment' => 'production',
                            'team' => 'infrastructure',
                        ],
                        'created' => '2023-06-15T10:30:00+00:00',
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

        $response = $client->floatingIPs()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->floatingIps());

        $floatingIp = $response->floatingIps()[0];
        $this->assertEquals(42, $floatingIp->id());
        $this->assertEquals('custom-floating-ip', $floatingIp->name());
        $this->assertEquals('Custom Floating IP', $floatingIp->description());
        $this->assertEquals('192.168.1.100', $floatingIp->ip());
        $this->assertEquals('ipv4', $floatingIp->type());
        $this->assertEquals(123, $floatingIp->server());
        $this->assertEquals('production', $floatingIp->labels()['environment']);
        $this->assertEquals('infrastructure', $floatingIp->labels()['team']);

        $this->assertRequestWasMade($requests, 'floating_ips', 'list');
    }

    /**
     * Test listing floating IPs with query parameters
     */
    public function test_can_list_floating_ips_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'my-floating-ip',
        ];

        $response = $client->floatingIPs()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);

        $this->assertRequestWasMade($requests, 'floating_ips', 'list');
    }

    /**
     * Test creating a floating IP
     */
    public function test_can_create_floating_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'new-floating-ip',
            'type' => 'ipv4',
            'home_location' => 'nbg1',
            'labels' => [
                'environment' => 'production',
                'team' => 'backend',
            ],
        ];

        $response = $client->floatingIPs()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $this->assertInstanceOf(FloatingIP::class, $response->floatingIp());
        $this->assertIsArray($response->action());

        $floatingIp = $response->floatingIp();
        $this->assertEquals(1, $floatingIp->id());
        $this->assertEquals('new-floating-ip', $floatingIp->name());
        $this->assertEquals('Test Floating IP', $floatingIp->description());
        $this->assertEquals('1.2.3.4', $floatingIp->ip());
        $this->assertEquals('ipv4', $floatingIp->type());
        $this->assertNull($floatingIp->server());

        $action = $response->action();
        $this->assertEquals(1, $action['id']);
        $this->assertEquals('create_floating_ip', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'floating_ips', 'create');
    }

    /**
     * Test creating floating IP with custom response
     */
    public function test_can_create_floating_ip_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'floating_ip' => [
                    'id' => 99,
                    'name' => 'custom-new-floating-ip',
                    'description' => 'Custom New Floating IP',
                    'ip' => '10.0.0.1',
                    'type' => 'ipv4',
                    'server' => null,
                    'dns_ptr' => [
                        [
                            'ip' => '10.0.0.1',
                            'dns_ptr' => 'new-floating-ip.example.com',
                        ],
                    ],
                    'home_location' => [
                        'id' => 1,
                        'name' => 'fsn1',
                        'description' => 'Falkenstein DC Park 1',
                        'country' => 'DE',
                        'city' => 'Falkenstein',
                        'latitude' => 50.4762,
                        'longitude' => 12.3707,
                        'network_zone' => 'eu-central',
                    ],
                    'blocked' => false,
                    'protection' => [
                        'delete' => false,
                    ],
                    'labels' => [
                        'environment' => 'development',
                        'team' => 'frontend',
                    ],
                    'created' => '2023-06-20T09:15:00+00:00',
                ],
                'action' => [
                    'id' => 99,
                    'command' => 'create_floating_ip',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2023-06-20T09:15:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 99,
                            'type' => 'floating_ip',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'custom-new-floating-ip',
            'type' => 'ipv4',
            'home_location' => 'fsn1',
        ];

        $response = $client->floatingIPs()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $floatingIp = $response->floatingIp();

        $this->assertEquals(99, $floatingIp->id());
        $this->assertEquals('custom-new-floating-ip', $floatingIp->name());
        $this->assertEquals('Custom New Floating IP', $floatingIp->description());
        $this->assertEquals('10.0.0.1', $floatingIp->ip());
        $this->assertEquals('ipv4', $floatingIp->type());
        $this->assertEquals('development', $floatingIp->labels()['environment']);
        $this->assertEquals('frontend', $floatingIp->labels()['team']);

        $this->assertRequestWasMade($requests, 'floating_ips', 'create');
    }

    /**
     * Test getting a specific floating IP
     */
    public function test_can_get_specific_floating_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $floatingIpId = '42';
        $response = $client->floatingIPs()->retrieve($floatingIpId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $this->assertInstanceOf(FloatingIP::class, $response->floatingIp());

        $floatingIp = $response->floatingIp();
        $this->assertEquals(42, $floatingIp->id());
        $this->assertEquals('test-floating-ip', $floatingIp->name());
        $this->assertEquals('Test Floating IP', $floatingIp->description());
        $this->assertEquals('1.2.3.4', $floatingIp->ip());
        $this->assertEquals('ipv4', $floatingIp->type());
        $this->assertNull($floatingIp->server());
        $this->assertFalse($floatingIp->blocked());
        $this->assertIsArray($floatingIp->dnsPtr());
        $this->assertIsArray($floatingIp->homeLocation());
        $this->assertIsArray($floatingIp->protection());
        $this->assertIsArray($floatingIp->labels());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $floatingIp->created());

        $this->assertRequestWasMade($requests, 'floating_ips', 'get');
    }

    /**
     * Test getting floating IP with custom response
     */
    public function test_can_get_floating_ip_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'floating_ip' => [
                    'id' => 123,
                    'name' => 'detailed-floating-ip',
                    'description' => 'Detailed Floating IP',
                    'ip' => '172.16.0.1',
                    'type' => 'ipv4',
                    'server' => 456,
                    'dns_ptr' => [
                        [
                            'ip' => '172.16.0.1',
                            'dns_ptr' => 'detailed.example.com',
                        ],
                    ],
                    'home_location' => [
                        'id' => 1,
                        'name' => 'fsn1',
                        'description' => 'Falkenstein DC Park 1',
                        'country' => 'DE',
                        'city' => 'Falkenstein',
                        'latitude' => 50.4762,
                        'longitude' => 12.3707,
                        'network_zone' => 'eu-central',
                    ],
                    'blocked' => false,
                    'protection' => [
                        'delete' => true,
                    ],
                    'labels' => [
                        'environment' => 'production',
                        'team' => 'infrastructure',
                        'project' => 'web-app',
                    ],
                    'created' => '2023-05-10T16:20:00+00:00',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $floatingIpId = '123';
        $response = $client->floatingIPs()->retrieve($floatingIpId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $floatingIp = $response->floatingIp();

        $this->assertEquals(123, $floatingIp->id());
        $this->assertEquals('detailed-floating-ip', $floatingIp->name());
        $this->assertEquals('Detailed Floating IP', $floatingIp->description());
        $this->assertEquals('172.16.0.1', $floatingIp->ip());
        $this->assertEquals('ipv4', $floatingIp->type());
        $this->assertEquals(456, $floatingIp->server());
        $this->assertEquals('production', $floatingIp->labels()['environment']);
        $this->assertEquals('infrastructure', $floatingIp->labels()['team']);
        $this->assertEquals('web-app', $floatingIp->labels()['project']);

        $this->assertRequestWasMade($requests, 'floating_ips', 'get');
    }

    /**
     * Test updating a floating IP
     */
    public function test_can_update_floating_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $floatingIpId = '42';
        $parameters = [
            'name' => 'updated-floating-ip',
            'description' => 'Updated Floating IP',
            'labels' => [
                'environment' => 'staging',
                'team' => 'frontend',
            ],
        ];

        $response = $client->floatingIPs()->update($floatingIpId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);
        $this->assertInstanceOf(FloatingIP::class, $response->floatingIp());

        $floatingIp = $response->floatingIp();
        $this->assertEquals(42, $floatingIp->id());
        $this->assertEquals('updated-floating-ip', $floatingIp->name());
        $this->assertEquals('Updated Floating IP', $floatingIp->description());
        $this->assertEquals('staging', $floatingIp->labels()['environment']);
        $this->assertEquals('frontend', $floatingIp->labels()['team']);

        $this->assertRequestWasMade($requests, 'floating_ips', 'update');
    }

    /**
     * Test deleting a floating IP
     */
    public function test_can_delete_floating_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $floatingIpId = '42';
        $response = $client->floatingIPs()->delete($floatingIpId);

        $this->assertInstanceOf(DeleteResponse::class, $response);

        $this->assertRequestWasMade($requests, 'floating_ips', 'delete');
    }

    /**
     * Test floating IP response structure
     */
    public function test_floating_ip_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->floatingIPs()->list();
        $floatingIps = $response->floatingIps();

        foreach ($floatingIps as $floatingIp) {
            // Test all floating IP properties
            $this->assertIsInt($floatingIp->id());
            $this->assertIsString($floatingIp->name());
            $this->assertIsString($floatingIp->description());
            $this->assertIsString($floatingIp->ip());
            $this->assertIsString($floatingIp->type());
            $this->assertIsBool($floatingIp->blocked());
            $this->assertIsArray($floatingIp->dnsPtr());
            $this->assertIsArray($floatingIp->homeLocation());
            $this->assertIsArray($floatingIp->protection());
            $this->assertIsArray($floatingIp->labels());
            $this->assertIsString($floatingIp->created());

            // Test floating IP type values
            $this->assertContains($floatingIp->type(), ['ipv4', 'ipv6']);

            // Test IP address format
            $this->assertMatchesRegularExpression('/^(\d{1,3}\.){3}\d{1,3}$/', $floatingIp->ip());

            // Test home location structure
            $homeLocation = $floatingIp->homeLocation();
            $this->assertArrayHasKey('id', $homeLocation);
            $this->assertArrayHasKey('name', $homeLocation);
            $this->assertArrayHasKey('description', $homeLocation);
            $this->assertArrayHasKey('country', $homeLocation);
            $this->assertArrayHasKey('city', $homeLocation);
            $this->assertArrayHasKey('latitude', $homeLocation);
            $this->assertArrayHasKey('longitude', $homeLocation);
            $this->assertArrayHasKey('network_zone', $homeLocation);

            // Test protection structure
            $protection = $floatingIp->protection();
            $this->assertArrayHasKey('delete', $protection);
            $this->assertIsBool($protection['delete']);

            // Test labels structure
            foreach ($floatingIp->labels() as $key => $value) {
                $this->assertIsString($key);
                $this->assertIsString($value);
            }

            // Test created date format
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/', $floatingIp->created());
        }
    }

    /**
     * Test handling floating IP API exception
     */
    public function test_can_handle_floating_ip_api_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Floating IP not found', new Request('GET', '/floating_ips/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Floating IP not found');

        $client->floatingIPs()->retrieve('999');
    }

    /**
     * Test handling floating IP list exception
     */
    public function test_can_handle_floating_ip_list_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Rate limit exceeded', new Request('GET', '/floating_ips')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->floatingIPs()->list();
    }

    /**
     * Test handling mixed floating IP response types
     */
    public function test_can_handle_mixed_floating_ip_response_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'floating_ips' => [
                    [
                        'id' => 1,
                        'name' => 'my-floating-ip',
                        'description' => 'My Floating IP',
                        'ip' => '1.2.3.4',
                        'type' => 'ipv4',
                        'server' => null,
                        'dns_ptr' => [
                            [
                                'ip' => '1.2.3.4',
                                'dns_ptr' => 'floating-ip.example.com',
                            ],
                        ],
                        'home_location' => [
                            'id' => 1,
                            'name' => 'nbg1',
                            'description' => 'Nuremberg',
                            'country' => 'DE',
                            'city' => 'Nuremberg',
                            'latitude' => 49.4521,
                            'longitude' => 11.0767,
                            'network_zone' => 'eu-central',
                        ],
                        'blocked' => false,
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
            new RequestException('Floating IP not found', new Request('GET', '/floating_ips/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $listResponse = $client->floatingIPs()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->floatingIps());

        // Second call should throw exception
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Floating IP not found');
        $client->floatingIPs()->retrieve('999');
    }

    /**
     * Test using individual floating IP fake
     */
    public function test_using_individual_floating_ip_fake(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $floatingIPsFake = $client->floatingIPs();

        $response = $floatingIPsFake->list(['page' => 1, 'per_page' => 5]);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->floatingIps());

        $floatingIPsFake->assertSent(function (array $request) {
            return $request['resource'] === 'floating_ips' &&
                   $request['method'] === 'list' &&
                   isset($request['parameters']['page']) &&
                   $request['parameters']['page'] === 1 &&
                   isset($request['parameters']['per_page']) &&
                   $request['parameters']['per_page'] === 5;
        });
    }

    /**
     * Test floating IP fake assert not sent
     */
    public function test_floating_ip_fake_assert_not_sent(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $floatingIPsFake = $client->floatingIPs();

        // No requests made yet
        $floatingIPsFake->assertNotSent();

        // Make a request
        $floatingIPsFake->list();

        // Now it should fail
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Requests were sent to floating_ips.');
        $floatingIPsFake->assertNotSent();
    }

    /**
     * Test floating IP workflow simulation
     */
    public function test_floating_ip_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List floating IPs response
            new Response(200, [], json_encode([
                'floating_ips' => [
                    [
                        'id' => 1,
                        'name' => 'my-floating-ip',
                        'description' => 'My Floating IP',
                        'ip' => '1.2.3.4',
                        'type' => 'ipv4',
                        'server' => null,
                        'dns_ptr' => [
                            [
                                'ip' => '1.2.3.4',
                                'dns_ptr' => 'floating-ip.example.com',
                            ],
                        ],
                        'home_location' => [
                            'id' => 1,
                            'name' => 'nbg1',
                            'description' => 'Nuremberg',
                            'country' => 'DE',
                            'city' => 'Nuremberg',
                            'latitude' => 49.4521,
                            'longitude' => 11.0767,
                            'network_zone' => 'eu-central',
                        ],
                        'blocked' => false,
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
            // Get specific floating IP response
            new Response(200, [], json_encode([
                'floating_ip' => [
                    'id' => 1,
                    'name' => 'my-floating-ip',
                    'description' => 'My Floating IP',
                    'ip' => '1.2.3.4',
                    'type' => 'ipv4',
                    'server' => null,
                    'dns_ptr' => [
                        [
                            'ip' => '1.2.3.4',
                            'dns_ptr' => 'floating-ip.example.com',
                        ],
                    ],
                    'home_location' => [
                        'id' => 1,
                        'name' => 'nbg1',
                        'description' => 'Nuremberg',
                        'country' => 'DE',
                        'city' => 'Nuremberg',
                        'latitude' => 49.4521,
                        'longitude' => 11.0767,
                        'network_zone' => 'eu-central',
                    ],
                    'blocked' => false,
                    'protection' => [
                        'delete' => false,
                    ],
                    'labels' => [],
                    'created' => '2023-01-01T00:00:00+00:00',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list floating IPs, then get details of the first one
        $listResponse = $client->floatingIPs()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->floatingIps());

        $firstFloatingIp = $listResponse->floatingIps()[0];
        $floatingIpId = (string) $firstFloatingIp->id();

        $getResponse = $client->floatingIPs()->retrieve($floatingIpId);
        $this->assertInstanceOf(RetrieveResponse::class, $getResponse);
        $this->assertEquals($firstFloatingIp->id(), $getResponse->floatingIp()->id());
        $this->assertEquals($firstFloatingIp->name(), $getResponse->floatingIp()->name());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'floating_ips', 'list');
        $this->assertRequestWasMade($requests, 'floating_ips', 'get');
    }

    /**
     * Test floating IP error response handling
     */
    public function test_floating_ip_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Floating IP not found', new Request('GET', '/floating_ips/nonexistent')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Floating IP not found');
        $client->floatingIPs()->retrieve('nonexistent');
    }

    /**
     * Test floating IP empty list response
     */
    public function test_floating_ip_empty_list_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'floating_ips' => [],
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

        $response = $client->floatingIPs()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(0, $response->floatingIps());
        $this->assertEmpty($response->floatingIps());
    }

    /**
     * Test floating IP pagination
     */
    public function test_floating_ip_pagination(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->floatingIPs()->list();
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
        $this->assertEquals(1, $pagination['total']);
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
     * Test floating IP to array conversion
     */
    public function test_floating_ip_to_array_conversion(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->floatingIPs()->list();
        $floatingIps = $response->floatingIps();

        foreach ($floatingIps as $floatingIp) {
            $array = $floatingIp->toArray();

            $this->assertIsArray($array);
            $this->assertArrayHasKey('id', $array);
            $this->assertArrayHasKey('name', $array);
            $this->assertArrayHasKey('description', $array);
            $this->assertArrayHasKey('ip', $array);
            $this->assertArrayHasKey('type', $array);
            $this->assertArrayHasKey('server', $array);
            $this->assertArrayHasKey('dns_ptr', $array);
            $this->assertArrayHasKey('home_location', $array);
            $this->assertArrayHasKey('blocked', $array);
            $this->assertArrayHasKey('protection', $array);
            $this->assertArrayHasKey('labels', $array);
            $this->assertArrayHasKey('created', $array);

            $this->assertEquals($floatingIp->id(), $array['id']);
            $this->assertEquals($floatingIp->name(), $array['name']);
            $this->assertEquals($floatingIp->description(), $array['description']);
            $this->assertEquals($floatingIp->ip(), $array['ip']);
            $this->assertEquals($floatingIp->type(), $array['type']);
        }
    }

    /**
     * Test floating IP actions - list actions
     */
    public function test_can_list_floating_ip_actions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $floatingIpId = '42';
        $response = $client->floatingIPs()->actions()->list($floatingIpId);

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertIsArray($response->actions());
        $this->assertCount(2, $response->actions());

        $actions = $response->actions();
        /** @var array<int, array<string, mixed>> $actions */
        $this->assertIsArray($actions[0]);
        $this->assertEquals(1, $actions[0]['id']);
        $this->assertEquals('assign_floating_ip', $actions[0]['command']);
        $this->assertEquals('success', $actions[0]['status']);
        $this->assertEquals(100, $actions[0]['progress']);

        $this->assertIsArray($actions[1]);
        $this->assertEquals(2, $actions[1]['id']);
        $this->assertEquals('change_reverse_dns', $actions[1]['command']);
        $this->assertEquals('running', $actions[1]['status']);
        $this->assertEquals(50, $actions[1]['progress']);

        $this->assertRequestWasMade($requests, 'floating_ip_actions', 'list');
    }

    /**
     * Test floating IP actions - get action
     */
    public function test_can_get_floating_ip_action(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $floatingIpId = '42';
        $actionId = '123';
        $response = $client->floatingIPs()->actions()->retrieve($floatingIpId, $actionId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertIsArray($response->action());

        $action = $response->action();
        $this->assertEquals(123, $action['id']);
        $this->assertEquals('assign_floating_ip', $action['command']);
        $this->assertEquals('running', $action['status']);
        $this->assertEquals(0, $action['progress']);

        $this->assertRequestWasMade($requests, 'floating_ip_actions', 'get');
    }

    /**
     * Test floating IP actions - assign floating IP
     */
    public function test_can_assign_floating_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $floatingIpId = '42';
        $parameters = [
            'server' => 123,
        ];

        $response = $client->floatingIPs()->actions()->assign($floatingIpId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action['id']);
        $this->assertEquals('assign_floating_ip', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'floating_ip_actions', 'assign');
    }

    /**
     * Test floating IP actions - unassign floating IP
     */
    public function test_can_unassign_floating_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $floatingIpId = '42';
        $response = $client->floatingIPs()->actions()->unassign($floatingIpId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action['id']);
        $this->assertEquals('unassign_floating_ip', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'floating_ip_actions', 'unassign');
    }

    /**
     * Test floating IP actions - change reverse DNS
     */
    public function test_can_change_floating_ip_reverse_dns(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $floatingIpId = '42';
        $parameters = [
            'dns_ptr' => 'new.example.com',
        ];

        $response = $client->floatingIPs()->actions()->changeReverseDns($floatingIpId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action['id']);
        $this->assertEquals('change_reverse_dns', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'floating_ip_actions', 'change_reverse_dns');
    }

    /**
     * Test floating IP actions - change protection
     */
    public function test_can_change_floating_ip_protection(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $floatingIpId = '42';
        $parameters = [
            'delete' => true,
        ];

        $response = $client->floatingIPs()->actions()->changeProtection($floatingIpId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action['id']);
        $this->assertEquals('change_protection', $action['command']);
        $this->assertEquals('running', $action['status']);

        $this->assertRequestWasMade($requests, 'floating_ip_actions', 'change_protection');
    }

    /**
     * Test floating IP actions workflow simulation
     */
    public function test_floating_ip_actions_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // Assign floating IP response
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 1,
                    'command' => 'assign_floating_ip',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2023-01-01T00:00:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 42,
                            'type' => 'floating_ip',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
            // Change reverse DNS response
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 2,
                    'command' => 'change_reverse_dns',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2023-01-01T00:01:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 42,
                            'type' => 'floating_ip',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $floatingIpId = '42';

        // Simulate a workflow: assign floating IP, then change reverse DNS
        $assignResponse = $client->floatingIPs()->actions()->assign($floatingIpId, ['server' => 123]);
        $this->assertInstanceOf(ActionResponse::class, $assignResponse);
        $this->assertEquals('assign_floating_ip', $assignResponse->action()['command']);

        $changeDnsResponse = $client->floatingIPs()->actions()->changeReverseDns($floatingIpId, ['dns_ptr' => 'new.example.com']);
        $this->assertInstanceOf(ActionResponse::class, $changeDnsResponse);
        $this->assertEquals('change_reverse_dns', $changeDnsResponse->action()['command']);

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'floating_ip_actions', 'assign');
        $this->assertRequestWasMade($requests, 'floating_ip_actions', 'change_reverse_dns');
    }

    /**
     * Test floating IP home location validation
     */
    public function test_floating_ip_home_location_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->floatingIPs()->list();
        $floatingIps = $response->floatingIps();

        foreach ($floatingIps as $floatingIp) {
            $homeLocation = $floatingIp->homeLocation();

            // Test home location structure
            $this->assertIsInt($homeLocation['id']);
            $this->assertIsString($homeLocation['name']);
            $this->assertIsString($homeLocation['description']);
            $this->assertIsString($homeLocation['country']);
            $this->assertIsString($homeLocation['city']);
            $this->assertIsFloat($homeLocation['latitude']);
            $this->assertIsFloat($homeLocation['longitude']);
            $this->assertIsString($homeLocation['network_zone']);

            // Test home location values
            $this->assertGreaterThan(0, $homeLocation['id']);
            $this->assertNotEmpty($homeLocation['name']);
            $this->assertNotEmpty($homeLocation['country']);
            $this->assertNotEmpty($homeLocation['city']);
            $this->assertGreaterThanOrEqual(-90, $homeLocation['latitude']);
            $this->assertLessThanOrEqual(90, $homeLocation['latitude']);
            $this->assertGreaterThanOrEqual(-180, $homeLocation['longitude']);
            $this->assertLessThanOrEqual(180, $homeLocation['longitude']);
        }
    }

    /**
     * Test floating IP DNS PTR validation
     */
    public function test_floating_ip_dns_ptr_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->floatingIPs()->list();
        $floatingIps = $response->floatingIps();

        foreach ($floatingIps as $floatingIp) {
            $dnsPtr = $floatingIp->dnsPtr();

            // Test DNS PTR structure
            $this->assertIsArray($dnsPtr);

            foreach ($dnsPtr as $ptr) {
                $this->assertIsArray($ptr);
                $this->assertArrayHasKey('ip', $ptr);
                $this->assertArrayHasKey('dns_ptr', $ptr);
                $this->assertIsString($ptr['ip']);
                $this->assertIsString($ptr['dns_ptr']);

                // Test IP format
                $this->assertMatchesRegularExpression('/^(\d{1,3}\.){3}\d{1,3}$/', $ptr['ip']);

                // Test DNS PTR format
                $this->assertMatchesRegularExpression('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $ptr['dns_ptr']);
            }
        }
    }
}
