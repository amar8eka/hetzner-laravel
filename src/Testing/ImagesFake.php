<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\Images\DeleteRequest;
use Boci\HetznerLaravel\Requests\Images\ListRequest;
use Boci\HetznerLaravel\Requests\Images\RetrieveRequest;
use Boci\HetznerLaravel\Requests\Images\UpdateRequest;
use Boci\HetznerLaravel\Responses\Images\DeleteResponse;
use Boci\HetznerLaravel\Responses\Images\ListResponse;
use Boci\HetznerLaravel\Responses\Images\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Images\UpdateResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Images Fake
 *
 * This fake images resource extends the real images resource for testing purposes.
 * It allows you to mock API responses and assert that specific image requests
 * were made during testing.
 */
final class ImagesFake implements ResourceContract
{
    /**
     * Create a new fake images resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List images (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'images',
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
     * Retrieve an image (fake implementation).
     *
     * @param  string  $imageId  The image ID
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $imageId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'images',
            'method' => 'retrieve',
            'parameters' => ['imageId' => $imageId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($imageId));
        }

        return RetrieveResponse::fake(['imageId' => $imageId]);
    }

    /**
     * Update an image (fake implementation).
     *
     * @param  string  $imageId  The ID of the image to update
     * @param  array<string, mixed>  $parameters  The update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function update(string $imageId, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'images',
            'method' => 'update',
            'parameters' => array_merge(['imageId' => $imageId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new UpdateRequest($imageId, $parameters));
        }

        return UpdateResponse::fake(array_merge(['imageId' => $imageId], $parameters));
    }

    /**
     * Delete an image (fake implementation).
     *
     * @param  string  $imageId  The ID of the image to delete
     *
     * @throws Throwable When a mock exception is provided
     */
    public function delete(string $imageId): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'images',
            'method' => 'delete',
            'parameters' => ['imageId' => $imageId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new DeleteRequest($imageId));
        }

        return DeleteResponse::fake(['imageId' => $imageId]);
    }

    /**
     * Get access to Image actions (fake implementation).
     */
    public function actions(): ImageActionsFake
    {
        return new ImageActionsFake($this->responses, $this->requests);
    }

    /**
     * Assert that a request was sent to the images resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'images');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to images.');
        }
    }

    /**
     * Assert that no requests were sent to the images resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'images');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to images.');
        }
    }
}
