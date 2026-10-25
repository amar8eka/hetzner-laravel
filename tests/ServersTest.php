<?php

namespace Tests;

use Boci\HetznerLaravel\Responses\ServerActions\ActionResponse;
use Boci\HetznerLaravel\Responses\ServerActions\ConsoleResponse;
use Boci\HetznerLaravel\Responses\ServerActions\ListActionsResponse;
use Boci\HetznerLaravel\Responses\Servers\CreateResponse;
use Boci\HetznerLaravel\Responses\Servers\DeleteResponse;
use Boci\HetznerLaravel\Responses\Servers\ListResponse;
use Boci\HetznerLaravel\Responses\Servers\MetricsResponse;
use Boci\HetznerLaravel\Responses\Servers\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Servers\UpdateResponse;
use Boci\HetznerLaravel\Testing\TestCase;
use Exception;
use GuzzleHttp\Psr7\Response;

/**
 * Servers Test Suite
 *
 * Comprehensive test suite for the Hetzner Cloud Servers API functionality.
 * This test file covers all server-related operations including CRUD operations,
 * server actions, metrics, and error handling scenarios.
 */
class ServersTest extends TestCase
{
    /**
     * Test basic server creation with auto-generated fake data
     */
    public function test_can_create_server_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->create([
            'name' => 'test-server',
            'server_type' => 'cx11',
            'image' => 'ubuntu-20.04',
        ]);

