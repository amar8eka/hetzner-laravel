<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\VolumeActions\AttachRequest;
use Boci\HetznerLaravel\Requests\VolumeActions\ChangeProtectionRequest;
use Boci\HetznerLaravel\Requests\VolumeActions\DetachRequest;
use Boci\HetznerLaravel\Requests\VolumeActions\GetActionRequest;
use Boci\HetznerLaravel\Requests\VolumeActions\ListActionsRequest;
use Boci\HetznerLaravel\Requests\VolumeActions\ResizeRequest;
use Boci\HetznerLaravel\Responses\VolumeActions\ActionResponse;
use Boci\HetznerLaravel\Responses\VolumeActions\ListActionsResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Volume Actions Fake
 *
 * This fake volume actions resource implements ResourceContract for testing purposes.
 * It allows you to mock API responses and assert that specific volume action requests
 * were made during testing.
 */
final class VolumeActionsFake implements ResourceContract
{
    /**
     * Create a new fake volume actions resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all actions for a volume (fake implementation).
     *
     * @param  string  $volumeId  The ID of the volume
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(string $volumeId, array $parameters = []): ListActionsResponse
    {
        $this->requests[] = [
            'resource' => 'volume_actions',
            'method' => 'list',
            'parameters' => array_merge(['volumeId' => $volumeId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListActionsResponse::from($response, new ListActionsRequest($volumeId, $parameters));
        }

        return ListActionsResponse::fake(array_merge(['volumeId' => $volumeId], $parameters));
    }

    /**
     * Get a specific action for a volume (fake implementation).
     *
     * @param  string  $volumeId  The ID of the volume
     * @param  string  $actionId  The ID of the action to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $volumeId, string $actionId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'volume_actions',
            'method' => 'get',
            'parameters' => ['volumeId' => $volumeId, 'actionId' => $actionId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new GetActionRequest($volumeId, $actionId));
        }

        return ActionResponse::fake(['volumeId' => $volumeId, 'actionId' => $actionId]);
    }

    /**
     * Attach a volume to a server (fake implementation).
     *
     * @param  string  $volumeId  The ID of the volume
     * @param  array<string, mixed>  $parameters  The attachment parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function attach(string $volumeId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'volume_actions',
            'method' => 'attach',
            'parameters' => array_merge(['volumeId' => $volumeId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new AttachRequest($volumeId, $parameters));
        }

        return ActionResponse::fake(array_merge(['volumeId' => $volumeId, 'command' => 'attach_volume'], $parameters));
    }

    /**
     * Detach a volume from a server (fake implementation).
     *
     * @param  string  $volumeId  The ID of the volume
     *
     * @throws Throwable When a mock exception is provided
     */
    public function detach(string $volumeId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'volume_actions',
            'method' => 'detach',
            'parameters' => ['volumeId' => $volumeId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new DetachRequest($volumeId));
        }

        return ActionResponse::fake(['volumeId' => $volumeId, 'command' => 'detach_volume']);
    }

    /**
     * Resize a volume (fake implementation).
     *
     * @param  string  $volumeId  The ID of the volume
     * @param  array<string, mixed>  $parameters  The resize parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function resize(string $volumeId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'volume_actions',
            'method' => 'resize',
            'parameters' => array_merge(['volumeId' => $volumeId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ResizeRequest($volumeId, $parameters));
        }

        return ActionResponse::fake(array_merge(['volumeId' => $volumeId, 'command' => 'resize_volume'], $parameters));
    }

    /**
     * Change volume protection (fake implementation).
     *
     * @param  string  $volumeId  The ID of the volume
     * @param  array<string, mixed>  $parameters  The protection parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeProtection(string $volumeId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'volume_actions',
            'method' => 'change_protection',
            'parameters' => array_merge(['volumeId' => $volumeId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeProtectionRequest($volumeId, $parameters));
        }

        return ActionResponse::fake(array_merge(['volumeId' => $volumeId, 'command' => 'change_protection'], $parameters));
    }

    /**
     * Assert that a request was sent to the volume actions resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'volume_actions');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to volume_actions.');
        }
    }

    /**
     * Assert that no requests were sent to the volume actions resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'volume_actions');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to volume_actions.');
        }
    }
}
