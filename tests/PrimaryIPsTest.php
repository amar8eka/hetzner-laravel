<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\PrimaryIPActions\Action;
use Boci\HetznerLaravel\Responses\PrimaryIPActions\ActionResponse;
use Boci\HetznerLaravel\Responses\PrimaryIPActions\ListActionsResponse;
use Boci\HetznerLaravel\Responses\PrimaryIPs\CreateResponse;
use Boci\HetznerLaravel\Responses\PrimaryIPs\DeleteResponse;
use Boci\HetznerLaravel\Responses\PrimaryIPs\ListResponse;
use Boci\HetznerLaravel\Responses\PrimaryIPs\PrimaryIP;
use Boci\HetznerLaravel\Responses\PrimaryIPs\RetrieveResponse;
use Boci\HetznerLaravel\Responses\PrimaryIPs\UpdateResponse;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Primary IPs Test Suite
 *
 * This test suite covers all functionality related to the Primary IPs resource,
 * including listing, creating, getting, updating, deleting primary IPs and their actions.
 */
final class PrimaryIPsTest extends TestCase
{
    /**
     * Test listing primary IPs with fake data
     */
    public function test_can_list_primary_ips_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->primaryIPs()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertIsArray($response->primaryIps());
        $this->assertCount(1, $response->primaryIps());

