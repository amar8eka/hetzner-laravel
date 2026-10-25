<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\ISOs\ListRequest;
use Boci\HetznerLaravel\Requests\ISOs\RetrieveRequest;
use Boci\HetznerLaravel\Responses\ISOs\ListResponse;
use Boci\HetznerLaravel\Responses\ISOs\RetrieveResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * ISOs Fake
 *
 * This fake ISOs resource extends the real ISOs resource for testing purposes.
 * It allows you to mock API responses and assert that specific ISO requests
 * were made during testing.
 */
final class ISOsFake implements ResourceContract
{
    /**
     * Create a new fake ISOs resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all ISOs (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'isos',
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
     * Retrieve a specific ISO by ID (fake implementation).
     *
     * @param  string  $isoId  The ID of the ISO to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $isoId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'isos',
            'method' => 'retrieve',
            'parameters' => ['isoId' => $isoId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($isoId));
        }

        return RetrieveResponse::fake(['isoId' => $isoId]);
    }

    /**
     * Assert that a request was sent to the ISOs resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'isos');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to isos.');
        }
    }

    /**
     * Assert that no requests were sent to the ISOs resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'isos');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to isos.');
        }
    }
}
