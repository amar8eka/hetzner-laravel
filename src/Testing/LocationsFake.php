<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\Locations\ListRequest;
use Boci\HetznerLaravel\Requests\Locations\RetrieveRequest;
use Boci\HetznerLaravel\Responses\Locations\ListResponse;
use Boci\HetznerLaravel\Responses\Locations\RetrieveResponse;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Locations Fake
 *
 * This fake locations resource extends the real locations resource for testing purposes.
 * It allows you to mock API responses and assert that specific location requests
 * were made during testing.
 */
final class LocationsFake implements ResourceContract
{
    /**
     * Create a new fake locations resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List locations (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'locations',
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
     * Retrieve a location (fake implementation).
     *
     * @param  string  $locationId  The location ID
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $locationId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'locations',
            'method' => 'retrieve',
            'parameters' => ['locationId' => $locationId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($locationId));
        }

        return RetrieveResponse::fake(['locationId' => $locationId]);
    }
}