        $this->assertInstanceOf(
            CreateResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'servers', 'create', function ($request) {
            return $request['parameters']['name'] === 'test-server' &&
                   $request['parameters']['server_type'] === 'cx11' &&
                   $request['parameters']['image'] === 'ubuntu-20.04';
        });
    }

    /**
     * Test server creation with custom response data
     */
    public function test_can_create_server_with_custom_response(): void
    {
        $customResponse = new Response(201, [], json_encode([
            'server' => [
                'id' => 12345,
                'name' => 'production-server',
                'status' => 'initializing',
                'created' => '2023-12-01T10:00:00+00:00',
                'public_net' => [
                    'ipv4' => [
                        'ip' => '192.168.1.100',
                        'blocked' => false,
                        'dns_ptr' => 'prod.example.com',
                    ],
                ],
                'server_type' => [
                    'id' => 1,
                    'name' => 'cx21',
                    'cores' => 3,
                    'memory' => 8.0,
                    'disk' => 80,
                ],
                'image' => [
                    'id' => 1,
                    'name' => 'ubuntu-22.04',
                    'description' => 'Ubuntu 22.04',
                ],
            ],
            'action' => [
                'id' => 67890,
                'command' => 'create_server',
                'status' => 'running',
                'progress' => 0,
            ],
            'root_password' => 'secure-password-123',
        ]) ?: '');

        $requests = [];
        $responses = [$customResponse];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->create([
            'name' => 'production-server',
            'server_type' => 'cx21',
            'image' => 'ubuntu-22.04',
            'location' => 'nbg1',
        ]);

        $server = $response->server();
        $this->assertEquals(12345, $server->id());
        $this->assertEquals('production-server', $server->name());
        $this->assertEquals('initializing', $server->status());
        $this->assertEquals('secure-password-123', $response->rootPassword());

        $this->assertRequestWasMade($requests, 'servers', 'create', function ($request) {
            return $request['parameters']['name'] === 'production-server' &&
                   $request['parameters']['server_type'] === 'cx21' &&
                   $request['parameters']['location'] === 'nbg1';
        });
    }

    /**
     * Test listing servers with auto-generated fake data
     */
    public function test_can_list_servers_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->list();

        $this->assertInstanceOf(
            ListResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'servers', 'list');
    }

    /**
     * Test listing servers with custom response data
     */
    public function test_can_list_servers_with_custom_response(): void
    {
        $customResponse = new Response(200, [], json_encode([
            'servers' => [
                [
                    'id' => 1,
                    'name' => 'web-server-1',
                    'status' => 'running',
                    'created' => '2023-11-01T10:00:00+00:00',
                    'public_net' => [
                        'ipv4' => [
                            'ip' => '1.2.3.4',
                            'blocked' => false,
                            'dns_ptr' => 'web1.example.com',
                        ],
                    ],
                    'server_type' => [
                        'id' => 1,
                        'name' => 'cx11',
                        'cores' => 1,
                        'memory' => 4.0,
                        'disk' => 20,
                    ],
                ],
                [
                    'id' => 2,
                    'name' => 'web-server-2',
                    'status' => 'running',
                    'created' => '2023-11-02T10:00:00+00:00',
                    'public_net' => [
                        'ipv4' => [
                            'ip' => '1.2.3.5',
                            'blocked' => false,
                            'dns_ptr' => 'web2.example.com',
                        ],
                    ],
                    'server_type' => [
                        'id' => 1,
                        'name' => 'cx11',
                        'cores' => 1,
                        'memory' => 4.0,
                        'disk' => 20,
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
                    'total_entries' => 2,
                ],
            ],
        ]) ?: '');

        $requests = [];
        $responses = [$customResponse];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->list();

        $servers = $response->servers();
        $this->assertCount(2, $servers);
        $this->assertEquals('web-server-1', $servers[0]->name());
        $this->assertEquals('web-server-2', $servers[1]->name());

        $pagination = $response->pagination();
        $this->assertEquals(2, $pagination['total']);

        $this->assertRequestWasMade($requests, 'servers', 'list');
    }

    /**
     * Test listing servers with query parameters
     */
    public function test_can_list_servers_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->list([
            'name' => 'web-server',
            'status' => 'running',
            'page' => 2,
            'per_page' => 10,
        ]);

        $this->assertInstanceOf(
            ListResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'servers', 'list', function ($request) {
            return $request['parameters']['name'] === 'web-server' &&
                   $request['parameters']['status'] === 'running' &&
                   $request['parameters']['page'] === 2 &&
                   $request['parameters']['per_page'] === 10;
        });
    }

    /**
     * Test retrieving a specific server
     */
    public function test_can_retrieve_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->retrieve('12345');

        $this->assertInstanceOf(
            RetrieveResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'servers', 'retrieve', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test retrieving a server with custom response
     */
    public function test_can_retrieve_server_with_custom_response(): void
    {
        $customResponse = new Response(200, [], json_encode([
            'server' => [
                'id' => 12345,
                'name' => 'database-server',
                'status' => 'running',
                'created' => '2023-10-01T10:00:00+00:00',
                'public_net' => [
                    'ipv4' => [
                        'ip' => '10.0.0.100',
                        'blocked' => false,
                        'dns_ptr' => 'db.example.com',
                    ],
                ],
                'server_type' => [
                    'id' => 2,
                    'name' => 'cx31',
                    'cores' => 2,
                    'memory' => 8.0,
                    'disk' => 160,
                ],
                'image' => [
                    'id' => 2,
                    'name' => 'ubuntu-20.04',
                    'description' => 'Ubuntu 20.04',
                ],
                'protection' => [
                    'delete' => true,
                    'rebuild' => false,
                ],
                'labels' => [
                    'environment' => 'production',
                    'role' => 'database',
                ],
            ],
        ]) ?: '');

        $requests = [];
        $responses = [$customResponse];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->retrieve('12345');

        $server = $response->server();
        $this->assertEquals(12345, $server->id());
        $this->assertEquals('database-server', $server->name());
        $this->assertEquals('running', $server->status());
        $this->assertTrue($server->protection()['delete']);
        $this->assertEquals('production', $server->labels()['environment']);

        $this->assertRequestWasMade($requests, 'servers', 'retrieve', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test updating a server
     */
    public function test_can_update_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->update('12345', [
            'name' => 'updated-server-name',
            'labels' => [
                'environment' => 'staging',
                'updated' => 'true',
            ],
        ]);

        $this->assertInstanceOf(
            UpdateResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'servers', 'update', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['name'] === 'updated-server-name' &&
                   $request['parameters']['labels']['environment'] === 'staging';
        });
    }

    /**
     * Test deleting a server
     */
    public function test_can_delete_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->delete('12345');

        $this->assertInstanceOf(
            DeleteResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'servers', 'delete', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test getting server metrics
     */
    public function test_can_get_server_metrics(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->metrics('12345');

        $this->assertInstanceOf(
            MetricsResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'servers', 'metrics', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test getting server metrics with query parameters
     */
    public function test_can_get_server_metrics_with_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->metrics('12345', [
            'type' => 'cpu',
            'start' => '2023-12-01T00:00:00+00:00',
            'end' => '2023-12-01T23:59:59+00:00',
        ]);

        $this->assertInstanceOf(
            MetricsResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'servers', 'metrics', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['type'] === 'cpu' &&
                   $request['parameters']['start'] === '2023-12-01T00:00:00+00:00';
        });
    }

    /**
     * Test server power on action
     */
    public function test_can_power_on_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->powerOn('12345');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'power_on', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test server power off action
     */
    public function test_can_power_off_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->powerOff('12345');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'power_off', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test server reboot action
     */
    public function test_can_reboot_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->reboot('12345');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'reboot', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test server shutdown action
     */
    public function test_can_shutdown_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->shutdown('12345');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'shutdown', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test server reset action
     */
    public function test_can_reset_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->reset('12345');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'reset', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test changing server protection settings
     */
    public function test_can_change_server_protection(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->changeProtection('12345', [
            'delete' => true,
            'rebuild' => false,
        ]);

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'change_protection', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['delete'] === true &&
                   $request['parameters']['rebuild'] === false;
        });
    }

    /**
     * Test changing server type
     */
    public function test_can_change_server_type(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->changeServerType('12345', [
            'server_type' => 'cx21',
            'upgrade_disk' => true,
        ]);

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'change_server_type', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['server_type'] === 'cx21' &&
                   $request['parameters']['upgrade_disk'] === true;
        });
    }

    /**
     * Test rebuilding a server
     */
    public function test_can_rebuild_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->rebuild('12345', [
            'image' => 'ubuntu-22.04',
        ]);

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'rebuild', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['image'] === 'ubuntu-22.04';
        });
    }

    /**
     * Test creating an image from a server
     */
    public function test_can_create_image_from_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->createImage('12345', [
            'description' => 'Backup image',
            'type' => 'snapshot',
            'labels' => [
                'backup' => 'true',
                'date' => '2023-12-01',
            ],
        ]);

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'create_image', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['description'] === 'Backup image' &&
                   $request['parameters']['type'] === 'snapshot';
        });
    }

    /**
     * Test resetting server password
     */
    public function test_can_reset_server_password(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->resetPassword('12345');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'reset_password', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test requesting server console
     */
    public function test_can_request_server_console(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->requestConsole('12345');

        $this->assertInstanceOf(
            ConsoleResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'request_console', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test attaching server to network
     */
    public function test_can_attach_server_to_network(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->attachToNetwork('12345', [
            'network' => 67890,
            'ip' => '10.0.1.100',
        ]);

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'attach_to_network', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['network'] === 67890 &&
                   $request['parameters']['ip'] === '10.0.1.100';
        });
    }

    /**
     * Test detaching server from network
     */
    public function test_can_detach_server_from_network(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->detachFromNetwork('12345', [
            'network' => 67890,
        ]);

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'detach_from_network', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['network'] === 67890;
        });
    }

    /**
     * Test changing server alias IPs
     */
    public function test_can_change_server_alias_ips(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->changeAliasIps('12345', [
            'network' => 67890,
            'alias_ips' => ['10.0.1.101', '10.0.1.102'],
        ]);

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'change_alias_ips', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['network'] === 67890 &&
                   $request['parameters']['alias_ips'] === ['10.0.1.101', '10.0.1.102'];
        });
    }

    /**
     * Test attaching ISO to server
     */
    public function test_can_attach_iso_to_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->attachIso('12345', [
            'iso' => 'ubuntu-20.04',
        ]);

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'attach_iso', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['iso'] === 'ubuntu-20.04';
        });
    }

    /**
     * Test detaching ISO from server
     */
    public function test_can_detach_iso_from_server(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->detachIso('12345');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'detach_iso', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test adding server to placement group
     */
    public function test_can_add_server_to_placement_group(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->addToPlacementGroup('12345', [
            'placement_group' => 99999,
        ]);

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'add_to_placement_group', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['placement_group'] === 99999;
        });
    }

    /**
     * Test removing server from placement group
     */
    public function test_can_remove_server_from_placement_group(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->removeFromPlacementGroup('12345');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'remove_from_placement_group', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test enabling server backups
     */
    public function test_can_enable_server_backups(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->enableBackups('12345');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'enable_backups', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test disabling server backups
     */
    public function test_can_disable_server_backups(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->disableBackups('12345');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'disable_backups', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test enabling server rescue mode
     */
    public function test_can_enable_server_rescue_mode(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->enableRescueMode('12345', [
            'type' => 'linux64',
            'ssh_keys' => [123, 456],
        ]);

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'enable_rescue_mode', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['type'] === 'linux64' &&
                   $request['parameters']['ssh_keys'] === [123, 456];
        });
    }

    /**
     * Test disabling server rescue mode
     */
    public function test_can_disable_server_rescue_mode(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->disableRescueMode('12345');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'disable_rescue_mode', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test changing server reverse DNS
     */
    public function test_can_change_server_reverse_dns(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->changeReverseDns('12345', [
            'dns_ptr' => 'new-hostname.example.com',
        ]);

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'change_reverse_dns', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['dns_ptr'] === 'new-hostname.example.com';
        });
    }

    /**
     * Test listing server actions
     */
    public function test_can_list_server_actions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->list('12345');

        $this->assertInstanceOf(
            ListActionsResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'list', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test listing server actions with query parameters
     */
    public function test_can_list_server_actions_with_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->list('12345', [
            'status' => 'running',
            'sort' => 'id:desc',
        ]);

        $this->assertInstanceOf(
            ListActionsResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'list', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['status'] === 'running' &&
                   $request['parameters']['sort'] === 'id:desc';
        });
    }

    /**
     * Test error handling with API exceptions
     */
    public function test_can_handle_server_api_exceptions(): void
    {
        $exception = new Exception('API Error: Server limit reached');
        $requests = [];
        $responses = [$exception];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('API Error: Server limit reached');

        $client->servers()->create(['name' => 'test-server']);
    }

    /**
     * Test error response handling
     */
    public function test_can_handle_server_error_responses(): void
    {
        $errorResponse = new Response(400, [], json_encode([
            'error' => [
                'code' => 'invalid_input',
                'message' => 'Server name already exists',
                'details' => [
                    'field' => 'name',
                    'value' => 'existing-server',
                ],
            ],
        ]) ?: '');

        $requests = [];
        $responses = [$errorResponse];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->create(['name' => 'existing-server']);

        $this->assertInstanceOf(
            CreateResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'servers', 'create');
    }

    /**
     * Test complex server management workflow
     */
    public function test_complete_server_management_workflow(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        // Create server
        $server = $client->servers()->create([
            'name' => 'production-web-server',
            'server_type' => 'cx21',
            'image' => 'ubuntu-22.04',
            'location' => 'nbg1',
            'ssh_keys' => [123, 456],
            'labels' => [
                'environment' => 'production',
                'role' => 'web',
            ],
        ]);

        // Update server
        $client->servers()->update('12345', [
            'name' => 'production-web-server-updated',
            'labels' => [
                'environment' => 'production',
                'role' => 'web',
                'updated' => 'true',
            ],
        ]);

        // Enable backups
        $client->servers()->actions()->enableBackups('12345');

        // Change protection settings
        $client->servers()->actions()->changeProtection('12345', [
            'delete' => true,
            'rebuild' => false,
        ]);

        // Get metrics
        $client->servers()->metrics('12345', [
            'type' => 'cpu',
            'start' => '2023-12-01T00:00:00+00:00',
            'end' => '2023-12-01T23:59:59+00:00',
        ]);

        // List actions
        $client->servers()->actions()->list('12345');

        // Assert all requests were made in correct order
        $this->assertRequestWasMade($requests, 'servers', 'create', function ($request) {
            return $request['parameters']['name'] === 'production-web-server' &&
                   $request['parameters']['server_type'] === 'cx21';
        });

        $this->assertRequestWasMade($requests, 'servers', 'update', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['name'] === 'production-web-server-updated';
        });

        $this->assertRequestWasMade($requests, 'server_actions', 'enable_backups', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });

        $this->assertRequestWasMade($requests, 'server_actions', 'change_protection', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['delete'] === true;
        });

        $this->assertRequestWasMade($requests, 'servers', 'metrics', function ($request) {
            return $request['parameters']['serverId'] === '12345' &&
                   $request['parameters']['type'] === 'cpu';
        });

        $this->assertRequestWasMade($requests, 'server_actions', 'list', function ($request) {
            return $request['parameters']['serverId'] === '12345';
        });

        // Assert total workflow requests
        $this->assertCount(6, $requests);
    }

    /**
     * Test server lifecycle workflow
     */
    public function test_server_lifecycle_workflow(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        // Create server
        $client->servers()->create([
            'name' => 'lifecycle-test-server',
            'server_type' => 'cx11',
            'image' => 'ubuntu-20.04',
        ]);

        // Power on server
        $client->servers()->actions()->powerOn('12345');

        // Wait for server to be running, then create backup image
        $client->servers()->actions()->createImage('12345', [
            'description' => 'Backup before maintenance',
            'type' => 'snapshot',
        ]);

        // Shutdown server for maintenance
        $client->servers()->actions()->shutdown('12345');

        // Rebuild server with new image
        $client->servers()->actions()->rebuild('12345', [
            'image' => 'ubuntu-22.04',
        ]);

        // Power on server after rebuild
        $client->servers()->actions()->powerOn('12345');

        // Delete server
        $client->servers()->delete('12345');

        // Assert all lifecycle requests were made
        $this->assertRequestWasMade($requests, 'servers', 'create');
        $this->assertRequestWasMade($requests, 'server_actions', 'power_on');
        $this->assertRequestWasMade($requests, 'server_actions', 'create_image');
        $this->assertRequestWasMade($requests, 'server_actions', 'shutdown');
        $this->assertRequestWasMade($requests, 'server_actions', 'rebuild');
        $this->assertRequestWasMade($requests, 'server_actions', 'power_on');
        $this->assertRequestWasMade($requests, 'servers', 'delete');

        $this->assertCount(7, $requests);
    }

    /**
     * Test using individual servers fake resource
     */
    public function test_using_individual_servers_fake(): void
    {
        $requests = [];
        $responses = [];
        $serversFake = $this->fakeServers($responses, $requests);

        // Test various server operations
        $serversFake->create(['name' => 'test-server']);
        $serversFake->list();
        $serversFake->retrieve('12345');
        $serversFake->update('12345', ['name' => 'updated-server']);
        $serversFake->delete('12345');
        $serversFake->metrics('12345');

        // Assert all requests were made
        $this->assertCount(6, $requests);

        // Test resource-specific assertions
        $serversFake->assertSent(function ($request) {
            return $request['method'] === 'create' &&
                   $request['parameters']['name'] === 'test-server';
        });

        $serversFake->assertSent(function ($request) {
            return $request['method'] === 'list';
        });

        $serversFake->assertSent(function ($request) {
            return $request['method'] === 'retrieve' &&
                   $request['parameters']['serverId'] === '12345';
        });

        $serversFake->assertSent(function ($request) {
            return $request['method'] === 'update' &&
                   $request['parameters']['name'] === 'updated-server';
        });

        $serversFake->assertSent(function ($request) {
            return $request['method'] === 'delete' &&
                   $request['parameters']['serverId'] === '12345';
        });

        $serversFake->assertSent(function ($request) {
            return $request['method'] === 'metrics' &&
                   $request['parameters']['serverId'] === '12345';
        });
    }

    /**
     * Test mixed response types for servers
     */
    public function test_can_handle_mixed_server_response_types(): void
    {
        $successResponse = new Response(200, [], json_encode(['servers' => []]) ?: '');
        $errorResponse = new Response(404, [], json_encode(['error' => 'Server not found']) ?: '');
        $exception = new Exception('Network timeout');

        $requests = [];
        $responses = [$successResponse, $errorResponse, $exception];
        $client = $this->fakeClient($responses, $requests);

        // First call succeeds
        $response1 = $client->servers()->list();
        $this->assertInstanceOf(
            ListResponse::class,
            $response1
        );

        // Second call returns error response
        $response2 = $client->servers()->list();
        $this->assertInstanceOf(
            ListResponse::class,
            $response2
        );

        // Third call throws exception
        $this->expectException(Exception::class);
        $client->servers()->list();

        // Verify all requests were made
        $this->assertCount(3, $requests);
    }

    /**
     * Test server actions with custom responses
     */
    public function test_server_actions_with_custom_responses(): void
    {
        $customActionResponse = new Response(201, [], json_encode([
            'action' => [
                'id' => 12345,
                'command' => 'power_on',
                'status' => 'running',
                'progress' => 0,
                'started' => '2023-12-01T10:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 67890,
                        'type' => 'server',
                    ],
                ],
                'error' => null,
            ],
        ]) ?: '');

        $requests = [];
        $responses = [$customActionResponse];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->powerOn('67890');

        $this->assertInstanceOf(
            ActionResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'power_on', function ($request) {
            return $request['parameters']['serverId'] === '67890';
        });
    }

    /**
     * Test server console response
     */
    public function test_server_console_response(): void
    {
        $consoleResponse = new Response(201, [], json_encode([
            'action' => [
                'id' => 12345,
                'command' => 'request_console',
                'status' => 'running',
                'progress' => 0,
                'started' => '2023-12-01T10:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 67890,
                        'type' => 'server',
                    ],
                ],
                'error' => null,
            ],
            'wss_url' => 'wss://console.hetzner.cloud/console/12345',
            'password' => 'console-password-123',
        ]) ?: '');

        $requests = [];
        $responses = [$consoleResponse];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->servers()->actions()->requestConsole('67890');

        $this->assertInstanceOf(
            ConsoleResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'server_actions', 'request_console', function ($request) {
            return $request['parameters']['serverId'] === '67890';
        });
    }
}
