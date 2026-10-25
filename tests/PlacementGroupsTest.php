<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\PlacementGroups\CreateResponse;
use Boci\HetznerLaravel\Responses\PlacementGroups\DeleteResponse;
use Boci\HetznerLaravel\Responses\PlacementGroups\ListResponse;
use Boci\HetznerLaravel\Responses\PlacementGroups\PlacementGroup;
use Boci\HetznerLaravel\Responses\PlacementGroups\RetrieveResponse;
use Boci\HetznerLaravel\Responses\PlacementGroups\UpdateResponse;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Placement Groups Test Suite
 *
 * This test suite covers all functionality related to the Placement Groups resource,
 * including listing, creating, getting, updating, and deleting placement groups.
 */
final class PlacementGroupsTest extends TestCase
{
    /**
     * Test listing placement groups with fake data
     */
    public function test_can_list_placement_groups_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->placementGroups()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertIsArray($response->placementGroups());
        $this->assertCount(2, $response->placementGroups());

        $placementGroups = $response->placementGroups();
        $this->assertInstanceOf(PlacementGroup::class, $placementGroups[0]);
        $this->assertEquals(1, $placementGroups[0]->id());
        $this->assertEquals('my-placement-group', $placementGroups[0]->name());
        $this->assertEquals('spread', $placementGroups[0]->type());
        $this->assertIsArray($placementGroups[0]->labels());
        $this->assertIsArray($placementGroups[0]->servers());

        $this->assertInstanceOf(PlacementGroup::class, $placementGroups[1]);
        $this->assertEquals(2, $placementGroups[1]->id());
        $this->assertEquals('my-placement-group-2', $placementGroups[1]->name());
        $this->assertEquals('anti_affinity', $placementGroups[1]->type());

