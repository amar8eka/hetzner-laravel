<?php

namespace Boci\HetznerLaravel\Testing;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Base Test Case
 *
 * This class provides common functionality for all Hetzner Laravel tests.
 * It includes utilities for creating fake clients and common assertions.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Create a fake client with optional responses and request tracking.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeClient(array &$responses = [], array &$requests = []): ClientFake
    {
        return new ClientFake($responses, $requests);
    }

    /**
     * Create a fake servers resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeServers(array $responses = [], array &$requests = []): ServersFake
    {
        return new ServersFake($responses, $requests);
    }

    /**
     * Create a fake images resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeImages(array $responses = [], array &$requests = []): ImagesFake
    {
        return new ImagesFake($responses, $requests);
    }

    /**
     * Create a fake locations resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeLocations(array $responses = [], array &$requests = []): LocationsFake
    {
        return new LocationsFake($responses, $requests);
    }

    /**
     * Create a fake server types resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeServerTypes(array $responses = [], array &$requests = []): ServerTypesFake
    {
        return new ServerTypesFake($responses, $requests);
    }

    /**
     * Create a fake SSH keys resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeSshKeys(array $responses = [], array &$requests = []): SshKeysFake
    {
        return new SshKeysFake($responses, $requests);
    }

    /**
     * Create a fake certificates resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeCertificates(array $responses = [], array &$requests = []): CertificatesFake
    {
        return new CertificatesFake($responses, $requests);
    }

    /**
     * Create a fake actions resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeActions(array $responses = [], array &$requests = []): ActionsFake
    {
        return new ActionsFake($responses, $requests);
    }

    /**
     * Create a fake billing resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeBilling(array $responses = [], array &$requests = []): BillingFake
    {
        return new BillingFake($responses, $requests);
    }

    /**
     * Create a fake firewalls resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeFirewalls(array $responses = [], array &$requests = []): FirewallsFake
    {
        return new FirewallsFake($responses, $requests);
    }

    /**
     * Create a fake floating IPs resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeFloatingIPs(array $responses = [], array &$requests = []): FloatingIPsFake
    {
        return new FloatingIPsFake($responses, $requests);
    }

    /**
     * Create a fake ISOs resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeISOs(array $responses = [], array &$requests = []): ISOsFake
    {
        return new ISOsFake($responses, $requests);
    }

    /**
     * Create a fake load balancers resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeLoadBalancers(array $responses = [], array &$requests = []): LoadBalancersFake
    {
        return new LoadBalancersFake($responses, $requests);
    }

    /**
     * Create a fake load balancer types resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeLoadBalancerTypes(array $responses = [], array &$requests = []): LoadBalancerTypesFake
    {
        return new LoadBalancerTypesFake($responses, $requests);
    }

    /**
     * Create a fake networks resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeNetworks(array $responses = [], array &$requests = []): NetworksFake
    {
        return new NetworksFake($responses, $requests);
    }

    /**
     * Create a fake placement groups resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakePlacementGroups(array $responses = [], array &$requests = []): PlacementGroupsFake
    {
        return new PlacementGroupsFake($responses, $requests);
    }

    /**
     * Create a fake primary IPs resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakePrimaryIPs(array $responses = [], array &$requests = []): PrimaryIPsFake
    {
        return new PrimaryIPsFake($responses, $requests);
    }

    /**
     * Create a fake volumes resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeVolumes(array $responses = [], array &$requests = []): VolumesFake
    {
        return new VolumesFake($responses, $requests);
    }

    /**
     * Create a fake firewall actions resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeFirewallActions(array $responses = [], array &$requests = []): FirewallActionsFake
    {
        return new FirewallActionsFake($responses, $requests);
    }

    /**
     * Create a fake floating IP actions resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeFloatingIPActions(array $responses = [], array &$requests = []): FloatingIPActionsFake
    {
        return new FloatingIPActionsFake($responses, $requests);
    }

    /**
     * Create a fake image actions resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeImageActions(array $responses = [], array &$requests = []): ImageActionsFake
    {
        return new ImageActionsFake($responses, $requests);
    }

    /**
     * Create a fake load balancer actions resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeLoadBalancerActions(array $responses = [], array &$requests = []): LoadBalancerActionsFake
    {
        return new LoadBalancerActionsFake($responses, $requests);
    }

    /**
     * Create a fake network actions resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeNetworkActions(array $responses = [], array &$requests = []): NetworkActionsFake
    {
        return new NetworkActionsFake($responses, $requests);
    }

    /**
     * Create a fake primary IP actions resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakePrimaryIPActions(array $responses = [], array &$requests = []): PrimaryIPActionsFake
    {
        return new PrimaryIPActionsFake($responses, $requests);
    }

    /**
     * Create a fake server actions resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeServerActions(array $responses = [], array &$requests = []): ServerActionsFake
    {
        return new ServerActionsFake($responses, $requests);
    }

    /**
     * Create a fake volume actions resource.
     *
     * @param  array<int, ResponseInterface|\Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function fakeVolumeActions(array $responses = [], array &$requests = []): VolumeActionsFake
    {
        return new VolumeActionsFake($responses, $requests);
    }

    /**
     * Assert that a request was made to a specific resource.
     *
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function assertRequestWasMade(
        array $requests,
        string $resource,
        string $method,
        ?callable $callback = null
    ): void {
        $filtered = array_filter($requests, function ($request) use ($resource, $method) {
            return $request['resource'] === $resource && $request['method'] === $method;
        });

        if ($callback) {
            $filtered = array_filter($filtered, $callback);
        }

        $this->assertNotEmpty($filtered, "No request was made to {$resource}::{$method}");
    }

    /**
     * Assert that no requests were made to a specific resource.
     *
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    protected function assertNoRequestWasMade(array $requests, string $resource): void
    {
        $filtered = array_filter($requests, function ($request) use ($resource) {
            return $request['resource'] === $resource;
        });

        $this->assertEmpty($filtered, "Requests were made to {$resource}");
    }
}
