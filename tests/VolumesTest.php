<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\VolumeActions\Action;
use Boci\HetznerLaravel\Responses\VolumeActions\ActionResponse;
use Boci\HetznerLaravel\Responses\VolumeActions\ListActionsResponse;
use Boci\HetznerLaravel\Responses\Volumes\CreateResponse;
use Boci\HetznerLaravel\Responses\Volumes\DeleteResponse;
use Boci\HetznerLaravel\Responses\Volumes\ListResponse;
use Boci\HetznerLaravel\Responses\Volumes\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Volumes\UpdateResponse;
use Boci\HetznerLaravel\Responses\Volumes\Volume;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Volumes Test Suite
 *
 * This test suite covers all functionality related to the Volumes resource,
 * including listing, creating, getting, updating, deleting volumes and their actions.
 */
final class VolumesTest extends TestCase
{
    /**
     * Test listing volumes with fake data
     */
    public function test_can_list_volumes_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->volumes()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertIsArray($response->volumes());
        $this->assertCount(2, $response->volumes());

        $volumes = $response->volumes();
        $this->assertInstanceOf(Volume::class, $volumes[0]);
        $this->assertEquals(1, $volumes[0]->id());
        $this->assertEquals('my-volume', $volumes[0]->name());
        $this->assertEquals('available', $volumes[0]->status());
        $this->assertNull($volumes[0]->server());
        $this->assertEquals(10, $volumes[0]->size());
        $this->assertEquals('/dev/disk/by-id/scsi-0HC_Volume_1', $volumes[0]->linuxDevice());
        $this->assertIsArray($volumes[0]->location());
        $this->assertIsArray($volumes[0]->protection());
        $this->assertIsArray($volumes[0]->labels());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $volumes[0]->created());

        $this->assertInstanceOf(Volume::class, $volumes[1]);
        $this->assertEquals(2, $volumes[1]->id());
        $this->assertEquals('another-volume', $volumes[1]->name());
        $this->assertEquals('creating', $volumes[1]->status());
        $this->assertEquals(42, $volumes[1]->server());
        $this->assertEquals(20, $volumes[1]->size());

        $this->assertRequestWasMade($requests, 'volumes', 'list');
    }

    /**
     * Test listing volumes with custom response
     */
    public function test_can_list_volumes_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'volumes' => [
                    [
                        'id' => 42,
                        'name' => 'custom-volume',
                        'status' => 'available',
                        'server' => 123,
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
                        'size' => 50,
                        'linux_device' => '/dev/disk/by-id/scsi-0HC_Volume_42',
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

        $response = $client->volumes()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->volumes());

        $volume = $response->volumes()[0];
        $this->assertEquals(42, $volume->id());
        $this->assertEquals('custom-volume', $volume->name());
        $this->assertEquals('available', $volume->status());
        $this->assertEquals(123, $volume->server());
        $this->assertEquals(50, $volume->size());
        $this->assertEquals('production', $volume->labels()['environment']);
        $this->assertEquals('infrastructure', $volume->labels()['team']);

        $this->assertRequestWasMade($requests, 'volumes', 'list');
    }

    /**
     * Test listing volumes with query parameters
     */
    public function test_can_list_volumes_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'my-volume',
        ];

        $response = $client->volumes()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);

        $this->assertRequestWasMade($requests, 'volumes', 'list');
    }

    /**
     * Test creating a volume
     */
    public function test_can_create_volume(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'new-volume',
            'size' => 20,
            'location' => 'fsn1',
            'labels' => [
                'environment' => 'production',
                'team' => 'backend',
            ],
        ];

        $response = $client->volumes()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $this->assertInstanceOf(Volume::class, $response->volume());
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\ServerActions\Action::class, $response->action());

        $volume = $response->volume();
        $this->assertEquals(1, $volume->id());
        $this->assertEquals('new-volume', $volume->name());
        $this->assertEquals('creating', $volume->status());
        $this->assertEquals(20, $volume->size());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('create_volume', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'volumes', 'create');
    }

    /**
     * Test creating volume with custom response
     */
    public function test_can_create_volume_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'volume' => [
                    'id' => 99,
                    'name' => 'custom-new-volume',
                    'status' => 'creating',
                    'server' => null,
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
                    'size' => 100,
                    'linux_device' => '/dev/disk/by-id/scsi-0HC_Volume_99',
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
                    'command' => 'create_volume',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2023-06-20T09:15:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 99,
                            'type' => 'volume',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'custom-new-volume',
            'size' => 100,
            'location' => 'fsn1',
        ];

        $response = $client->volumes()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $volume = $response->volume();

        $this->assertEquals(99, $volume->id());
        $this->assertEquals('custom-new-volume', $volume->name());
        $this->assertEquals('creating', $volume->status());
        $this->assertEquals(100, $volume->size());
        $this->assertEquals('development', $volume->labels()['environment']);
        $this->assertEquals('frontend', $volume->labels()['team']);

        $this->assertRequestWasMade($requests, 'volumes', 'create');
    }

    /**
     * Test getting a specific volume
     */
    public function test_can_get_specific_volume(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $volumeId = '42';
        $response = $client->volumes()->retrieve($volumeId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $this->assertInstanceOf(Volume::class, $response->volume());

        $volume = $response->volume();
        $this->assertEquals(42, $volume->id());
        $this->assertEquals('test-volume', $volume->name());
        $this->assertEquals('available', $volume->status());
        $this->assertNull($volume->server());
        $this->assertEquals(10, $volume->size());
        $this->assertEquals('/dev/disk/by-id/scsi-0HC_Volume_1', $volume->linuxDevice());
        $this->assertIsArray($volume->location());
        $this->assertIsArray($volume->protection());
        $this->assertIsArray($volume->labels());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $volume->created());

        $this->assertRequestWasMade($requests, 'volumes', 'get');
    }

    /**
     * Test getting volume with custom response
     */
    public function test_can_get_volume_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'volume' => [
                    'id' => 123,
                    'name' => 'detailed-volume',
                    'status' => 'available',
                    'server' => 456,
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
                    'size' => 50,
                    'linux_device' => '/dev/disk/by-id/scsi-0HC_Volume_123',
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

        $volumeId = '123';
        $response = $client->volumes()->retrieve($volumeId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $volume = $response->volume();

        $this->assertEquals(123, $volume->id());
        $this->assertEquals('detailed-volume', $volume->name());
        $this->assertEquals('available', $volume->status());
        $this->assertEquals(456, $volume->server());
        $this->assertEquals(50, $volume->size());
        $this->assertEquals('production', $volume->labels()['environment']);
        $this->assertEquals('infrastructure', $volume->labels()['team']);
        $this->assertEquals('web-app', $volume->labels()['project']);

        $this->assertRequestWasMade($requests, 'volumes', 'get');
    }

    /**
     * Test updating a volume
     */
    public function test_can_update_volume(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $volumeId = '42';
        $parameters = [
            'name' => 'updated-volume',
            'labels' => [
                'environment' => 'staging',
                'team' => 'frontend',
            ],
        ];

        $response = $client->volumes()->update($volumeId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);
        $this->assertInstanceOf(Volume::class, $response->volume());

        $volume = $response->volume();
        $this->assertEquals(42, $volume->id());
        $this->assertEquals('updated-volume', $volume->name());
        $this->assertEquals('staging', $volume->labels()['environment']);
        $this->assertEquals('frontend', $volume->labels()['team']);

        $this->assertRequestWasMade($requests, 'volumes', 'update');
    }

    /**
     * Test deleting a volume
     */
    public function test_can_delete_volume(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $volumeId = '42';
        $response = $client->volumes()->delete($volumeId);

        $this->assertInstanceOf(DeleteResponse::class, $response);
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\ServerActions\Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('delete_volume', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'volumes', 'delete');
    }

    /**
     * Test volume response structure
     */
    public function test_volume_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->volumes()->list();
        $volumes = $response->volumes();

        foreach ($volumes as $volume) {
            // Test all volume properties
            $this->assertIsInt($volume->id());
            $this->assertIsString($volume->name());
            $this->assertIsString($volume->status());
            $this->assertIsInt($volume->size());
            $this->assertIsString($volume->linuxDevice());
            $this->assertIsArray($volume->location());
            $this->assertIsArray($volume->protection());
            $this->assertIsArray($volume->labels());
            $this->assertIsString($volume->created());

            // Test volume status values
            $this->assertContains($volume->status(), ['available', 'creating', 'deleting']);

            // Test volume size values
            $this->assertGreaterThan(0, $volume->size());
            $this->assertLessThanOrEqual(10240, $volume->size()); // Max 10TB

            // Test location structure
            $location = $volume->location();
            $this->assertArrayHasKey('id', $location);
            $this->assertArrayHasKey('name', $location);
            $this->assertArrayHasKey('description', $location);
            $this->assertArrayHasKey('country', $location);
            $this->assertArrayHasKey('city', $location);
            $this->assertArrayHasKey('latitude', $location);
            $this->assertArrayHasKey('longitude', $location);
            $this->assertArrayHasKey('network_zone', $location);

            // Test protection structure
            $protection = $volume->protection();
            $this->assertArrayHasKey('delete', $protection);
            $this->assertIsBool($protection['delete']);

            // Test labels structure
            foreach ($volume->labels() as $key => $value) {
                $this->assertIsString($key);
                $this->assertIsString($value);
            }

            // Test created date format
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/', $volume->created());
        }
    }

    /**
     * Test handling volume API exception
     */
    public function test_can_handle_volume_api_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Volume not found', new Request('GET', '/volumes/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Volume not found');

        $client->volumes()->retrieve('999');
    }

    /**
     * Test handling volume list exception
     */
    public function test_can_handle_volume_list_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Rate limit exceeded', new Request('GET', '/volumes')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->volumes()->list();
    }

    /**
     * Test handling mixed volume response types
     */
    public function test_can_handle_mixed_volume_response_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'volumes' => [
                    [
                        'id' => 1,
                        'name' => 'my-volume',
                        'status' => 'available',
                        'server' => null,
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
                        'size' => 10,
                        'linux_device' => '/dev/disk/by-id/scsi-0HC_Volume_1',
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
            new RequestException('Volume not found', new Request('GET', '/volumes/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $listResponse = $client->volumes()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->volumes());

        // Second call should throw exception
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Volume not found');
        $client->volumes()->retrieve('999');
    }

    /**
     * Test using individual volume fake
     */
    public function test_using_individual_volume_fake(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $volumesFake = $client->volumes();

        $response = $volumesFake->list(['page' => 1, 'per_page' => 5]);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(2, $response->volumes());

        $volumesFake->assertSent(function (array $request) {
            return $request['resource'] === 'volumes' &&
                   $request['method'] === 'list' &&
                   isset($request['parameters']['page']) &&
                   $request['parameters']['page'] === 1 &&
                   isset($request['parameters']['per_page']) &&
                   $request['parameters']['per_page'] === 5;
        });
    }

    /**
     * Test volume fake assert not sent
     */
    public function test_volume_fake_assert_not_sent(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $volumesFake = $client->volumes();

        // No requests made yet
        $volumesFake->assertNotSent();

        // Make a request
        $volumesFake->list();

        // Now it should fail
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Requests were sent to volumes.');
        $volumesFake->assertNotSent();
    }

    /**
     * Test volume workflow simulation
     */
    public function test_volume_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List volumes response
            new Response(200, [], json_encode([
                'volumes' => [
                    [
                        'id' => 1,
                        'name' => 'my-volume',
                        'status' => 'available',
                        'server' => null,
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
                        'size' => 10,
                        'linux_device' => '/dev/disk/by-id/scsi-0HC_Volume_1',
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
            // Get specific volume response
            new Response(200, [], json_encode([
                'volume' => [
                    'id' => 1,
                    'name' => 'my-volume',
                    'status' => 'available',
                    'server' => null,
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
                    'size' => 10,
                    'linux_device' => '/dev/disk/by-id/scsi-0HC_Volume_1',
                    'protection' => [
                        'delete' => false,
                    ],
                    'labels' => [],
                    'created' => '2023-01-01T00:00:00+00:00',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list volumes, then get details of the first one
        $listResponse = $client->volumes()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->volumes());

        $firstVolume = $listResponse->volumes()[0];
        $volumeId = (string) $firstVolume->id();

        $getResponse = $client->volumes()->retrieve($volumeId);
        $this->assertInstanceOf(RetrieveResponse::class, $getResponse);
        $this->assertEquals($firstVolume->id(), $getResponse->volume()->id());
        $this->assertEquals($firstVolume->name(), $getResponse->volume()->name());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'volumes', 'list');
        $this->assertRequestWasMade($requests, 'volumes', 'get');
    }

    /**
     * Test volume error response handling
     */
    public function test_volume_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Volume not found', new Request('GET', '/volumes/nonexistent')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Volume not found');
        $client->volumes()->retrieve('nonexistent');
    }

    /**
     * Test volume empty list response
     */
    public function test_volume_empty_list_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'volumes' => [],
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

        $response = $client->volumes()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(0, $response->volumes());
        $this->assertEmpty($response->volumes());
    }

    /**
     * Test volume pagination
     */
    public function test_volume_pagination(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->volumes()->list();
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
        $this->assertEquals(2, $pagination['total']);
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
     * Test volume to array conversion
     */
    public function test_volume_to_array_conversion(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->volumes()->list();
        $volumes = $response->volumes();

        foreach ($volumes as $volume) {
            $array = $volume->toArray();

            $this->assertIsArray($array);
            $this->assertArrayHasKey('id', $array);
            $this->assertArrayHasKey('name', $array);
            $this->assertArrayHasKey('status', $array);
            $this->assertArrayHasKey('size', $array);
            $this->assertArrayHasKey('linux_device', $array);
            $this->assertArrayHasKey('location', $array);
            $this->assertArrayHasKey('protection', $array);
            $this->assertArrayHasKey('labels', $array);
            $this->assertArrayHasKey('created', $array);

            $this->assertEquals($volume->id(), $array['id']);
            $this->assertEquals($volume->name(), $array['name']);
            $this->assertEquals($volume->status(), $array['status']);
            $this->assertEquals($volume->size(), $array['size']);
        }
    }

    /**
     * Test volume actions - list actions
     */
    public function test_can_list_volume_actions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $volumeId = '42';
        $response = $client->volumes()->actions()->list($volumeId);

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertIsArray($response->actions());
        $this->assertCount(2, $response->actions());

        $actions = $response->actions();
        $this->assertInstanceOf(Action::class, $actions[0]);
        $this->assertEquals(1, $actions[0]->id());
        $this->assertEquals('attach_volume', $actions[0]->command());
        $this->assertEquals('success', $actions[0]->status());
        $this->assertEquals(100, $actions[0]->progress());

        $this->assertInstanceOf(Action::class, $actions[1]);
        $this->assertEquals(2, $actions[1]->id());
        $this->assertEquals('resize_volume', $actions[1]->command());
        $this->assertEquals('running', $actions[1]->status());
        $this->assertEquals(50, $actions[1]->progress());

        $this->assertRequestWasMade($requests, 'volume_actions', 'list');
    }

    /**
     * Test volume actions - get action
     */
    public function test_can_get_volume_action(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $volumeId = '42';
        $actionId = '123';
        $response = $client->volumes()->actions()->retrieve($volumeId, $actionId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(123, $action->id());
        $this->assertEquals('attach_volume', $action->command());
        $this->assertEquals('running', $action->status());
        $this->assertEquals(0, $action->progress());

        $this->assertRequestWasMade($requests, 'volume_actions', 'get');
    }

    /**
     * Test volume actions - attach volume
     */
    public function test_can_attach_volume(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $volumeId = '42';
        $parameters = [
            'server' => 123,
            'automount' => true,
        ];

        $response = $client->volumes()->actions()->attach($volumeId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('attach_volume', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'volume_actions', 'attach');
    }

    /**
     * Test volume actions - detach volume
     */
    public function test_can_detach_volume(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $volumeId = '42';
        $response = $client->volumes()->actions()->detach($volumeId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('detach_volume', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'volume_actions', 'detach');
    }

    /**
     * Test volume actions - resize volume
     */
    public function test_can_resize_volume(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $volumeId = '42';
        $parameters = [
            'size' => 50,
        ];

        $response = $client->volumes()->actions()->resize($volumeId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('resize_volume', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'volume_actions', 'resize');
    }

    /**
     * Test volume actions - change protection
     */
    public function test_can_change_volume_protection(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $volumeId = '42';
        $parameters = [
            'delete' => true,
        ];

        $response = $client->volumes()->actions()->changeProtection($volumeId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('change_protection', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'volume_actions', 'change_protection');
    }

    /**
     * Test volume actions workflow simulation
     */
    public function test_volume_actions_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // Attach volume response
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 1,
                    'command' => 'attach_volume',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2023-01-01T00:00:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 42,
                            'type' => 'volume',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
            // Resize volume response
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 2,
                    'command' => 'resize_volume',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2023-01-01T00:01:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 42,
                            'type' => 'volume',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $volumeId = '42';

        // Simulate a workflow: attach volume, then resize it
        $attachResponse = $client->volumes()->actions()->attach($volumeId, ['server' => 123]);
        $this->assertInstanceOf(ActionResponse::class, $attachResponse);
        $this->assertEquals('attach_volume', $attachResponse->action()->command());

        $resizeResponse = $client->volumes()->actions()->resize($volumeId, ['size' => 50]);
        $this->assertInstanceOf(ActionResponse::class, $resizeResponse);
        $this->assertEquals('resize_volume', $resizeResponse->action()->command());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'volume_actions', 'attach');
        $this->assertRequestWasMade($requests, 'volume_actions', 'resize');
    }

    /**
     * Test volume location validation
     */
    public function test_volume_location_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->volumes()->list();
        $volumes = $response->volumes();

        foreach ($volumes as $volume) {
            $location = $volume->location();

            // Test location structure
            $this->assertIsInt($location['id']);
            $this->assertIsString($location['name']);
            $this->assertIsString($location['description']);
            $this->assertIsString($location['country']);
            $this->assertIsString($location['city']);
            $this->assertIsFloat($location['latitude']);
            $this->assertIsFloat($location['longitude']);
            $this->assertIsString($location['network_zone']);

            // Test location values
            $this->assertGreaterThan(0, $location['id']);
            $this->assertNotEmpty($location['name']);
            $this->assertNotEmpty($location['country']);
            $this->assertNotEmpty($location['city']);
            $this->assertGreaterThanOrEqual(-90, $location['latitude']);
            $this->assertLessThanOrEqual(90, $location['latitude']);
            $this->assertGreaterThanOrEqual(-180, $location['longitude']);
            $this->assertLessThanOrEqual(180, $location['longitude']);
        }
    }

    /**
     * Test volume size validation
     */
    public function test_volume_size_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->volumes()->list();
        $volumes = $response->volumes();

        foreach ($volumes as $volume) {
            $size = $volume->size();

            // Test volume size constraints
            $this->assertGreaterThan(0, $size);
            $this->assertLessThanOrEqual(10240, $size); // Max 10TB

            // Test that size is a multiple of 1GB
            $this->assertEquals(0, $size % 1);
        }
    }
}
