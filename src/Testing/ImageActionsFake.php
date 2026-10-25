<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\ImageActions\ChangeProtectionRequest;
use Boci\HetznerLaravel\Requests\ImageActions\GetActionRequest;
use Boci\HetznerLaravel\Requests\ImageActions\ListActionsRequest;
use Boci\HetznerLaravel\Responses\ImageActions\ActionResponse;
use Boci\HetznerLaravel\Responses\ImageActions\ListActionsResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Image Actions Fake
 *
 * This fake image actions resource implements the same interface as the real image actions resource for testing purposes.
 * It allows you to mock API responses and assert that specific image action requests
 * were made during testing.
 */
final class ImageActionsFake implements ResourceContract
{
    /**
     * Create a new fake image actions resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all actions for an image (fake implementation).
     *
     * @param  string  $imageId  The ID of the image
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(string $imageId, array $parameters = []): ListActionsResponse
    {
        $this->requests[] = [
            'resource' => 'image_actions',
            'method' => 'list',
            'parameters' => array_merge(['imageId' => $imageId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListActionsResponse::from($response, new ListActionsRequest($imageId, $parameters));
        }

        return ListActionsResponse::fake(array_merge(['imageId' => $imageId], $parameters));
    }

    /**
     * Get a specific action for an image (fake implementation).
     *
     * @param  string  $imageId  The ID of the image
     * @param  string  $actionId  The ID of the action to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $imageId, string $actionId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'image_actions',
            'method' => 'get',
            'parameters' => ['imageId' => $imageId, 'actionId' => $actionId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new GetActionRequest($imageId, $actionId));
        }

        return ActionResponse::fake(['imageId' => $imageId, 'actionId' => $actionId, 'command' => 'get']);
    }

    /**
     * Change protection settings for an image (fake implementation).
     *
     * @param  string  $imageId  The ID of the image
     * @param  array<string, mixed>  $parameters  The protection parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeProtection(string $imageId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'image_actions',
            'method' => 'change_protection',
            'parameters' => array_merge(['imageId' => $imageId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeProtectionRequest($imageId, $parameters));
        }

        return ActionResponse::fake(array_merge(['imageId' => $imageId, 'command' => 'change_protection'], $parameters));
    }

    /**
     * Assert that a request was sent to the image actions resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'image_actions');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to image_actions.');
        }
    }

    /**
     * Assert that no requests were sent to the image actions resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'image_actions');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to image_actions.');
        }
    }
}
