<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\Actions\GetActionRequest;
use Boci\HetznerLaravel\Requests\Actions\ListActionsRequest;
use Boci\HetznerLaravel\Responses\Actions\GetActionResponse;
use Boci\HetznerLaravel\Responses\Actions\ListActionsResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Actions Fake
 *
 * This fake actions resource implements the same interface as the real actions resource for testing purposes.
 * It allows you to mock API responses and assert that specific action requests
 * were made during testing.
 */
final class ActionsFake implements ResourceContract
{
    /**
     * Create a new fake actions resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all actions (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListActionsResponse
    {
        $this->requests[] = [
            'resource' => 'actions',
            'method' => 'list',
            'parameters' => $parameters,
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListActionsResponse::from($response, new ListActionsRequest($parameters));
        }

        return ListActionsResponse::fake($parameters);
    }

    /**
     * Get a specific action by ID (fake implementation).
     *
     * @param  string  $actionId  The ID of the action to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $actionId): GetActionResponse
    {
        $this->requests[] = [
            'resource' => 'actions',
            'method' => 'get',
            'parameters' => ['actionId' => $actionId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return GetActionResponse::from($response, new GetActionRequest($actionId));
        }

        return GetActionResponse::fake(['actionId' => $actionId]);
    }

    /**
     * Assert that a request was sent to the actions resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'actions');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to actions.');
        }
    }

    /**
     * Assert that no requests were sent to the actions resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'actions');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to actions.');
        }
    }
}
