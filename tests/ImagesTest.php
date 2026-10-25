<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\ImageActions\ActionResponse;
use Boci\HetznerLaravel\Responses\ImageActions\ListActionsResponse;
use Boci\HetznerLaravel\Responses\Images\DeleteResponse;
use Boci\HetznerLaravel\Responses\Images\Image;
use Boci\HetznerLaravel\Responses\Images\ListResponse;
use Boci\HetznerLaravel\Responses\Images\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Images\UpdateResponse;
use Boci\HetznerLaravel\Testing\ImagesFake;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Psr7\Response;

/**
 * Images Test Suite
 *
 * This test suite covers all functionality related to the Images resource,
 * including listing, getting, updating, deleting images, and managing image actions.
 */
final class ImagesTest extends TestCase
{
    /**
     * Test listing images with fake data
     */
    public function test_can_list_images_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->images()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->images());

        $images = $response->images();
        $this->assertInstanceOf(Image::class, $images[0]);
        $this->assertEquals(1, $images[0]->id());
        $this->assertEquals('test-image', $images[0]->name());
        $this->assertEquals('snapshot', $images[0]->type());
        $this->assertEquals('available', $images[0]->status());

        $this->assertRequestWasMade($requests, 'images', 'list');
    }

    /**
     * Test listing images with custom response
     */
    public function test_can_list_images_with_custom_response(): void
    {
        $customData = [
            'images' => [
                [
                    'id' => 1,
                    'type' => 'snapshot',
                    'status' => 'available',
                    'name' => 'web-server-snapshot',
                    'description' => 'Snapshot of web server',
                    'image_size' => 2.3,
                    'disk_size' => 20,
                    'created' => '2023-01-01T00:00:00+00:00',
                    'created_from' => [
                        'id' => 1,
                        'name' => 'web-server',
                    ],
                    'bound_to' => null,
                    'os_flavor' => 'ubuntu',
                    'os_version' => '22.04',
                    'rapid_deploy' => false,
                    'protection' => [
                        'delete' => false,
                    ],
                    'deprecated' => null,
                    'labels' => ['environment' => 'production'],
                ],
                [
                    'id' => 2,
                    'type' => 'backup',
                    'status' => 'available',
                    'name' => 'database-backup',
                    'description' => 'Database backup image',
                    'image_size' => 5.7,
                    'disk_size' => 50,
                    'created' => '2023-01-01T01:00:00+00:00',
                    'created_from' => [
                        'id' => 2,
                        'name' => 'database-server',
                    ],
                    'bound_to' => null,
                    'os_flavor' => 'ubuntu',
                    'os_version' => '20.04',
                    'rapid_deploy' => true,
                    'protection' => [
                        'delete' => true,
                    ],
                    'deprecated' => null,
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

        $response = $client->images()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(2, $response->images());

        $images = $response->images();
        $this->assertEquals('web-server-snapshot', $images[0]->name());
        $this->assertEquals('database-backup', $images[1]->name());

        $this->assertEquals('snapshot', $images[0]->type());
        $this->assertEquals('backup', $images[1]->type());

        $this->assertEquals(2.3, $images[0]->imageSize());
        $this->assertEquals(5.7, $images[1]->imageSize());

        $this->assertRequestWasMade($requests, 'images', 'list');
    }

    /**
     * Test listing images with query parameters
     */
    public function test_can_list_images_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'type' => 'snapshot',
            'status' => 'available',
            'name' => 'web-server',
        ];

        $response = $client->images()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertRequestWasMade($requests, 'images', 'list');
    }

    /**
     * Test getting a specific image
     */
    public function test_can_get_specific_image(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $imageId = '123';
        $response = $client->images()->retrieve($imageId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);

        $image = $response->image();
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals(123, $image->id());
        $this->assertEquals('test-image-123', $image->name());

        $this->assertRequestWasMade($requests, 'images', 'retrieve');
    }

    /**
     * Test getting a specific image with custom response
     */
    public function test_can_get_specific_image_with_custom_response(): void
    {
        $customData = [
            'image' => [
                'id' => 456,
                'type' => 'snapshot',
                'status' => 'available',
                'name' => 'production-server-snapshot',
                'description' => 'Production server snapshot with all configurations',
                'image_size' => 8.5,
                'disk_size' => 100,
                'created' => '2023-01-01T10:00:00+00:00',
                'created_from' => [
                    'id' => 789,
                    'name' => 'production-server',
                ],
                'bound_to' => null,
                'os_flavor' => 'ubuntu',
                'os_version' => '22.04',
                'rapid_deploy' => true,
                'protection' => [
                    'delete' => true,
                ],
                'deprecated' => null,
                'labels' => ['environment' => 'production', 'team' => 'backend', 'critical' => 'true'],
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $imageId = '456';
        $response = $client->images()->retrieve($imageId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);

        $image = $response->image();
        $this->assertEquals(456, $image->id());
        $this->assertEquals('production-server-snapshot', $image->name());
        $this->assertEquals('snapshot', $image->type());
        $this->assertEquals('available', $image->status());

        $this->assertEquals(8.5, $image->imageSize());
        $this->assertEquals(100, $image->diskSize());
        $this->assertEquals('ubuntu', $image->osFlavor());
        $this->assertEquals('22.04', $image->osVersion());
        $this->assertTrue($image->rapidDeploy());

        $protection = $image->protection();
        $this->assertTrue($protection['delete']);

        $labels = $image->labels();
        $this->assertEquals('production', $labels['environment']);
        $this->assertEquals('backend', $labels['team']);
        $this->assertEquals('true', $labels['critical']);

        $this->assertRequestWasMade($requests, 'images', 'retrieve');
    }

    /**
     * Test updating an image
     */
    public function test_can_update_image(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $imageId = '123';
        $parameters = [
            'name' => 'updated-image-name',
            'description' => 'Updated image description',
            'labels' => ['environment' => 'staging', 'updated' => 'true'],
        ];

        $response = $client->images()->update($imageId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);

        $image = $response->image();
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals(123, $image->id());
        $this->assertEquals('updated-image-name', $image->name());

        $this->assertRequestWasMade($requests, 'images', 'update');
    }

    /**
     * Test updating an image with custom response
     */
    public function test_can_update_image_with_custom_response(): void
    {
        $customData = [
            'image' => [
                'id' => 789,
                'type' => 'snapshot',
                'status' => 'available',
                'name' => 'updated-production-snapshot',
                'description' => 'Updated production snapshot with new configurations',
                'image_size' => 8.5,
                'disk_size' => 100,
                'created' => '2023-01-01T10:00:00+00:00',
                'created_from' => [
                    'id' => 789,
                    'name' => 'production-server',
                ],
                'bound_to' => null,
                'os_flavor' => 'ubuntu',
                'os_version' => '22.04',
                'rapid_deploy' => true,
                'protection' => [
                    'delete' => true,
                ],
                'deprecated' => null,
                'labels' => ['environment' => 'production', 'updated' => 'true', 'version' => '2.0'],
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $imageId = '789';
        $parameters = [
            'name' => 'updated-production-snapshot',
            'description' => 'Updated production snapshot with new configurations',
            'labels' => ['environment' => 'production', 'updated' => 'true', 'version' => '2.0'],
        ];

        $response = $client->images()->update($imageId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);

        $image = $response->image();
        $this->assertEquals(789, $image->id());
        $this->assertEquals('updated-production-snapshot', $image->name());

        $labels = $image->labels();
        $this->assertEquals('production', $labels['environment']);
        $this->assertEquals('true', $labels['updated']);
        $this->assertEquals('2.0', $labels['version']);

        $this->assertRequestWasMade($requests, 'images', 'update');
    }

    /**
     * Test deleting an image
     */
    public function test_can_delete_image(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $imageId = '123';
        $response = $client->images()->delete($imageId);

        $this->assertInstanceOf(DeleteResponse::class, $response);

        $action = $response->action();
        $this->assertEquals('delete_image', $action->command());
        $this->assertEquals('success', $action->status());

        $this->assertRequestWasMade($requests, 'images', 'delete');
    }

    /**
     * Test image response structure validation
     */
    public function test_image_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->images()->list();
        $images = $response->images();

        foreach ($images as $image) {
            $this->assertIsInt($image->id());
            $this->assertIsString($image->name());
            $this->assertIsString($image->type());
            $this->assertIsString($image->status());
            $this->assertIsString($image->created());
            $this->assertIsArray($image->labels());

            // Optional fields
            $this->assertIsString($image->description());
            $this->assertIsFloat($image->imageSize());
            $this->assertIsFloat($image->diskSize());
            $this->assertIsString($image->osFlavor());
            $this->assertIsString($image->osVersion());
            $this->assertIsBool($image->rapidDeploy());
            $this->assertIsArray($image->protection());
        }
    }

    /**
     * Test image types and statuses
     */
    public function test_image_types_and_statuses(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->images()->list();
        $images = $response->images();

        foreach ($images as $image) {
            $type = $image->type();
            $this->assertContains($type, ['snapshot', 'backup', 'system']);

            $status = $image->status();
            $this->assertContains($status, ['available', 'creating', 'unavailable']);
        }
    }

    /**
     * Test image protection structure
     */
    public function test_image_protection_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->images()->list();
        $images = $response->images();

        foreach ($images as $image) {
            $protection = $image->protection();
            $this->assertIsArray($protection);
            $this->assertArrayHasKey('delete', $protection);
            $this->assertIsBool($protection['delete']);
        }
    }

    /**
     * Test handling API exceptions
     */
    public function test_can_handle_images_api_exceptions(): void
    {
        $exception = new \Exception('API connection failed');

        $requests = [];
        $responses = [$exception];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API connection failed');

        $client->images()->list();
    }

    /**
     * Test handling error responses
     */
    public function test_can_handle_images_error_responses(): void
    {
        $errorResponse = new Response(400, [], json_encode([
            'error' => [
                'code' => 'invalid_request',
                'message' => 'Invalid image parameters provided',
            ],
        ]) ?: '');

        $requests = [];
        $responses = [$errorResponse];
        $client = $this->fakeClient($responses, $requests);

        // The fake client will return the error response as-is
        $response = $client->images()->update('123', ['name' => 'invalid']);
        $this->assertInstanceOf(UpdateResponse::class, $response);

        $this->assertRequestWasMade($requests, 'images', 'update');
    }

    /**
     * Test using individual images fake
     */
    public function test_using_individual_images_fake(): void
    {
        $responses = [];
        $requests = [];

        $imagesFake = new ImagesFake($responses, $requests);

        // Test various image operations
        $imagesFake->list();
        $imagesFake->retrieve('123');
        $imagesFake->update('123', ['name' => 'updated']);
        $imagesFake->delete('123');

        // Assert all requests were made
        $this->assertCount(4, $requests);

        // Test resource-specific assertions
        $imagesFake->assertSent(function ($request) {
            return $request['method'] === 'list';
        });

        $imagesFake->assertSent(function ($request) {
            return $request['method'] === 'retrieve' &&
                   $request['parameters']['imageId'] === '123';
        });

        $imagesFake->assertSent(function ($request) {
            return $request['method'] === 'update' &&
                   $request['parameters']['name'] === 'updated';
        });
    }

    /**
     * Test mixed response types for images
     */
    public function test_can_handle_mixed_images_response_types(): void
    {
        $responses = [
            new Response(200, [], json_encode([
                'images' => [
                    [
                        'id' => 1,
                        'type' => 'snapshot',
                        'status' => 'available',
                        'name' => 'test-image',
                        'description' => 'Test image',
                        'image_size' => 2.3,
                        'disk_size' => 10,
                        'created' => '2023-01-01T00:00:00+00:00',
                        'created_from' => [
                            'id' => 1,
                            'name' => 'test-server',
                        ],
                        'bound_to' => null,
                        'os_flavor' => 'ubuntu',
                        'os_version' => '20.04',
                        'rapid_deploy' => false,
                        'protection' => [
                            'delete' => false,
                        ],
                        'deprecated' => null,
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
        $response = $client->images()->list();
        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->images());

        // Second call should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Network timeout');
        $client->images()->retrieve('456');
    }

    /**
     * Test images with complex parameters
     */
    public function test_can_list_images_with_complex_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 3,
            'per_page' => 50,
            'type' => 'snapshot',
            'status' => 'available',
            'sort' => 'id:desc',
            'name' => 'production',
        ];

        $response = $client->images()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertRequestWasMade($requests, 'images', 'list');
    }

    /**
     * Test image workflow simulation
     */
    public function test_image_workflow_simulation(): void
    {
        $responses = [
            // Get image
            new Response(200, [], json_encode([
                'image' => [
                    'id' => 1,
                    'type' => 'snapshot',
                    'status' => 'available',
                    'name' => 'web-server-snapshot',
                    'description' => 'Web server snapshot',
                    'image_size' => 2.3,
                    'disk_size' => 20,
                    'created' => '2023-01-01T10:00:00+00:00',
                    'created_from' => [
                        'id' => 1,
                        'name' => 'web-server',
                    ],
                    'bound_to' => null,
                    'os_flavor' => 'ubuntu',
                    'os_version' => '22.04',
                    'rapid_deploy' => false,
                    'protection' => [
                        'delete' => false,
                    ],
                    'deprecated' => null,
                    'labels' => ['environment' => 'production'],
                ],
            ]) ?: ''),
            // Update image
            new Response(200, [], json_encode([
                'image' => [
                    'id' => 1,
                    'type' => 'snapshot',
                    'status' => 'available',
                    'name' => 'updated-web-server-snapshot',
                    'description' => 'Updated web server snapshot',
                    'image_size' => 2.3,
                    'disk_size' => 20,
                    'created' => '2023-01-01T10:00:00+00:00',
                    'created_from' => [
                        'id' => 1,
                        'name' => 'web-server',
                    ],
                    'bound_to' => null,
                    'os_flavor' => 'ubuntu',
                    'os_version' => '22.04',
                    'rapid_deploy' => false,
                    'protection' => [
                        'delete' => false,
                    ],
                    'deprecated' => null,
                    'labels' => ['environment' => 'production', 'updated' => 'true'],
                ],
            ]) ?: ''),
        ];

        $requests = [];
        $client = $this->fakeClient($responses, $requests);

        // Simulate workflow: get image details, then update
        $getResponse = $client->images()->retrieve('1');
        $image = $getResponse->image();
        $this->assertEquals(1, $image->id());
        $this->assertEquals('web-server-snapshot', $image->name());

        $updateResponse = $client->images()->update('1', [
            'name' => 'updated-web-server-snapshot',
            'description' => 'Updated web server snapshot',
            'labels' => ['environment' => 'production', 'updated' => 'true'],
        ]);
        $updatedImage = $updateResponse->image();
        $this->assertEquals('updated-web-server-snapshot', $updatedImage->name());

        // Verify all requests were made
        $this->assertRequestWasMade($requests, 'images', 'retrieve');
        $this->assertRequestWasMade($requests, 'images', 'update');
    }

    /**
     * Test images resource assertions
     */
    public function test_images_resource_assertions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $client->images()->list(['type' => 'snapshot']);
        $client->images()->retrieve('999');
        $client->images()->update('999', ['name' => 'updated']);
        $client->images()->delete('999');

        // Test that specific requests were made
        $this->assertRequestWasMade($requests, 'images', 'list');
        $this->assertRequestWasMade($requests, 'images', 'retrieve');
        $this->assertRequestWasMade($requests, 'images', 'update');
        $this->assertRequestWasMade($requests, 'images', 'delete');

        // Test that no other requests were made
        $this->assertNoRequestWasMade($requests, 'servers');
        $this->assertNoRequestWasMade($requests, 'certificates');
    }

    /**
     * Test images with specific image data
     */
    public function test_images_with_specific_image_data(): void
    {
        $customData = [
            'image' => [
                'id' => 200,
                'type' => 'backup',
                'status' => 'available',
                'name' => 'database-backup-image',
                'description' => 'Complete database backup with all data',
                'image_size' => 15.7,
                'disk_size' => 200,
                'created' => '2023-01-01T15:00:00+00:00',
                'created_from' => [
                    'id' => 100,
                    'name' => 'database-server',
                ],
                'bound_to' => null,
                'os_flavor' => 'ubuntu',
                'os_version' => '20.04',
                'rapid_deploy' => true,
                'protection' => [
                    'delete' => true,
                ],
                'deprecated' => null,
                'labels' => ['environment' => 'production', 'type' => 'database', 'team' => 'backend'],
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->images()->retrieve('200');
        $image = $response->image();

        $this->assertEquals(200, $image->id());
        $this->assertEquals('database-backup-image', $image->name());
        $this->assertEquals('backup', $image->type());
        $this->assertEquals('available', $image->status());
        $this->assertEquals('2023-01-01T15:00:00+00:00', $image->created());

        $this->assertEquals(15.7, $image->imageSize());
        $this->assertEquals(200, $image->diskSize());
        $this->assertEquals('ubuntu', $image->osFlavor());
        $this->assertEquals('20.04', $image->osVersion());
        $this->assertTrue($image->rapidDeploy());

        $protection = $image->protection();
        $this->assertTrue($protection['delete']);

        $labels = $image->labels();
        $this->assertEquals('production', $labels['environment']);
        $this->assertEquals('database', $labels['type']);
        $this->assertEquals('backend', $labels['team']);

        $this->assertRequestWasMade($requests, 'images', 'retrieve');
    }

    /**
     * Test image with different types
     */
    public function test_image_with_different_types(): void
    {
        $customData = [
            'images' => [
                [
                    'id' => 1,
                    'type' => 'snapshot',
                    'status' => 'available',
                    'name' => 'snapshot-image',
                    'description' => 'Server snapshot',
                    'image_size' => 2.3,
                    'disk_size' => 20,
                    'created' => '2023-01-01T00:00:00+00:00',
                    'created_from' => [
                        'id' => 1,
                        'name' => 'test-server',
                    ],
                    'bound_to' => null,
                    'os_flavor' => 'ubuntu',
                    'os_version' => '22.04',
                    'rapid_deploy' => false,
                    'protection' => [
                        'delete' => false,
                    ],
                    'deprecated' => null,
                    'labels' => ['type' => 'snapshot'],
                ],
                [
                    'id' => 2,
                    'type' => 'backup',
                    'status' => 'available',
                    'name' => 'backup-image',
                    'description' => 'Server backup',
                    'image_size' => 5.7,
                    'disk_size' => 50,
                    'created' => '2023-01-01T00:00:00+00:00',
                    'created_from' => [
                        'id' => 2,
                        'name' => 'test-server',
                    ],
                    'bound_to' => null,
                    'os_flavor' => 'ubuntu',
                    'os_version' => '20.04',
                    'rapid_deploy' => true,
                    'protection' => [
                        'delete' => true,
                    ],
                    'deprecated' => null,
                    'labels' => ['type' => 'backup'],
                ],
                [
                    'id' => 3,
                    'type' => 'system',
                    'status' => 'available',
                    'name' => 'system-image',
                    'description' => 'System image',
                    'image_size' => 1.2,
                    'disk_size' => 10,
                    'created' => '2023-01-01T00:00:00+00:00',
                    'created_from' => null,
                    'bound_to' => null,
                    'os_flavor' => 'ubuntu',
                    'os_version' => '22.04',
                    'rapid_deploy' => true,
                    'protection' => [
                        'delete' => false,
                    ],
                    'deprecated' => null,
                    'labels' => ['type' => 'system'],
                ],
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'per_page' => 25,
                    'previous_page' => null,
                    'next_page' => null,
                    'last_page' => 1,
                    'total_entries' => 3,
                ],
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->images()->list();
        $images = $response->images();

        $this->assertCount(3, $images);

        $snapshotImage = $images[0];
        $this->assertEquals('snapshot', $snapshotImage->type());
        $this->assertFalse($snapshotImage->rapidDeploy());
        $this->assertFalse($snapshotImage->protection()['delete']);

        $backupImage = $images[1];
        $this->assertEquals('backup', $backupImage->type());
        $this->assertTrue($backupImage->rapidDeploy());
        $this->assertTrue($backupImage->protection()['delete']);

        $systemImage = $images[2];
        $this->assertEquals('system', $systemImage->type());
        $this->assertTrue($systemImage->rapidDeploy());
        $this->assertFalse($systemImage->protection()['delete']);

        $this->assertRequestWasMade($requests, 'images', 'list');
    }

    // ===== IMAGE ACTIONS TESTS =====

    /**
     * Test listing image actions
     */
    public function test_can_list_image_actions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $imageId = '123';
        $response = $client->images()->actions()->list($imageId);

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertCount(2, $response->actions());

        $actions = $response->actions();
        $this->assertIsArray($actions);
        $this->assertEquals('change_protection', $actions[0]->command());
        $this->assertEquals('success', $actions[0]->status());

        $this->assertRequestWasMade($requests, 'image_actions', 'list');
    }

    /**
     * Test getting a specific image action
     */
    public function test_can_get_specific_image_action(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $imageId = '123';
        $actionId = '456';
        $response = $client->images()->actions()->retrieve($imageId, $actionId);

        $this->assertInstanceOf(ActionResponse::class, $response);

        $action = $response->action();
        $this->assertEquals(456, $action->id());
        $this->assertEquals('get', $action->command());
        $this->assertEquals('success', $action->status());

        $this->assertRequestWasMade($requests, 'image_actions', 'get');
    }

    /**
     * Test changing image protection
     */
    public function test_can_change_image_protection(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $imageId = '123';
        $parameters = [
            'delete' => true,
        ];

        $response = $client->images()->actions()->changeProtection($imageId, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);

        $action = $response->action();
        $this->assertEquals('change_protection', $action->command());
        $this->assertEquals('success', $action->status());

        $this->assertRequestWasMade($requests, 'image_actions', 'change_protection');
    }

    /**
     * Test image actions with custom responses
     */
    public function test_image_actions_with_custom_responses(): void
    {
        $customData = [
            'action' => [
                'id' => 999,
                'command' => 'change_protection',
                'status' => 'running',
                'progress' => 50,
                'started' => '2023-01-01T16:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 123,
                        'type' => 'image',
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

        $response = $client->images()->actions()->changeProtection('123', [
            'delete' => true,
        ]);

        $this->assertInstanceOf(ActionResponse::class, $response);

        $action = $response->action();
        $this->assertEquals(999, $action->id());
        $this->assertEquals('change_protection', $action->command());
        $this->assertEquals('running', $action->status());
        $this->assertEquals(50, $action->progress());
        $this->assertNull($action->finished());

        $resources = $action->resources();
        $this->assertCount(1, $resources);
        /** @var array<int, array<string, mixed>> $resources */
        $this->assertEquals(123, $resources[0]['id']);
        $this->assertEquals('image', $resources[0]['type']);

        $this->assertRequestWasMade($requests, 'image_actions', 'change_protection');
    }

    /**
     * Test complete image management workflow
     */
    public function test_complete_image_management_workflow(): void
    {
        $responses = [
            // Get image
            new Response(200, [], json_encode([
                'image' => [
                    'id' => 1,
                    'type' => 'snapshot',
                    'status' => 'available',
                    'name' => 'web-server-snapshot',
                    'description' => 'Web server snapshot',
                    'image_size' => 2.3,
                    'disk_size' => 20,
                    'created' => '2023-01-01T10:00:00+00:00',
                    'created_from' => [
                        'id' => 1,
                        'name' => 'web-server',
                    ],
                    'bound_to' => null,
                    'os_flavor' => 'ubuntu',
                    'os_version' => '22.04',
                    'rapid_deploy' => false,
                    'protection' => [
                        'delete' => false,
                    ],
                    'deprecated' => null,
                    'labels' => ['environment' => 'production'],
                ],
            ]) ?: ''),
            // Change protection
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 2,
                    'command' => 'change_protection',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2023-01-01T10:01:00+00:00',
                    'finished' => '2023-01-01T10:01:01+00:00',
                    'resources' => [
                        ['id' => 1, 'type' => 'image'],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
            // Update image
            new Response(200, [], json_encode([
                'image' => [
                    'id' => 1,
                    'type' => 'snapshot',
                    'status' => 'available',
                    'name' => 'updated-web-server-snapshot',
                    'description' => 'Updated web server snapshot',
                    'image_size' => 2.3,
                    'disk_size' => 20,
                    'created' => '2023-01-01T10:00:00+00:00',
                    'created_from' => [
                        'id' => 1,
                        'name' => 'web-server',
                    ],
                    'bound_to' => null,
                    'os_flavor' => 'ubuntu',
                    'os_version' => '22.04',
                    'rapid_deploy' => false,
                    'protection' => [
                        'delete' => true,
                    ],
                    'deprecated' => null,
                    'labels' => ['environment' => 'production', 'updated' => 'true'],
                ],
            ]) ?: ''),
            // Delete image
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 3,
                    'command' => 'delete_image',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2023-01-01T10:02:00+00:00',
                    'finished' => '2023-01-01T10:02:01+00:00',
                    'resources' => [
                        ['id' => 1, 'type' => 'image'],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];

        $requests = [];
        $client = $this->fakeClient($responses, $requests);

        // Complete workflow: get image, change protection, update, delete
        $getResponse = $client->images()->retrieve('1');
        $image = $getResponse->image();
        $this->assertEquals(1, $image->id());

        $changeProtectionResponse = $client->images()->actions()->changeProtection('1', [
            'delete' => true,
        ]);
        $this->assertEquals('change_protection', $changeProtectionResponse->action()->command());

        $updateResponse = $client->images()->update('1', [
            'name' => 'updated-web-server-snapshot',
            'description' => 'Updated web server snapshot',
            'labels' => ['environment' => 'production', 'updated' => 'true'],
        ]);
        $this->assertEquals('updated-web-server-snapshot', $updateResponse->image()->name());

        $deleteResponse = $client->images()->delete('1');
        $this->assertEquals('delete_image', $deleteResponse->action()->command());

        // Verify all requests were made
        $this->assertRequestWasMade($requests, 'images', 'retrieve');
        $this->assertRequestWasMade($requests, 'image_actions', 'change_protection');
        $this->assertRequestWasMade($requests, 'images', 'update');
        $this->assertRequestWasMade($requests, 'images', 'delete');
    }
}
