<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\AddServiceRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\AddTargetRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\AttachToNetworkRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\ChangeAlgorithmRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\ChangeProtectionRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\ChangeReverseDnsRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\ChangeTypeRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\DeleteServiceRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\DetachFromNetworkRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\DisablePublicInterfaceRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\EnablePublicInterfaceRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\GetActionRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\ListActionsRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\RemoveTargetRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerActions\UpdateServiceRequest;
use Boci\HetznerLaravel\Responses\LoadBalancerActions\ActionResponse;
use Boci\HetznerLaravel\Responses\LoadBalancerActions\ListActionsResponse;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Load Balancer Actions Fake
 *
 * This fake load balancer actions resource implements ResourceContract for testing purposes.
 * It allows you to mock API responses and assert that specific load balancer action requests
 * were made during testing.
 */
final class LoadBalancerActionsFake implements ResourceContract
{
    /**
     * Create a new fake load balancer actions resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all actions for a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(string $loadBalancerId, array $parameters = []): ListActionsResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'list',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListActionsResponse::from($response, new ListActionsRequest($loadBalancerId, $parameters));
        }

        return ListActionsResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters));
    }

    /**
     * Get a specific action for a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  string  $actionId  The ID of the action to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $loadBalancerId, string $actionId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'get',
            'parameters' => ['loadBalancerId' => $loadBalancerId, 'actionId' => $actionId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new GetActionRequest($loadBalancerId, $actionId));
        }

        return ActionResponse::fake(['loadBalancerId' => $loadBalancerId, 'actionId' => $actionId, 'command' => 'get_action']);
    }

    /**
     * Add a service to a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  The service parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function addService(string $loadBalancerId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'add_service',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new AddServiceRequest($loadBalancerId, $parameters));
        }

        return ActionResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters, ['command' => 'add_service']));
    }

    /**
     * Update a service on a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  The service update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function updateService(string $loadBalancerId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'update_service',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new UpdateServiceRequest($loadBalancerId, $parameters));
        }

        return ActionResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters, ['command' => 'update_service']));
    }

    /**
     * Delete a service from a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  The service deletion parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function deleteService(string $loadBalancerId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'delete_service',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new DeleteServiceRequest($loadBalancerId, $parameters));
        }

        return ActionResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters, ['command' => 'delete_service']));
    }

    /**
     * Add a target to a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  The target parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function addTarretrieve(string $loadBalancerId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'add_target',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new AddTargetRequest($loadBalancerId, $parameters));
        }

        return ActionResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters, ['command' => 'add_target']));
    }

    /**
     * Remove a target from a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  The target removal parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function removeTarretrieve(string $loadBalancerId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'remove_target',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new RemoveTargetRequest($loadBalancerId, $parameters));
        }

        return ActionResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters, ['command' => 'remove_target']));
    }

    /**
     * Change the algorithm of a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  The algorithm parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeAlgorithm(string $loadBalancerId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'change_algorithm',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeAlgorithmRequest($loadBalancerId, $parameters));
        }

        return ActionResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters, ['command' => 'change_algorithm']));
    }

    /**
     * Change reverse DNS entry for a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  The reverse DNS parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeReverseDns(string $loadBalancerId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'change_reverse_dns',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeReverseDnsRequest($loadBalancerId, $parameters));
        }

        return ActionResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters, ['command' => 'change_reverse_dns']));
    }

    /**
     * Change Load Balancer protection (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  The protection parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeProtection(string $loadBalancerId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'change_protection',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeProtectionRequest($loadBalancerId, $parameters));
        }

        return ActionResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters, ['command' => 'change_protection']));
    }

    /**
     * Change the type of a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  The type change parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeType(string $loadBalancerId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'change_type',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeTypeRequest($loadBalancerId, $parameters));
        }

        return ActionResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters, ['command' => 'change_type']));
    }

    /**
     * Attach a Load Balancer to a Network (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  The network attachment parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function attachToNetwork(string $loadBalancerId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'attach_to_network',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new AttachToNetworkRequest($loadBalancerId, $parameters));
        }

        return ActionResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters, ['command' => 'attach_to_network']));
    }

    /**
     * Detach a Load Balancer from a Network (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  The network detachment parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function detachFromNetwork(string $loadBalancerId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'detach_from_network',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new DetachFromNetworkRequest($loadBalancerId, $parameters));
        }

        return ActionResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters, ['command' => 'detach_from_network']));
    }

    /**
     * Enable the public interface of a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     *
     * @throws Throwable When a mock exception is provided
     */
    public function enablePublicInterface(string $loadBalancerId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'enable_public_interface',
            'parameters' => ['loadBalancerId' => $loadBalancerId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new EnablePublicInterfaceRequest($loadBalancerId));
        }

        return ActionResponse::fake(['loadBalancerId' => $loadBalancerId, 'command' => 'enable_public_interface']);
    }

    /**
     * Disable the public interface of a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     *
     * @throws Throwable When a mock exception is provided
     */
    public function disablePublicInterface(string $loadBalancerId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_actions',
            'method' => 'disable_public_interface',
            'parameters' => ['loadBalancerId' => $loadBalancerId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new DisablePublicInterfaceRequest($loadBalancerId));
        }

        return ActionResponse::fake(['loadBalancerId' => $loadBalancerId, 'command' => 'disable_public_interface']);
    }
}
