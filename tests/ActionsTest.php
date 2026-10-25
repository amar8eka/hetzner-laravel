<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\Actions\Action;
use Boci\HetznerLaravel\Responses\Actions\GetActionResponse;
use Boci\HetznerLaravel\Responses\Actions\ListActionsResponse;
use Boci\HetznerLaravel\Testing\ActionsFake;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Psr7\Response;

/**
 * Actions Test Suite
 *
 * This test suite covers all functionality related to the Actions resource,
 * including listing actions, getting specific actions, and handling various
 * response types and error conditions.
 */
final class ActionsTest extends TestCase
{
    /**
     * Test listing actions with fake data
     */
    public function test_can_list_actions_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->actions()->list();

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertCount(3, $response->actions());

        $actions = $response->actions();
        $this->assertInstanceOf(Action::class, $actions[0]);
        $this->assertEquals(1, $actions[0]->id());
        $this->assertEquals('create_server', $actions[0]->command());
        $this->assertEquals('success', $actions[0]->status());
        $this->assertEquals(100, $actions[0]->progress());

        $pagination = $response->pagination();
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(25, $pagination['per_page']);
        $this->assertEquals(3, $pagination['total']);

        $this->assertRequestWasMade($requests, 'actions', 'list');
    }

    /**
     * Test listing actions with custom response
     */
    public function test_can_list_actions_with_custom_response(): void
    {
        $customData = [
            'actions' => [
                [
                    'id' => 1,
                    'command' => 'power_on',
                    'status' => 'running',
                    'progress' => 50,
                    'started' => '2023-01-01T12:00:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 1,
                            'type' => 'server',
                        ],
                    ],
                    'error' => null,
                ],
                [
                    'id' => 2,
                    'command' => 'reboot',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2023-01-01T11:00:00+00:00',
                    'finished' => '2023-01-01T11:02:00+00:00',
                    'resources' => [
                        [
                            'id' => 1,
                            'type' => 'server',
                        ],
                    ],
                    'error' => null,
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

        $response = $client->actions()->list();

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertCount(2, $response->actions());

        $actions = $response->actions();
        $this->assertEquals('power_on', $actions[0]->command());
        $this->assertEquals('running', $actions[0]->status());
        $this->assertEquals(50, $actions[0]->progress());
        $this->assertNull($actions[0]->finished());

        $this->assertEquals('reboot', $actions[1]->command());
        $this->assertEquals('success', $actions[1]->status());
        $this->assertEquals(100, $actions[1]->progress());
        $this->assertEquals('2023-01-01T11:02:00+00:00', $actions[1]->finished());

        $this->assertRequestWasMade($requests, 'actions', 'list');
    }

    /**
     * Test listing actions with query parameters
     */
    public function test_can_list_actions_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'status' => 'success',
        ];

        $response = $client->actions()->list($parameters);

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertRequestWasMade($requests, 'actions', 'list');
    }

    /**
     * Test getting a specific action
     */
    public function test_can_get_specific_action(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $actionId = '123';
        $response = $client->actions()->retrieve($actionId);

        $this->assertInstanceOf(GetActionResponse::class, $response);

        $action = $response->action();
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(123, $action->id());
        $this->assertEquals('create_server', $action->command());
        $this->assertEquals('success', $action->status());
        $this->assertEquals(100, $action->progress());

        $this->assertRequestWasMade($requests, 'actions', 'get');
    }

    /**
     * Test getting a specific action with custom response
     */
    public function test_can_get_specific_action_with_custom_response(): void
    {
        $customData = [
            'action' => [
                'id' => 456,
                'command' => 'power_off',
                'status' => 'running',
                'progress' => 75,
                'started' => '2023-01-01T14:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 2,
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

        $actionId = '456';
        $response = $client->actions()->retrieve($actionId);

        $this->assertInstanceOf(GetActionResponse::class, $response);

        $action = $response->action();
        $this->assertEquals(456, $action->id());
        $this->assertEquals('power_off', $action->command());
        $this->assertEquals('running', $action->status());
        $this->assertEquals(75, $action->progress());
        $this->assertNull($action->finished());

        $resources = $action->resources();
        $this->assertCount(1, $resources);
        $this->assertArrayHasKey(0, $resources);
        /** @var array<int, array<string, mixed>> $resources */
        $resource = $resources[0];
        $this->assertEquals(2, $resource['id']);
        $this->assertEquals('server', $resource['type']);

        $this->assertRequestWasMade($requests, 'actions', 'get');
    }

    /**
     * Test action response structure validation
     */
    public function test_action_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->actions()->list();
        $actions = $response->actions();

        foreach ($actions as $action) {
            $this->assertIsInt($action->id());
            $this->assertIsString($action->command());
            $this->assertIsString($action->status());
            $this->assertIsInt($action->progress());
            $this->assertIsString($action->started());
            $this->assertIsArray($action->resources());
            $this->assertNull($action->error());
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
     * Test action with error information
     */
    public function test_action_with_error_information(): void
    {
        $customData = [
            'action' => [
                'id' => 789,
                'command' => 'create_server',
                'status' => 'error',
                'progress' => 0,
                'started' => '2023-01-01T15:00:00+00:00',
                'finished' => '2023-01-01T15:00:30+00:00',
                'resources' => [],
                'error' => [
                    'code' => 'server_limit_reached',
                    'message' => 'Server limit reached for this project',
                ],
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->actions()->retrieve('789');
        $action = $response->action();

        $this->assertEquals('error', $action->status());
        $this->assertEquals(0, $action->progress());

        $error = $action->error();
        $this->assertIsArray($error);
        $this->assertEquals('server_limit_reached', $error['code']);
        $this->assertEquals('Server limit reached for this project', $error['message']);

        $this->assertRequestWasMade($requests, 'actions', 'get');
    }

    /**
     * Test action resources information
     */
    public function test_action_resources_information(): void
    {
        $customData = [
            'action' => [
                'id' => 101,
                'command' => 'attach_to_network',
                'status' => 'success',
                'progress' => 100,
                'started' => '2023-01-01T16:00:00+00:00',
                'finished' => '2023-01-01T16:00:15+00:00',
                'resources' => [
                    [
                        'id' => 1,
                        'type' => 'server',
                    ],
                    [
                        'id' => 2,
                        'type' => 'network',
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

        $response = $client->actions()->retrieve('101');
        $action = $response->action();

        $resources = $action->resources();
        $this->assertCount(2, $resources);

        $this->assertArrayHasKey(0, $resources);
        /** @var array<int, array<string, mixed>> $resources */
        $resource0 = $resources[0];
        $this->assertEquals(1, $resource0['id']);
        $this->assertEquals('server', $resource0['type']);

        $this->assertArrayHasKey(1, $resources);
        $resource1 = $resources[1];
        $this->assertEquals(2, $resource1['id']);
        $this->assertEquals('network', $resource1['type']);

        $this->assertRequestWasMade($requests, 'actions', 'get');
    }

    /**
     * Test action status variations
     */
    public function test_action_status_variations(): void
    {
        $statuses = ['running', 'success', 'error'];
        $responses = [];

        foreach ($statuses as $index => $status) {
            $responses[] = new Response(200, [], json_encode([
                'action' => [
                    'id' => $index + 1,
                    'command' => 'test_command',
                    'status' => $status,
                    'progress' => $status === 'success' ? 100 : ($status === 'error' ? 0 : 50),
                    'started' => '2023-01-01T12:00:00+00:00',
                    'finished' => $status !== 'running' ? '2023-01-01T12:01:00+00:00' : null,
                    'resources' => [],
                    'error' => $status === 'error' ? ['code' => 'test_error', 'message' => 'Test error'] : null,
                ],
            ]) ?: '');
        }

        $requests = [];
        $client = $this->fakeClient($responses, $requests);

        foreach ($statuses as $index => $expectedStatus) {
            $response = $client->actions()->retrieve((string) ($index + 1));
            $action = $response->action();

            $this->assertEquals($expectedStatus, $action->status());

            if ($expectedStatus === 'running') {
                $this->assertNull($action->finished());
            } else {
                $this->assertNotNull($action->finished());
            }
        }
    }

    /**
     * Test handling API exceptions
     */
    public function test_can_handle_actions_api_exceptions(): void
    {
        $exception = new \Exception('API connection failed');

        $requests = [];
        $responses = [$exception];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API connection failed');

        $client->actions()->list();
    }

    /**
     * Test handling error responses
     */
    public function test_can_handle_actions_error_responses(): void
    {
        $errorResponse = new Response(400, [], json_encode([
            'error' => [
                'code' => 'invalid_request',
                'message' => 'Invalid action ID provided',
            ],
        ]) ?: '');

        $requests = [];
        $responses = [$errorResponse];
        $client = $this->fakeClient($responses, $requests);

        // The fake client will return the error response as-is
        $response = $client->actions()->retrieve('invalid-id');
        $this->assertInstanceOf(GetActionResponse::class, $response);

        $this->assertRequestWasMade($requests, 'actions', 'get');
    }

    /**
     * Test using individual actions fake
     */
    public function test_using_individual_actions_fake(): void
    {
        $responses = [];
        $requests = [];

        $actionsFake = new ActionsFake($responses, $requests);

        // Test various actions operations
        $actionsFake->list();
        $actionsFake->list(['status' => 'success']);
        $actionsFake->retrieve('123');

        // Assert all requests were made
        $this->assertCount(3, $requests);

        // Test resource-specific assertions
        $actionsFake->assertSent(function ($request) {
            return $request['method'] === 'list';
        });

        $actionsFake->assertSent(function ($request) {
            return $request['method'] === 'list' &&
                   isset($request['parameters']['status']) &&
                   $request['parameters']['status'] === 'success';
        });

        $actionsFake->assertSent(function ($request) {
            return $request['method'] === 'get' &&
                   $request['parameters']['actionId'] === '123';
        });
    }

    /**
     * Test mixed response types for actions
     */
    public function test_can_handle_mixed_actions_response_types(): void
    {
        $responses = [
            new Response(200, [], json_encode([
                'actions' => [
                    [
                        'id' => 1,
                        'command' => 'create_server',
                        'status' => 'success',
                        'progress' => 100,
                        'started' => '2023-01-01T12:00:00+00:00',
                        'finished' => '2023-01-01T12:01:00+00:00',
                        'resources' => [],
                        'error' => null,
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
        $response = $client->actions()->list();
        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertCount(1, $response->actions());

        // Second call should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Network timeout');
        $client->actions()->retrieve('456');
    }

    /**
     * Test actions with complex parameters
     */
    public function test_can_list_actions_with_complex_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 3,
            'per_page' => 50,
            'status' => 'success',
            'sort' => 'id:desc',
        ];

        $response = $client->actions()->list($parameters);

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertRequestWasMade($requests, 'actions', 'list');
    }

    /**
     * Test actions workflow simulation
     */
    public function test_actions_workflow_simulation(): void
    {
        $responses = [
            // List actions
            new Response(200, [], json_encode([
                'actions' => [
                    [
                        'id' => 1,
                        'command' => 'create_server',
                        'status' => 'success',
                        'progress' => 100,
                        'started' => '2023-01-01T10:00:00+00:00',
                        'finished' => '2023-01-01T10:02:00+00:00',
                        'resources' => [['id' => 1, 'type' => 'server']],
                        'error' => null,
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
            // Get specific action
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 1,
                    'command' => 'create_server',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2023-01-01T10:00:00+00:00',
                    'finished' => '2023-01-01T10:02:00+00:00',
                    'resources' => [['id' => 1, 'type' => 'server']],
                    'error' => null,
                ],
            ]) ?: ''),
        ];

        $requests = [];
        $client = $this->fakeClient($responses, $requests);

        // Simulate workflow: list actions, then get details of first action
        $listResponse = $client->actions()->list();
        $actions = $listResponse->actions();

        $this->assertCount(1, $actions);
        $firstActionId = $actions[0]->id();

        $detailResponse = $client->actions()->retrieve((string) $firstActionId);
        $action = $detailResponse->action();

        $this->assertEquals($firstActionId, $action->id());
        $this->assertEquals('create_server', $action->command());
        $this->assertEquals('success', $action->status());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'actions', 'list');
        $this->assertRequestWasMade($requests, 'actions', 'get');
    }

    /**
     * Test actions resource assertions
     */
    public function test_actions_resource_assertions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $client->actions()->list(['status' => 'running']);
        $client->actions()->retrieve('999');

        // Test that specific requests were made
        $this->assertRequestWasMade($requests, 'actions', 'list');
        $this->assertRequestWasMade($requests, 'actions', 'get');

        // Test that no other requests were made
        $this->assertNoRequestWasMade($requests, 'servers');
        $this->assertNoRequestWasMade($requests, 'images');
    }

    /**
     * Test actions with specific action data
     */
    public function test_actions_with_specific_action_data(): void
    {
        $customData = [
            'action' => [
                'id' => 200,
                'command' => 'change_server_type',
                'status' => 'running',
                'progress' => 60,
                'started' => '2023-01-01T17:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 5,
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

        $response = $client->actions()->retrieve('200');
        $action = $response->action();

        $this->assertEquals(200, $action->id());
        $this->assertEquals('change_server_type', $action->command());
        $this->assertEquals('running', $action->status());
        $this->assertEquals(60, $action->progress());
        $this->assertEquals('2023-01-01T17:00:00+00:00', $action->started());
        $this->assertNull($action->finished());

        $resources = $action->resources();
        $this->assertCount(1, $resources);
        $this->assertArrayHasKey(0, $resources);
        /** @var array<int, array<string, mixed>> $resources */
        $resource = $resources[0];
        $this->assertEquals(5, $resource['id']);
        $this->assertEquals('server', $resource['type']);

        $this->assertRequestWasMade($requests, 'actions', 'get');
    }
}
