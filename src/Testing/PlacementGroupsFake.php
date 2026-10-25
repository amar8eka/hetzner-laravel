<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\PlacementGroups\CreateRequest;
use Boci\HetznerLaravel\Requests\PlacementGroups\DeleteRequest;
use Boci\HetznerLaravel\Requests\PlacementGroups\ListRequest;
use Boci\HetznerLaravel\Requests\PlacementGroups\RetrieveRequest;
use Boci\HetznerLaravel\Requests\PlacementGroups\UpdateRequest;
use Boci\HetznerLaravel\Responses\PlacementGroups\CreateResponse;
use Boci\HetznerLaravel\Responses\PlacementGroups\DeleteResponse;
use Boci\HetznerLaravel\Responses\PlacementGroups\ListResponse;
use Boci\HetznerLaravel\Responses\PlacementGroups\RetrieveResponse;
use Boci\HetznerLaravel\Responses\PlacementGroups\UpdateResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Placement Groups Fake
 *
 * This fake placement groups resource extends the real placement groups resource for testing purposes.
 * It allows you to mock API responses and assert that specific placement group requests
 * were made during testing.
 */
final class PlacementGroupsFake implements ResourceContract
{
    /**
     * Create a new fake placement groups resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * Create a new placement group (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  The placement group creation parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function create(array $parameters): CreateResponse
    {
        $this->requests[] = [
            'resource' => 'placement_groups',
            'method' => 'create',
            'parameters' => $parameters,
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return CreateResponse::from($response, new CreateRequest($parameters));
        }

        return CreateResponse::fake($parameters);
    }

    /**
     * List all placement groups (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'placement_groups',
            'method' => 'list',
            'parameters' => $parameters,
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListResponse::from($response, new ListRequest($parameters));
        }

        return ListResponse::fake($parameters);
    }

    /**
     * Retrieve a specific placement group by ID (fake implementation).
     *
     * @param  string  $placementGroupId  The ID of the placement group to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $placementGroupId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'placement_groups',
            'method' => 'retrieve',
            'parameters' => ['placementGroupId' => $placementGroupId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($placementGroupId));
        }

        return RetrieveResponse::fake(['placementGroupId' => $placementGroupId]);
    }

    /**
     * Update a placement group (fake implementation).
     *
     * @param  string  $placementGroupId  The ID of the placement group to update
     * @param  array<string, mixed>  $parameters  The update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function update(string $placementGroupId, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'placement_groups',
            'method' => 'update',
            'parameters' => array_merge(['placementGroupId' => $placementGroupId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new UpdateRequest($placementGroupId, $parameters));
        }

        return UpdateResponse::fake(array_merge(['placementGroupId' => $placementGroupId], $parameters));
    }

    /**
     * Delete a placement group (fake implementation).
     *
     * @param  string  $placementGroupId  The ID of the placement group to delete
     *
     * @throws Throwable When a mock exception is provided
     */
    public function delete(string $placementGroupId): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'placement_groups',
            'method' => 'delete',
            'parameters' => ['placementGroupId' => $placementGroupId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new DeleteRequest($placementGroupId));
        }

        return DeleteResponse::fake(['placementGroupId' => $placementGroupId]);
    }

    /**
     * Assert that a request was sent to the placement groups resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'placement_groups');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to placement_groups.');
        }
    }

    /**
     * Assert that no requests were sent to the placement groups resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'placement_groups');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to placement_groups.');
        }
    }
}
