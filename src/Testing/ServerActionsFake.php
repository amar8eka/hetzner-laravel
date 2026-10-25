<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\ServerActions\AddToPlacementGroupRequest;
use Boci\HetznerLaravel\Requests\ServerActions\AttachIsoRequest;
use Boci\HetznerLaravel\Requests\ServerActions\AttachToNetworkRequest;
use Boci\HetznerLaravel\Requests\ServerActions\ChangeAliasIpsRequest;
use Boci\HetznerLaravel\Requests\ServerActions\ChangeProtectionRequest;
use Boci\HetznerLaravel\Requests\ServerActions\ChangeReverseDnsRequest;
use Boci\HetznerLaravel\Requests\ServerActions\ChangeServerTypeRequest;
use Boci\HetznerLaravel\Requests\ServerActions\CreateImageRequest;
use Boci\HetznerLaravel\Requests\ServerActions\DetachFromNetworkRequest;
use Boci\HetznerLaravel\Requests\ServerActions\DetachIsoRequest;
use Boci\HetznerLaravel\Requests\ServerActions\DisableBackupsRequest;
use Boci\HetznerLaravel\Requests\ServerActions\DisableRescueModeRequest;
use Boci\HetznerLaravel\Requests\ServerActions\EnableBackupsRequest;
use Boci\HetznerLaravel\Requests\ServerActions\EnableRescueModeRequest;
use Boci\HetznerLaravel\Requests\ServerActions\ListActionsRequest;
use Boci\HetznerLaravel\Requests\ServerActions\PowerOffRequest;
use Boci\HetznerLaravel\Requests\ServerActions\PowerOnRequest;
use Boci\HetznerLaravel\Requests\ServerActions\RebootRequest;
use Boci\HetznerLaravel\Requests\ServerActions\RebuildRequest;
use Boci\HetznerLaravel\Requests\ServerActions\RemoveFromPlacementGroupRequest;
use Boci\HetznerLaravel\Requests\ServerActions\RequestConsoleRequest;
use Boci\HetznerLaravel\Requests\ServerActions\ResetPasswordRequest;
use Boci\HetznerLaravel\Requests\ServerActions\ResetRequest;
use Boci\HetznerLaravel\Requests\ServerActions\ShutdownRequest;
use Boci\HetznerLaravel\Responses\ServerActions\ActionResponse;
use Boci\HetznerLaravel\Responses\ServerActions\ConsoleResponse;
use Boci\HetznerLaravel\Responses\ServerActions\ListActionsResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Server Actions Fake
 *
 * This fake server actions resource extends the real server actions resource for testing purposes.
 * It allows you to mock API responses and assert that specific server action requests
 * were made during testing.
 */
final class ServerActionsFake implements ResourceContract
{
    /**
     * Create a new fake server actions resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all actions for a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(string $serverId, array $parameters = []): ListActionsResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'list',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListActionsResponse::from($response, new ListActionsRequest($serverId, $parameters));
        }

        return ListActionsResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Power on a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function powerOn(string $serverId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'power_on',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new PowerOnRequest($serverId));
        }

        return ActionResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Power off a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function powerOff(string $serverId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'power_off',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new PowerOffRequest($serverId));
        }

        return ActionResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Reboot a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function reboot(string $serverId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'reboot',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new RebootRequest($serverId));
        }

        return ActionResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Shutdown a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function shutdown(string $serverId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'shutdown',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ShutdownRequest($serverId));
        }

        return ActionResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Reset a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function reset(string $serverId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'reset',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ResetRequest($serverId));
        }

        return ActionResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Change protection settings for a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  The protection parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeProtection(string $serverId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'change_protection',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeProtectionRequest($serverId, $parameters));
        }

        return ActionResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Change the type of a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  The server type parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeServerType(string $serverId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'change_server_type',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeServerTypeRequest($serverId, $parameters));
        }

        return ActionResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Rebuild a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  The rebuild parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function rebuild(string $serverId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'rebuild',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new RebuildRequest($serverId, $parameters));
        }

        return ActionResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Create an image from a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  The image creation parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function createImage(string $serverId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'create_image',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new CreateImageRequest($serverId, $parameters));
        }

        return ActionResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Reset password for a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function resetPassword(string $serverId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'reset_password',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ResetPasswordRequest($serverId));
        }

        return ActionResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Request console for a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function requestConsole(string $serverId): ConsoleResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'request_console',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ConsoleResponse::from($response, new RequestConsoleRequest($serverId));
        }

        return ConsoleResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Attach a server to a network (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  The network attachment parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function attachToNetwork(string $serverId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'attach_to_network',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new AttachToNetworkRequest($serverId, $parameters));
        }

        return ActionResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Detach a server from a network (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  The network detachment parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function detachFromNetwork(string $serverId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'detach_from_network',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new DetachFromNetworkRequest($serverId, $parameters));
        }

        return ActionResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Change alias IPs for a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  The alias IP parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeAliasIps(string $serverId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'change_alias_ips',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeAliasIpsRequest($serverId, $parameters));
        }

        return ActionResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Attach an ISO to a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  The ISO attachment parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function attachIso(string $serverId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'attach_iso',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new AttachIsoRequest($serverId, $parameters));
        }

        return ActionResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Detach an ISO from a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function detachIso(string $serverId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'detach_iso',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new DetachIsoRequest($serverId));
        }

        return ActionResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Add a server to a placement group (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  The placement group parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function addToPlacementGroup(string $serverId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'add_to_placement_group',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new AddToPlacementGroupRequest($serverId, $parameters));
        }

        return ActionResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Remove a server from a placement group (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function removeFromPlacementGroup(string $serverId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'remove_from_placement_group',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new RemoveFromPlacementGroupRequest($serverId));
        }

        return ActionResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Enable backups for a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function enableBackups(string $serverId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'enable_backups',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new EnableBackupsRequest($serverId));
        }

        return ActionResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Disable backups for a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function disableBackups(string $serverId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'disable_backups',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new DisableBackupsRequest($serverId));
        }

        return ActionResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Enable rescue mode for a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  The rescue mode parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function enableRescueMode(string $serverId, array $parameters = []): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'enable_rescue_mode',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new EnableRescueModeRequest($serverId, $parameters));
        }

        return ActionResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Disable rescue mode for a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     *
     * @throws Throwable When a mock exception is provided
     */
    public function disableRescueMode(string $serverId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'disable_rescue_mode',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new DisableRescueModeRequest($serverId));
        }

        return ActionResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Change reverse DNS for a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server
     * @param  array<string, mixed>  $parameters  The reverse DNS parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeReverseDns(string $serverId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'server_actions',
            'method' => 'change_reverse_dns',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeReverseDnsRequest($serverId, $parameters));
        }

        return ActionResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Assert that a request was sent to the server actions resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'server_actions');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to server_actions.');
        }
    }

    /**
     * Assert that no requests were sent to the server actions resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'server_actions');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to server_actions.');
        }
    }
}