        $primaryIps = $response->primaryIps();
        $this->assertInstanceOf(PrimaryIP::class, $primaryIps[0]);
        $this->assertEquals(1, $primaryIps[0]->id());
        $this->assertEquals('test-primary-ip-1', $primaryIps[0]->name());
        $this->assertEquals('1.2.3.4', $primaryIps[0]->ip());
        $this->assertEquals('ipv4', $primaryIps[0]->type());
        $this->assertNull($primaryIps[0]->assigneeId());
        $this->assertNull($primaryIps[0]->assigneeType());
        $this->assertFalse($primaryIps[0]->autoDelete());
        $this->assertFalse($primaryIps[0]->blocked());
        $this->assertIsArray($primaryIps[0]->dnsPtr());
        $this->assertIsArray($primaryIps[0]->homeLocation());
        $this->assertIsArray($primaryIps[0]->datacenter());
        $this->assertIsArray($primaryIps[0]->protection());
        $this->assertIsArray($primaryIps[0]->labels());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $primaryIps[0]->created());

        $this->assertRequestWasMade($requests, 'primary_ips', 'list');
    }

    /**
     * Test listing primary IPs with custom response
     */
    public function test_can_list_primary_ips_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'primary_ips' => [
                    [
                        'id' => 42,
                        'name' => 'custom-primary-ip',
                        'ip' => '192.168.1.100',
                        'type' => 'ipv4',
                        'assignee_id' => 123,
                        'assignee_type' => 'server',
                        'auto_delete' => true,
                        'blocked' => false,
                        'created' => '2023-06-15T10:30:00+00:00',
                        'datacenter' => [
                            'id' => 1,
                            'name' => 'fsn1-dc8',
                            'description' => 'Falkenstein DC Park 8',
                            'location' => [
                                'id' => 1,
                                'name' => 'fsn1',
                                'description' => 'Falkenstein DC Park 1',
                                'country' => 'DE',
                                'city' => 'Falkenstein',
                                'latitude' => 50.4762,
                                'longitude' => 12.3707,
                                'network_zone' => 'eu-central',
                            ],
                            'server_types' => [
                                'supported' => [1, 2, 3],
                                'available' => [1, 2, 3],
                                'available_for_migration' => [1, 2, 3],
                            ],
                        ],
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
                        'labels' => [
                            'environment' => 'production',
                            'team' => 'infrastructure',
                        ],
                        'protection' => [
                            'delete' => true,
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

        $response = $client->primaryIPs()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->primaryIps());

        $primaryIp = $response->primaryIps()[0];
        $this->assertEquals(42, $primaryIp->id());
        $this->assertEquals('custom-primary-ip', $primaryIp->name());
        $this->assertEquals('192.168.1.100', $primaryIp->ip());
        $this->assertEquals('ipv4', $primaryIp->type());
        $this->assertEquals(123, $primaryIp->assigneeId());
        $this->assertEquals('server', $primaryIp->assigneeType());
        $this->assertTrue($primaryIp->autoDelete());
        $this->assertEquals('production', $primaryIp->labels()['environment']);
        $this->assertEquals('infrastructure', $primaryIp->labels()['team']);

        $this->assertRequestWasMade($requests, 'primary_ips', 'list');
    }

    /**
     * Test listing primary IPs with query parameters
     */
    public function test_can_list_primary_ips_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'my-primary-ip',
        ];

        $response = $client->primaryIPs()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);

        $this->assertRequestWasMade($requests, 'primary_ips', 'list');
    }

    /**
     * Test creating a primary IP
     */
    public function test_can_create_primary_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'new-primary-ip',
            'type' => 'ipv4',
            'datacenter' => 'nbg1-dc3',
            'labels' => [
                'environment' => 'production',
                'team' => 'backend',
            ],
        ];

        $response = $client->primaryIPs()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $this->assertInstanceOf(PrimaryIP::class, $response->primaryIp());
        $this->assertNotNull($response->action());

        $primaryIp = $response->primaryIp();
        $this->assertEquals(1, $primaryIp->id());
        $this->assertEquals('new-primary-ip', $primaryIp->name());
        $this->assertEquals('1.2.3.4', $primaryIp->ip());
        $this->assertEquals('ipv4', $primaryIp->type());
        $this->assertNull($primaryIp->assigneeId());
        $this->assertNull($primaryIp->assigneeType());
        $this->assertFalse($primaryIp->autoDelete());
        $this->assertFalse($primaryIp->blocked());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('create_primary_ip', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'primary_ips', 'create');
    }

    /**
     * Test creating primary IP with custom response
     */
    public function test_can_create_primary_ip_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'primary_ip' => [
                    'id' => 99,
                    'name' => 'custom-new-primary-ip',
                    'ip' => '10.0.0.1',
                    'type' => 'ipv4',
                    'assignee_id' => null,
                    'assignee_type' => null,
                    'auto_delete' => false,
                    'blocked' => false,
                    'created' => '2023-06-20T09:15:00+00:00',
                    'datacenter' => [
                        'id' => 1,
                        'name' => 'fsn1-dc8',
                        'description' => 'Falkenstein DC Park 8',
                        'location' => [
                            'id' => 1,
                            'name' => 'fsn1',
                            'description' => 'Falkenstein DC Park 1',
                            'country' => 'DE',
                            'city' => 'Falkenstein',
                            'latitude' => 50.4762,
                            'longitude' => 12.3707,
                            'network_zone' => 'eu-central',
                        ],
                        'server_types' => [
                            'supported' => [1, 2, 3],
                            'available' => [1, 2, 3],
                            'available_for_migration' => [1, 2, 3],
                        ],
                    ],
                    'dns_ptr' => [
                        [
                            'ip' => '10.0.0.1',
                            'dns_ptr' => 'new-primary-ip.example.com',
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
                    'labels' => [
                        'environment' => 'development',
                        'team' => 'frontend',
                    ],
                    'protection' => [
                        'delete' => false,
                    ],
                ],
                'action' => [
                    'id' => 99,
                    'command' => 'create_primary_ip',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2023-06-20T09:15:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 99,
                            'type' => 'primary_ip',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'custom-new-primary-ip',
            'type' => 'ipv4',
            'datacenter' => 'fsn1-dc8',
        ];

        $response = $client->primaryIPs()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $primaryIp = $response->primaryIp();

        $this->assertEquals(99, $primaryIp->id());
        $this->assertEquals('custom-new-primary-ip', $primaryIp->name());
        $this->assertEquals('10.0.0.1', $primaryIp->ip());
        $this->assertEquals('ipv4', $primaryIp->type());
        $this->assertEquals('development', $primaryIp->labels()['environment']);
        $this->assertEquals('frontend', $primaryIp->labels()['team']);

        $this->assertRequestWasMade($requests, 'primary_ips', 'create');
    }

    /**
     * Test getting a specific primary IP
     */
    public function test_can_get_specific_primary_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $primaryIpId = '42';
        $response = $client->primaryIPs()->retrieve($primaryIpId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $this->assertInstanceOf(PrimaryIP::class, $response->primaryIp());

        $primaryIp = $response->primaryIp();
        $this->assertEquals(42, $primaryIp->id());
        $this->assertEquals('test-primary-ip', $primaryIp->name());
        $this->assertEquals('1.2.3.4', $primaryIp->ip());
        $this->assertEquals('ipv4', $primaryIp->type());
        $this->assertNull($primaryIp->assigneeId());
        $this->assertNull($primaryIp->assigneeType());
        $this->assertFalse($primaryIp->autoDelete());
        $this->assertFalse($primaryIp->blocked());
        $this->assertIsArray($primaryIp->dnsPtr());
        $this->assertIsArray($primaryIp->homeLocation());
        $this->assertIsArray($primaryIp->datacenter());
        $this->assertIsArray($primaryIp->protection());
        $this->assertIsArray($primaryIp->labels());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $primaryIp->created());

        $this->assertRequestWasMade($requests, 'primary_ips', 'get');
    }

    /**
     * Test getting primary IP with custom response
     */
    public function test_can_get_primary_ip_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'primary_ip' => [
                    'id' => 123,
                    'name' => 'detailed-primary-ip',
                    'ip' => '172.16.0.1',
                    'type' => 'ipv4',
                    'assignee_id' => 456,
                    'assignee_type' => 'server',
                    'auto_delete' => true,
                    'blocked' => false,
                    'created' => '2023-05-10T16:20:00+00:00',
                    'datacenter' => [
                        'id' => 1,
                        'name' => 'fsn1-dc8',
                        'description' => 'Falkenstein DC Park 8',
                        'location' => [
                            'id' => 1,
                            'name' => 'fsn1',
                            'description' => 'Falkenstein DC Park 1',
                            'country' => 'DE',
                            'city' => 'Falkenstein',
                            'latitude' => 50.4762,
                            'longitude' => 12.3707,
                            'network_zone' => 'eu-central',
                        ],
                        'server_types' => [
                            'supported' => [1, 2, 3],
                            'available' => [1, 2, 3],
                            'available_for_migration' => [1, 2, 3],
                        ],
                    ],
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
                    'labels' => [
                        'environment' => 'production',
                        'team' => 'infrastructure',
                        'project' => 'web-app',
                    ],
                    'protection' => [
                        'delete' => true,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $primaryIpId = '123';
        $response = $client->primaryIPs()->retrieve($primaryIpId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $primaryIp = $response->primaryIp();

        $this->assertEquals(123, $primaryIp->id());
        $this->assertEquals('detailed-primary-ip', $primaryIp->name());
        $this->assertEquals('172.16.0.1', $primaryIp->ip());
        $this->assertEquals('ipv4', $primaryIp->type());
        $this->assertEquals(456, $primaryIp->assigneeId());
        $this->assertEquals('server', $primaryIp->assigneeType());
        $this->assertTrue($primaryIp->autoDelete());
        $this->assertEquals('production', $primaryIp->labels()['environment']);
        $this->assertEquals('infrastructure', $primaryIp->labels()['team']);
        $this->assertEquals('web-app', $primaryIp->labels()['project']);

        $this->assertRequestWasMade($requests, 'primary_ips', 'get');
    }

    /**
     * Test updating a primary IP
     */
    public function test_can_update_primary_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $primaryIpId = '42';
        $parameters = [
            'name' => 'updated-primary-ip',
            'labels' => [
                'environment' => 'staging',
                'team' => 'frontend',
            ],
        ];

        $response = $client->primaryIPs()->update($primaryIpId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);
        $this->assertInstanceOf(PrimaryIP::class, $response->primaryIp());

        $primaryIp = $response->primaryIp();
        $this->assertEquals(42, $primaryIp->id());
        $this->assertEquals('updated-primary-ip', $primaryIp->name());
        $this->assertEquals('staging', $primaryIp->labels()['environment']);
        $this->assertEquals('frontend', $primaryIp->labels()['team']);

        $this->assertRequestWasMade($requests, 'primary_ips', 'update');
    }

    /**
     * Test deleting a primary IP
     */
    public function test_can_delete_primary_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $primaryIpId = '42';
        $response = $client->primaryIPs()->delete($primaryIpId);

        $this->assertInstanceOf(DeleteResponse::class, $response);
        $this->assertNotNull($response->action());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('delete_primary_ip', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'primary_ips', 'delete');
    }

    /**
     * Test primary IP response structure
     */
    public function test_primary_ip_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->primaryIPs()->list();
        $primaryIps = $response->primaryIps();

        foreach ($primaryIps as $primaryIp) {
            // Test all primary IP properties
            $this->assertIsInt($primaryIp->id());
            $this->assertIsString($primaryIp->name());
            $this->assertIsString($primaryIp->ip());
            $this->assertIsString($primaryIp->type());
            $this->assertIsBool($primaryIp->autoDelete());
            $this->assertIsBool($primaryIp->blocked());
            $this->assertIsArray($primaryIp->dnsPtr());
            $this->assertIsArray($primaryIp->homeLocation());
            $this->assertIsArray($primaryIp->datacenter());
            $this->assertIsArray($primaryIp->protection());
            $this->assertIsArray($primaryIp->labels());
            $this->assertIsString($primaryIp->created());

            // Test primary IP type values
            $this->assertContains($primaryIp->type(), ['ipv4', 'ipv6']);

            // Test IP address format
            $this->assertMatchesRegularExpression('/^(\d{1,3}\.){3}\d{1,3}$/', $primaryIp->ip());

            // Test datacenter structure
            $datacenter = $primaryIp->datacenter();
            $this->assertArrayHasKey('id', $datacenter);
            $this->assertArrayHasKey('name', $datacenter);
            $this->assertArrayHasKey('description', $datacenter);
            $this->assertArrayHasKey('location', $datacenter);
            $this->assertArrayHasKey('server_types', $datacenter);

            // Test home location structure
            $homeLocation = $primaryIp->homeLocation();
            $this->assertArrayHasKey('id', $homeLocation);
            $this->assertArrayHasKey('name', $homeLocation);
            $this->assertArrayHasKey('description', $homeLocation);
            $this->assertArrayHasKey('country', $homeLocation);
            $this->assertArrayHasKey('city', $homeLocation);
            $this->assertArrayHasKey('latitude', $homeLocation);
            $this->assertArrayHasKey('longitude', $homeLocation);
            $this->assertArrayHasKey('network_zone', $homeLocation);

            // Test protection structure
            $protection = $primaryIp->protection();
            $this->assertArrayHasKey('delete', $protection);
            $this->assertIsBool($protection['delete']);

            // Test labels structure
            foreach ($primaryIp->labels() as $key => $value) {
                $this->assertIsString($key);
                $this->assertIsString($value);
            }

            // Test created date format
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/', $primaryIp->created());
        }
    }

    /**
     * Test handling primary IP API exception
     */
    public function test_can_handle_primary_ip_api_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Primary IP not found', new Request('GET', '/primary_ips/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Primary IP not found');

        $client->primaryIPs()->retrieve('999');
    }

    /**
     * Test handling primary IP list exception
     */
    public function test_can_handle_primary_ip_list_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Rate limit exceeded', new Request('GET', '/primary_ips')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->primaryIPs()->list();
    }

    /**
     * Test handling mixed primary IP response types
     */
    public function test_can_handle_mixed_primary_ip_response_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'primary_ips' => [
                    [
                        'id' => 1,
                        'name' => 'my-primary-ip',
                        'ip' => '1.2.3.4',
                        'type' => 'ipv4',
                        'assignee_id' => null,
                        'assignee_type' => null,
                        'auto_delete' => false,
                        'blocked' => false,
                        'created' => '2023-01-01T00:00:00+00:00',
                        'datacenter' => [
                            'id' => 1,
                            'name' => 'nbg1-dc3',
                            'description' => 'Nuremberg 1 DC 3',
                            'location' => [
                                'id' => 1,
                                'name' => 'nbg1',
                                'description' => 'Nuremberg',
                                'country' => 'DE',
                                'city' => 'Nuremberg',
                                'latitude' => 49.4521,
                                'longitude' => 11.0767,
                                'network_zone' => 'eu-central',
                            ],
                            'server_types' => [
                                'supported' => [1, 2, 3],
                                'available' => [1, 2, 3],
                                'available_for_migration' => [1, 2, 3],
                            ],
                        ],
                        'dns_ptr' => [
                            [
                                'ip' => '1.2.3.4',
                                'dns_ptr' => 'primary-ip.example.com',
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
                        'labels' => [],
                        'protection' => [
                            'delete' => false,
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
            new RequestException('Primary IP not found', new Request('GET', '/primary_ips/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $listResponse = $client->primaryIPs()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->primaryIps());

        // Second call should throw exception
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Primary IP not found');
        $client->primaryIPs()->retrieve('999');
    }

    /**
     * Test using individual primary IP fake
     */
    public function test_using_individual_primary_ip_fake(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $primaryIPsFake = $client->primaryIPs();

        $response = $primaryIPsFake->list(['page' => 1, 'per_page' => 5]);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->primaryIps());

        $primaryIPsFake->assertSent(function (array $request) {
            return $request['resource'] === 'primary_ips' &&
                   $request['method'] === 'list' &&
                   isset($request['parameters']['page']) &&
                   $request['parameters']['page'] === 1 &&
                   isset($request['parameters']['per_page']) &&
                   $request['parameters']['per_page'] === 5;
        });
    }

    /**
     * Test primary IP fake assert not sent
     */
    public function test_primary_ip_fake_assert_not_sent(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $primaryIPsFake = $client->primaryIPs();

        // No requests made yet
        $primaryIPsFake->assertNotSent();

        // Make a request
        $primaryIPsFake->list();

        // Now it should fail
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Requests were sent to primary_ips.');
        $primaryIPsFake->assertNotSent();
    }

    /**
     * Test primary IP workflow simulation
     */
    public function test_primary_ip_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List primary IPs response
            new Response(200, [], json_encode([
                'primary_ips' => [
                    [
                        'id' => 1,
                        'name' => 'my-primary-ip',
                        'ip' => '1.2.3.4',
                        'type' => 'ipv4',
                        'assignee_id' => null,
                        'assignee_type' => null,
                        'auto_delete' => false,
                        'blocked' => false,
                        'created' => '2023-01-01T00:00:00+00:00',
                        'datacenter' => [
                            'id' => 1,
                            'name' => 'nbg1-dc3',
                            'description' => 'Nuremberg 1 DC 3',
                            'location' => [
                                'id' => 1,
                                'name' => 'nbg1',
                                'description' => 'Nuremberg',
                                'country' => 'DE',
                                'city' => 'Nuremberg',
                                'latitude' => 49.4521,
                                'longitude' => 11.0767,
                                'network_zone' => 'eu-central',
                            ],
                            'server_types' => [
                                'supported' => [1, 2, 3],
                                'available' => [1, 2, 3],
                                'available_for_migration' => [1, 2, 3],
                            ],
                        ],
                        'dns_ptr' => [
                            [
                                'ip' => '1.2.3.4',
                                'dns_ptr' => 'primary-ip.example.com',
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
                        'labels' => [],
                        'protection' => [
                            'delete' => false,
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
            // Get specific primary IP response
            new Response(200, [], json_encode([
                'primary_ip' => [
                    'id' => 1,
                    'name' => 'my-primary-ip',
                    'ip' => '1.2.3.4',
                    'type' => 'ipv4',
                    'assignee_id' => null,
                    'assignee_type' => null,
                    'auto_delete' => false,
                    'blocked' => false,
                    'created' => '2023-01-01T00:00:00+00:00',
                    'datacenter' => [
                        'id' => 1,
                        'name' => 'nbg1-dc3',
                        'description' => 'Nuremberg 1 DC 3',
                        'location' => [
                            'id' => 1,
                            'name' => 'nbg1',
                            'description' => 'Nuremberg',
                            'country' => 'DE',
                            'city' => 'Nuremberg',
                            'latitude' => 49.4521,
                            'longitude' => 11.0767,
                            'network_zone' => 'eu-central',
                        ],
                        'server_types' => [
                            'supported' => [1, 2, 3],
                            'available' => [1, 2, 3],
                            'available_for_migration' => [1, 2, 3],
                        ],
                    ],
                    'dns_ptr' => [
                        [
                            'ip' => '1.2.3.4',
                            'dns_ptr' => 'primary-ip.example.com',
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
                    'labels' => [],
                    'protection' => [
                        'delete' => false,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list primary IPs, then get details of the first one
        $listResponse = $client->primaryIPs()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->primaryIps());

        $firstPrimaryIp = $listResponse->primaryIps()[0];
        $primaryIpId = (string) $firstPrimaryIp->id();

        $getResponse = $client->primaryIPs()->retrieve($primaryIpId);
        $this->assertInstanceOf(RetrieveResponse::class, $getResponse);
        $this->assertEquals($firstPrimaryIp->id(), $getResponse->primaryIp()->id());
        $this->assertEquals($firstPrimaryIp->name(), $getResponse->primaryIp()->name());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'primary_ips', 'list');
        $this->assertRequestWasMade($requests, 'primary_ips', 'get');
    }

    /**
     * Test primary IP error response handling
     */
    public function test_primary_ip_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Primary IP not found', new Request('GET', '/primary_ips/nonexistent')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Primary IP not found');
        $client->primaryIPs()->retrieve('nonexistent');
    }

    /**
     * Test primary IP empty list response
     */
    public function test_primary_ip_empty_list_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'primary_ips' => [],
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

        $response = $client->primaryIPs()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(0, $response->primaryIps());
        $this->assertEmpty($response->primaryIps());
    }

    /**
     * Test primary IP pagination
     */
    public function test_primary_ip_pagination(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->primaryIPs()->list();
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
     * Test primary IP to array conversion
     */
    public function test_primary_ip_to_array_conversion(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->primaryIPs()->list();
        $primaryIps = $response->primaryIps();

        foreach ($primaryIps as $primaryIp) {
            $array = $primaryIp->toArray();

            $this->assertIsArray($array);
            $this->assertArrayHasKey('id', $array);
            $this->assertArrayHasKey('name', $array);
            $this->assertArrayHasKey('ip', $array);
            $this->assertArrayHasKey('type', $array);
            $this->assertArrayHasKey('assignee_id', $array);
            $this->assertArrayHasKey('assignee_type', $array);
            $this->assertArrayHasKey('auto_delete', $array);
            $this->assertArrayHasKey('blocked', $array);
            $this->assertArrayHasKey('created', $array);
            $this->assertArrayHasKey('datacenter', $array);
            $this->assertArrayHasKey('dns_ptr', $array);
            $this->assertArrayHasKey('home_location', $array);
            $this->assertArrayHasKey('labels', $array);
            $this->assertArrayHasKey('protection', $array);

            $this->assertEquals($primaryIp->id(), $array['id']);
            $this->assertEquals($primaryIp->name(), $array['name']);
            $this->assertEquals($primaryIp->ip(), $array['ip']);
            $this->assertEquals($primaryIp->type(), $array['type']);
        }
    }

    /**
     * Test primary IP actions - list all actions
     */
    public function test_can_list_all_primary_ip_actions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->primaryIPs()->actions()->listAll();

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertIsArray($response->actions());
        $this->assertCount(2, $response->actions());

        $actions = $response->actions();
        $this->assertInstanceOf(Action::class, $actions[0]);
        $this->assertEquals(1, $actions[0]->id());
        $this->assertEquals('assign_primary_ip', $actions[0]->command());
        $this->assertEquals('success', $actions[0]->status());
        $this->assertEquals(100, $actions[0]->progress());

        $this->assertInstanceOf(Action::class, $actions[1]);
        $this->assertEquals(2, $actions[1]->id());
        $this->assertEquals('change_reverse_dns', $actions[1]->command());
        $this->assertEquals('running', $actions[1]->status());
        $this->assertEquals(50, $actions[1]->progress());

        $this->assertRequestWasMade($requests, 'primary_ip_actions', 'list_all');
    }

    /**
     * Test primary IP actions - list actions for specific primary IP
     */
    public function test_can_list_primary_ip_actions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $primaryIpId = '42';
        $response = $client->primaryIPs()->actions()->list($primaryIpId);

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertIsArray($response->actions());
        $this->assertCount(2, $response->actions());

        $actions = $response->actions();
        $this->assertInstanceOf(Action::class, $actions[0]);
        $this->assertEquals(1, $actions[0]->id());
        $this->assertEquals('assign_primary_ip', $actions[0]->command());
        $this->assertEquals('success', $actions[0]->status());
        $this->assertEquals(100, $actions[0]->progress());

        $this->assertInstanceOf(Action::class, $actions[1]);
        $this->assertEquals(2, $actions[1]->id());
        $this->assertEquals('change_reverse_dns', $actions[1]->command());
        $this->assertEquals('running', $actions[1]->status());
        $this->assertEquals(50, $actions[1]->progress());

        $this->assertRequestWasMade($requests, 'primary_ip_actions', 'list');
    }

    /**
     * Test primary IP actions - get action by ID
     */
    public function test_can_get_primary_ip_action_by_id(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $actionId = '123';
        $response = $client->primaryIPs()->actions()->getById($actionId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(123, $action->id());
        $this->assertEquals('assign_primary_ip', $action->command());
        $this->assertEquals('running', $action->status());
        $this->assertEquals(0, $action->progress());

        $this->assertRequestWasMade($requests, 'primary_ip_actions', 'get_by_id');
    }

    /**
     * Test primary IP actions - get action for specific primary IP
     */
    public function test_can_get_primary_ip_action(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $primaryIpId = '42';
        $actionId = '123';
        $response = $client->primaryIPs()->actions()->retrieve($primaryIpId, $actionId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(123, $action->id());
        $this->assertEquals('assign_primary_ip', $action->command());
        $this->assertEquals('running', $action->status());
        $this->assertEquals(0, $action->progress());

        $this->assertRequestWasMade($requests, 'primary_ip_actions', 'get');
    }

    /**
     * Test primary IP actions - assign primary IP
     */
    public function test_can_assign_primary_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $primaryIpId = '42';
        $parameters = [
            'assignee_id' => 123,
            'assignee_type' => 'server',
        ];

        $response = $client->primaryIPs()->actions()->assign($primaryIpId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('assign_primary_ip', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'primary_ip_actions', 'assign');
    }

    /**
     * Test primary IP actions - unassign primary IP
     */
    public function test_can_unassign_primary_ip(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $primaryIpId = '42';
        $response = $client->primaryIPs()->actions()->unassign($primaryIpId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('unassign_primary_ip', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'primary_ip_actions', 'unassign');
    }

    /**
     * Test primary IP actions - change reverse DNS
     */
    public function test_can_change_primary_ip_reverse_dns(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $primaryIpId = '42';
        $parameters = [
            'dns_ptr' => 'new.example.com',
        ];

        $response = $client->primaryIPs()->actions()->changeReverseDns($primaryIpId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('change_reverse_dns', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'primary_ip_actions', 'change_reverse_dns');
    }

    /**
     * Test primary IP actions - change protection
     */
    public function test_can_change_primary_ip_protection(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $primaryIpId = '42';
        $parameters = [
            'delete' => true,
        ];

        $response = $client->primaryIPs()->actions()->changeProtection($primaryIpId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('change_protection', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'primary_ip_actions', 'change_protection');
    }

    /**
     * Test primary IP actions workflow simulation
     */
    public function test_primary_ip_actions_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // Assign primary IP response
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 1,
                    'command' => 'assign_primary_ip',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2023-01-01T00:00:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 42,
                            'type' => 'primary_ip',
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
                            'type' => 'primary_ip',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $primaryIpId = '42';

        // Simulate a workflow: assign primary IP, then change reverse DNS
        $assignResponse = $client->primaryIPs()->actions()->assign($primaryIpId, ['assignee_id' => 123, 'assignee_type' => 'server']);
        $this->assertInstanceOf(ActionResponse::class, $assignResponse);
        $this->assertEquals('assign_primary_ip', $assignResponse->action()->command());

        $changeDnsResponse = $client->primaryIPs()->actions()->changeReverseDns($primaryIpId, ['dns_ptr' => 'new.example.com']);
        $this->assertInstanceOf(ActionResponse::class, $changeDnsResponse);
        $this->assertEquals('change_reverse_dns', $changeDnsResponse->action()->command());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'primary_ip_actions', 'assign');
        $this->assertRequestWasMade($requests, 'primary_ip_actions', 'change_reverse_dns');
    }

    /**
     * Test primary IP datacenter validation
     */
    public function test_primary_ip_datacenter_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->primaryIPs()->list();
        $primaryIps = $response->primaryIps();

        foreach ($primaryIps as $primaryIp) {
            $datacenter = $primaryIp->datacenter();
            $this->assertNotNull($datacenter);

            // Test datacenter structure
            $this->assertIsInt($datacenter['id']);
            $this->assertIsString($datacenter['name']);
            $this->assertIsString($datacenter['description']);
            $this->assertIsArray($datacenter['location']);
            $this->assertIsArray($datacenter['server_types']);

            // Test datacenter values
            $this->assertGreaterThan(0, $datacenter['id']);
            $this->assertNotEmpty($datacenter['name']);
            $this->assertNotEmpty($datacenter['description']);

            // Test location structure
            $location = $datacenter['location'];
            $this->assertArrayHasKey('id', $location);
            $this->assertArrayHasKey('name', $location);
            $this->assertArrayHasKey('description', $location);
            $this->assertArrayHasKey('country', $location);
            $this->assertArrayHasKey('city', $location);
            $this->assertArrayHasKey('latitude', $location);
            $this->assertArrayHasKey('longitude', $location);
            $this->assertArrayHasKey('network_zone', $location);

            // Test server types structure
            $serverTypes = $datacenter['server_types'];
            $this->assertArrayHasKey('supported', $serverTypes);
            $this->assertArrayHasKey('available', $serverTypes);
            $this->assertArrayHasKey('available_for_migration', $serverTypes);
            $this->assertIsArray($serverTypes['supported']);
            $this->assertIsArray($serverTypes['available']);
            $this->assertIsArray($serverTypes['available_for_migration']);
        }
    }

    /**
     * Test primary IP DNS PTR validation
     */
    public function test_primary_ip_dns_ptr_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->primaryIPs()->list();
        $primaryIps = $response->primaryIps();

        foreach ($primaryIps as $primaryIp) {
            $dnsPtr = $primaryIp->dnsPtr();

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