        $this->assertRequestWasMade($requests, 'placement_groups', 'list');
    }

    /**
     * Test listing placement groups with custom response
     */
    public function test_can_list_placement_groups_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'placement_groups' => [
                    [
                        'id' => 42,
                        'name' => 'custom-placement-group',
                        'labels' => [
                            'environment' => 'staging',
                            'team' => 'devops',
                        ],
                        'type' => 'spread',
                        'created' => '2016-01-30T23:50:00+00:00',
                        'servers' => [1234, 5678, 9012],
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

        $response = $client->placementGroups()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->placementGroups());

        $placementGroup = $response->placementGroups()[0];
        $this->assertEquals(42, $placementGroup->id());
        $this->assertEquals('custom-placement-group', $placementGroup->name());
        $this->assertEquals('spread', $placementGroup->type());
        $this->assertCount(3, $placementGroup->servers());
        $this->assertEquals('staging', $placementGroup->labels()['environment']);
        $this->assertEquals('devops', $placementGroup->labels()['team']);

        $this->assertRequestWasMade($requests, 'placement_groups', 'list');
    }

    /**
     * Test listing placement groups with query parameters
     */
    public function test_can_list_placement_groups_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'my-placement-group',
        ];

        $response = $client->placementGroups()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);

        $this->assertRequestWasMade($requests, 'placement_groups', 'list');
    }

    /**
     * Test creating a placement group
     */
    public function test_can_create_placement_group(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'new-placement-group',
            'type' => 'spread',
            'labels' => [
                'environment' => 'production',
                'team' => 'backend',
            ],
        ];

        $response = $client->placementGroups()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $this->assertInstanceOf(PlacementGroup::class, $response->placementGroup());
        $this->assertNotNull($response->action());

        $placementGroup = $response->placementGroup();
        $this->assertEquals(1, $placementGroup->id());
        $this->assertEquals('new-placement-group', $placementGroup->name());
        $this->assertEquals('spread', $placementGroup->type());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('create_placement_group', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'placement_groups', 'create');
    }

    /**
     * Test creating placement group with custom response
     */
    public function test_can_create_placement_group_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'placement_group' => [
                    'id' => 99,
                    'name' => 'custom-new-placement-group',
                    'labels' => [
                        'environment' => 'development',
                        'team' => 'frontend',
                    ],
                    'type' => 'anti_affinity',
                    'created' => '2016-01-30T23:50:00+00:00',
                    'servers' => [],
                ],
                'action' => [
                    'id' => 99,
                    'command' => 'create_placement_group',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2016-01-30T23:50:00+00:00',
                    'finished' => '2016-01-30T23:51:00+00:00',
                    'resources' => [
                        [
                            'id' => 99,
                            'type' => 'placement_group',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'custom-new-placement-group',
            'type' => 'anti_affinity',
        ];

        $response = $client->placementGroups()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $placementGroup = $response->placementGroup();
        $action = $response->action();

        $this->assertEquals(99, $placementGroup->id());
        $this->assertEquals('custom-new-placement-group', $placementGroup->name());
        $this->assertEquals('anti_affinity', $placementGroup->type());

        $this->assertNotNull($action);
        $this->assertEquals(99, $action->id());
        $this->assertEquals('create_placement_group', $action->command());
        $this->assertEquals('success', $action->status());
        $this->assertEquals(100, $action->progress());

        $this->assertRequestWasMade($requests, 'placement_groups', 'create');
    }

    /**
     * Test getting a specific placement group
     */
    public function test_can_get_specific_placement_group(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $placementGroupId = '42';
        $response = $client->placementGroups()->retrieve($placementGroupId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $this->assertInstanceOf(PlacementGroup::class, $response->placementGroup());

        $placementGroup = $response->placementGroup();
        $this->assertEquals(42, $placementGroup->id());
        $this->assertEquals('my-placement-group', $placementGroup->name());
        $this->assertEquals('spread', $placementGroup->type());
        $this->assertIsArray($placementGroup->labels());
        $this->assertIsArray($placementGroup->servers());

        $this->assertRequestWasMade($requests, 'placement_groups', 'retrieve');
    }

    /**
     * Test getting placement group with custom response
     */
    public function test_can_get_placement_group_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'placement_group' => [
                    'id' => 123,
                    'name' => 'detailed-placement-group',
                    'labels' => [
                        'environment' => 'production',
                        'team' => 'infrastructure',
                        'project' => 'web-app',
                    ],
                    'type' => 'anti_affinity',
                    'created' => '2016-01-30T23:50:00+00:00',
                    'servers' => [1111, 2222, 3333, 4444],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $placementGroupId = '123';
        $response = $client->placementGroups()->retrieve($placementGroupId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $placementGroup = $response->placementGroup();

        $this->assertEquals(123, $placementGroup->id());
        $this->assertEquals('detailed-placement-group', $placementGroup->name());
        $this->assertEquals('anti_affinity', $placementGroup->type());
        $this->assertCount(4, $placementGroup->servers());
        $this->assertEquals('production', $placementGroup->labels()['environment']);
        $this->assertEquals('infrastructure', $placementGroup->labels()['team']);
        $this->assertEquals('web-app', $placementGroup->labels()['project']);

        $this->assertRequestWasMade($requests, 'placement_groups', 'retrieve');
    }

    /**
     * Test updating a placement group
     */
    public function test_can_update_placement_group(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $placementGroupId = '42';
        $parameters = [
            'name' => 'updated-placement-group-name',
            'labels' => [
                'environment' => 'staging',
                'team' => 'frontend',
            ],
        ];

        $response = $client->placementGroups()->update($placementGroupId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);
        $this->assertInstanceOf(PlacementGroup::class, $response->placementGroup());

        $placementGroup = $response->placementGroup();
        $this->assertEquals(42, $placementGroup->id());
        $this->assertEquals('updated-placement-group-name', $placementGroup->name());
        $this->assertEquals('staging', $placementGroup->labels()['environment']);
        $this->assertEquals('frontend', $placementGroup->labels()['team']);

        $this->assertRequestWasMade($requests, 'placement_groups', 'update');
    }

    /**
     * Test deleting a placement group
     */
    public function test_can_delete_placement_group(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $placementGroupId = '42';
        $response = $client->placementGroups()->delete($placementGroupId);

        $this->assertInstanceOf(DeleteResponse::class, $response);
        $this->assertNotNull($response->action());

        $action = $response->action();
        $this->assertEquals(1, $action->id());
        $this->assertEquals('delete_placement_group', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'placement_groups', 'delete');
    }

    /**
     * Test placement group response structure
     */
    public function test_placement_group_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->placementGroups()->list();
        $placementGroups = $response->placementGroups();

        foreach ($placementGroups as $placementGroup) {
            // Test all placement group properties
            $this->assertIsInt($placementGroup->id());
            $this->assertIsString($placementGroup->name());
            $this->assertIsString($placementGroup->type());
            $this->assertIsArray($placementGroup->labels());
            $this->assertIsArray($placementGroup->servers());
            $this->assertIsString($placementGroup->created());

            // Test placement group types
            $this->assertContains($placementGroup->type(), ['spread', 'anti_affinity']);

            // Test labels structure
            foreach ($placementGroup->labels() as $key => $value) {
                $this->assertIsString($key);
                $this->assertIsString($value);
            }

            // Test servers array
            foreach ($placementGroup->servers() as $serverId) {
                $this->assertIsInt($serverId);
            }
        }
    }

    /**
     * Test handling placement group API exception
     */
    public function test_can_handle_placement_group_api_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Placement group not found', new Request('GET', '/placement_groups/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Placement group not found');

        $client->placementGroups()->retrieve('999');
    }

    /**
     * Test handling placement group list exception
     */
    public function test_can_handle_placement_group_list_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Rate limit exceeded', new Request('GET', '/placement_groups')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->placementGroups()->list();
    }

    /**
     * Test handling mixed placement group response types
     */
    public function test_can_handle_mixed_placement_group_response_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'placement_groups' => [
                    [
                        'id' => 1,
                        'name' => 'my-placement-group',
                        'labels' => [
                            'environment' => 'production',
                            'team' => 'backend',
                        ],
                        'type' => 'spread',
                        'created' => '2016-01-30T23:50:00+00:00',
                        'servers' => [4711, 4712],
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
            new RequestException('Placement group not found', new Request('GET', '/placement_groups/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $listResponse = $client->placementGroups()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->placementGroups());

        // Second call should throw exception
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Placement group not found');
        $client->placementGroups()->retrieve('999');
    }

    /**
     * Test using individual placement group fake
     */
    public function test_using_individual_placement_group_fake(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $placementGroupsFake = $client->placementGroups();

        $response = $placementGroupsFake->list(['page' => 1, 'per_page' => 5]);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(2, $response->placementGroups());

        $placementGroupsFake->assertSent(function (array $request) {
            return $request['resource'] === 'placement_groups' &&
                   $request['method'] === 'list' &&
                   isset($request['parameters']['page']) &&
                   $request['parameters']['page'] === 1 &&
                   isset($request['parameters']['per_page']) &&
                   $request['parameters']['per_page'] === 5;
        });
    }

    /**
     * Test placement group fake assert not sent
     */
    public function test_placement_group_fake_assert_not_sent(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $placementGroupsFake = $client->placementGroups();

        // No requests made yet
        $placementGroupsFake->assertNotSent();

        // Make a request
        $placementGroupsFake->list();

        // Now it should fail
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Requests were sent to placement_groups.');
        $placementGroupsFake->assertNotSent();
    }

    /**
     * Test placement group workflow simulation
     */
    public function test_placement_group_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List placement groups response
            new Response(200, [], json_encode([
                'placement_groups' => [
                    [
                        'id' => 1,
                        'name' => 'my-placement-group',
                        'labels' => [
                            'environment' => 'production',
                            'team' => 'backend',
                        ],
                        'type' => 'spread',
                        'created' => '2016-01-30T23:50:00+00:00',
                        'servers' => [4711, 4712],
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
            // Get specific placement group response
            new Response(200, [], json_encode([
                'placement_group' => [
                    'id' => 1,
                    'name' => 'my-placement-group',
                    'labels' => [
                        'environment' => 'production',
                        'team' => 'backend',
                    ],
                    'type' => 'spread',
                    'created' => '2016-01-30T23:50:00+00:00',
                    'servers' => [4711, 4712],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list placement groups, then get details of the first one
        $listResponse = $client->placementGroups()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->placementGroups());

        $firstPlacementGroup = $listResponse->placementGroups()[0];
        $placementGroupId = (string) $firstPlacementGroup->id();

        $getResponse = $client->placementGroups()->retrieve($placementGroupId);
        $this->assertInstanceOf(RetrieveResponse::class, $getResponse);
        $this->assertEquals($firstPlacementGroup->id(), $getResponse->placementGroup()->id());
        $this->assertEquals($firstPlacementGroup->name(), $getResponse->placementGroup()->name());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'placement_groups', 'list');
        $this->assertRequestWasMade($requests, 'placement_groups', 'retrieve');
    }

    /**
     * Test placement group error response handling
     */
    public function test_placement_group_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Placement group not found', new Request('GET', '/placement_groups/nonexistent')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Placement group not found');
        $client->placementGroups()->retrieve('nonexistent');
    }

    /**
     * Test placement group empty list response
     */
    public function test_placement_group_empty_list_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'placement_groups' => [],
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

        $response = $client->placementGroups()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(0, $response->placementGroups());
        $this->assertEmpty($response->placementGroups());
    }

    /**
     * Test placement group type validation
     */
    public function test_placement_group_type_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->placementGroups()->list();
        $placementGroups = $response->placementGroups();

        foreach ($placementGroups as $placementGroup) {
            // Test that placement group types are valid
            $this->assertContains($placementGroup->type(), ['spread', 'anti_affinity']);
        }
    }

    /**
     * Test placement group pagination
     */
    public function test_placement_group_pagination(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->placementGroups()->list();
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
     * Test placement group creation with different types
     */
    public function test_placement_group_creation_with_different_types(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        // Test spread type
        $spreadParameters = [
            'name' => 'spread-placement-group',
            'type' => 'spread',
            'labels' => [
                'environment' => 'production',
            ],
        ];

        $spreadResponse = $client->placementGroups()->create($spreadParameters);
        $this->assertInstanceOf(CreateResponse::class, $spreadResponse);
        $this->assertEquals('spread', $spreadResponse->placementGroup()->type());

        // Test anti_affinity type
        $antiAffinityParameters = [
            'name' => 'anti-affinity-placement-group',
            'type' => 'anti_affinity',
            'labels' => [
                'environment' => 'production',
            ],
        ];

        $antiAffinityResponse = $client->placementGroups()->create($antiAffinityParameters);
        $this->assertInstanceOf(CreateResponse::class, $antiAffinityResponse);
        $this->assertEquals('anti_affinity', $antiAffinityResponse->placementGroup()->type());
    }

    /**
     * Test placement group to array conversion
     */
    public function test_placement_group_to_array_conversion(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->placementGroups()->list();
        $placementGroups = $response->placementGroups();

        foreach ($placementGroups as $placementGroup) {
            $array = $placementGroup->toArray();

            $this->assertIsArray($array);
            $this->assertArrayHasKey('id', $array);
            $this->assertArrayHasKey('name', $array);
            $this->assertArrayHasKey('type', $array);
            $this->assertArrayHasKey('labels', $array);
            $this->assertArrayHasKey('servers', $array);
            $this->assertArrayHasKey('created', $array);

            $this->assertEquals($placementGroup->id(), $array['id']);
            $this->assertEquals($placementGroup->name(), $array['name']);
            $this->assertEquals($placementGroup->type(), $array['type']);
        }
    }

    /**
     * Test placement group with servers
     */
    public function test_placement_group_with_servers(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->placementGroups()->list();
        $placementGroups = $response->placementGroups();

        foreach ($placementGroups as $placementGroup) {
            $servers = $placementGroup->servers();
            $this->assertIsArray($servers);

            foreach ($servers as $serverId) {
                $this->assertIsInt($serverId);
                $this->assertGreaterThan(0, $serverId);
            }
        }
    }

    /**
     * Test placement group labels validation
     */
    public function test_placement_group_labels_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->placementGroups()->list();
        $placementGroups = $response->placementGroups();

        foreach ($placementGroups as $placementGroup) {
            $labels = $placementGroup->labels();
            $this->assertIsArray($labels);

            foreach ($labels as $key => $value) {
                $this->assertIsString($key);
                $this->assertIsString($value);
                $this->assertNotEmpty($key);
                $this->assertNotEmpty($value);
            }
        }
    }
}
