<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\Volumes\CreateRequest;
use Boci\HetznerLaravel\Requests\Volumes\DeleteRequest;
use Boci\HetznerLaravel\Requests\Volumes\ListRequest;
use Boci\HetznerLaravel\Requests\Volumes\RetrieveRequest;
use Boci\HetznerLaravel\Requests\Volumes\UpdateRequest;
use Boci\HetznerLaravel\Responses\Volumes\CreateResponse;
use Boci\HetznerLaravel\Responses\Volumes\DeleteResponse;
use Boci\HetznerLaravel\Responses\Volumes\ListResponse;
use Boci\HetznerLaravel\Responses\Volumes\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Volumes\UpdateResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Volumes Fake
 *
 * This fake volumes resource extends the real volumes resource for testing purposes.
 * It allows you to mock API responses and assert that specific volume requests
 * were made during testing.
 */
final class VolumesFake implements ResourceContract
{
    /**
     * Create a new fake volumes resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * Create a new volume (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  The volume creation parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function create(array $parameters): CreateResponse
    {
        $this->requests[] = [
            'resource' => 'volumes',
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
     * List all volumes (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'volumes',
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
     * Get a specific volume by ID (fake implementation).
     *
     * @param  string  $volumeId  The ID of the volume to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $volumeId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'volumes',
            'method' => 'get',
            'parameters' => ['volumeId' => $volumeId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($volumeId));
        }

        return RetrieveResponse::fake(['volumeId' => $volumeId]);
    }

    /**
     * Update a volume (fake implementation).
     *
     * @param  string  $volumeId  The ID of the volume to update
     * @param  array<string, mixed>  $parameters  The update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function update(string $volumeId, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'volumes',
            'method' => 'update',
            'parameters' => array_merge(['volumeId' => $volumeId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new UpdateRequest($volumeId, $parameters));
        }

        return UpdateResponse::fake(array_merge(['volumeId' => $volumeId], $parameters));
    }

    /**
     * Delete a volume (fake implementation).
     *
     * @param  string  $volumeId  The ID of the volume to delete
     *
     * @throws Throwable When a mock exception is provided
     */
    public function delete(string $volumeId): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'volumes',
            'method' => 'delete',
            'parameters' => ['volumeId' => $volumeId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new DeleteRequest($volumeId));
        }

        return DeleteResponse::fake(['volumeId' => $volumeId]);
    }

    /**
     * Get access to Volume actions (fake implementation).
     */
    public function actions(): VolumeActionsFake
    {
        return new VolumeActionsFake($this->responses, $this->requests);
    }

    /**
     * Assert that a request was sent to the volumes resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'volumes');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to volumes.');
        }
    }

    /**
     * Assert that no requests were sent to the volumes resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'volumes');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to volumes.');
        }
    }
}
