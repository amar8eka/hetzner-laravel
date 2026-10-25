<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\ServerTypes\ListRequest;
use Boci\HetznerLaravel\Requests\ServerTypes\RetrieveRequest;
use Boci\HetznerLaravel\Responses\ServerTypes\ListResponse;
use Boci\HetznerLaravel\Responses\ServerTypes\RetrieveResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Server Types Fake
 *
 * This fake server types resource extends the real server types resource for testing purposes.
 * It allows you to mock API responses and assert that specific server type requests
 * were made during testing.
 */
final class ServerTypesFake implements ResourceContract
{
    /**
     * Create a new fake server types resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List server types (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'server_types',
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
     * Retrieve a server type (fake implementation).
     *
     * @param  string  $serverTypeId  The server type ID
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $serverTypeId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'server_types',
            'method' => 'retrieve',
            'parameters' => ['serverTypeId' => $serverTypeId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($serverTypeId));
        }

        return RetrieveResponse::fake(['serverTypeId' => $serverTypeId]);
    }

    /**
     * Assert that a request was sent to the server types resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'server_types');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to server_types.');
        }
    }

    /**
     * Assert that no requests were sent to the server types resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'server_types');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to server_types.');
        }
    }
}
