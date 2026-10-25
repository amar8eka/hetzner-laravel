<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\Billing\ListPricingRequest;
use Boci\HetznerLaravel\Responses\Billing\ListPricingResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Billing Fake
 *
 * This fake billing resource extends the real billing resource for testing purposes.
 * It allows you to mock API responses and assert that specific billing requests
 * were made during testing.
 */
final class BillingFake implements ResourceContract
{
    /**
     * Create a new fake billing resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all pricing information (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function listPricing(array $parameters = []): ListPricingResponse
    {
        $this->requests[] = [
            'resource' => 'billing',
            'method' => 'list_pricing',
            'parameters' => $parameters,
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListPricingResponse::from($response, new ListPricingRequest($parameters));
        }

        return ListPricingResponse::fake($parameters);
    }

    /**
     * Assert that a request was sent to the billing resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'billing');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to billing.');
        }
    }

    /**
     * Assert that no requests were sent to the billing resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'billing');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to billing.');
        }
    }
}
