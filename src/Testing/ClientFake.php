<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Client;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Client Fake
 *
 * This fake client provides the same interface as the real client for testing purposes.
 * It allows you to mock API responses and assert that specific
 * requests were made during testing.
 */
final class ClientFake
{
    /**
     * @var array<int, ResponseInterface|Throwable> The mock responses
     */
    private array $responses;

    /**
     * @var array<int, array{resource: string, method: string, parameters: array}> The recorded requests
     */
    private array $requests = [];

    /**
     * Create a new fake client instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses to return
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(array &$responses = [], array &$requests = [])
    {
        $this->responses = &$responses;
        $this->requests = &$requests;
    }

    /**
     * Get the fake actions resource.
     */
    public function actions(): ActionsFake
    {
        return new ActionsFake($this->responses, $this->requests);
    }

    /**
     * Get the fake firewalls resource.
     */
    public function firewalls(): FirewallsFake
    {
        return new FirewallsFake($this->responses, $this->requests);
    }

    /**
     * Get the fake servers resource.
     */
    public function servers(): ServersFake
    {
        return new ServersFake($this->responses, $this->requests);
    }

    /**
     * Get the fake images resource.
     */
    public function images(): ImagesFake
    {
        return new ImagesFake($this->responses, $this->requests);
    }

    /**
     * Get the ISOs fake resource.
     */
    public function isos(): ISOsFake
    {
        return new ISOsFake($this->responses, $this->requests);
    }

    /**
     * Get the Load Balancers fake resource.
     */
    public function loadBalancers(): LoadBalancersFake
    {
        return new LoadBalancersFake($this->responses, $this->requests);
    }

    /**
     * Get the Load Balancer Types fake resource.
     */
    public function loadBalancerTypes(): LoadBalancerTypesFake
    {
        return new LoadBalancerTypesFake($this->responses, $this->requests);
    }

    /**
     * Get the Networks fake resource.
     */
    public function networks(): NetworksFake
    {
        return new NetworksFake($this->responses, $this->requests);
    }

    /**
     * Get the Placement Groups fake resource.
     */
    public function placementGroups(): PlacementGroupsFake
    {
        return new PlacementGroupsFake($this->responses, $this->requests);
    }

    /**
     * Get the fake locations resource.
     */
    public function locations(): LocationsFake
    {
        return new LocationsFake($this->responses, $this->requests);
    }

    /**
     * Get the fake server types resource.
     */
    public function serverTypes(): ServerTypesFake
    {
        return new ServerTypesFake($this->responses, $this->requests);
    }

    /**
     * Get the fake SSH keys resource.
     */
    public function sshKeys(): SshKeysFake
    {
        return new SshKeysFake($this->responses, $this->requests);
    }

    /**
     * Get the fake certificates resource.
     */
    public function certificates(): CertificatesFake
    {
        return new CertificatesFake($this->responses, $this->requests);
    }

    /**
     * Get the fake volumes resource.
     */
    public function volumes(): VolumesFake
    {
        return new VolumesFake($this->responses, $this->requests);
    }

    public function floatingIPs(): FloatingIPsFake
    {
        return new FloatingIPsFake($this->responses, $this->requests);
    }

    public function primaryIPs(): PrimaryIPsFake
    {
        return new PrimaryIPsFake($this->responses, $this->requests);
    }

    public function dnsZones(): DnsZonesFake
    {
        return new DnsZonesFake($this->responses, $this->requests);
    }

    /**
     * Get the fake server actions resource.
     */
    public function serverActions(): ServerActionsFake
    {
        return new ServerActionsFake($this->responses, $this->requests);
    }

    /**
     * Get the fake billing resource.
     */
    public function billing(): BillingFake
    {
        return new BillingFake($this->responses, $this->requests);
    }

    /**
     * Assert that a request was sent to a specific resource.
     *
     * @param  class-string  $resource  The resource class name
     * @param  callable|int|null  $callback  Optional callback to filter requests or expected count
     */
    public function assertSent(string $resource, callable|int|null $callback = null): void
    {
        if (is_int($callback)) {
            $this->assertSentTimes($resource, $callback);

            return;
        }

        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === $resource);

        if ($callback) {
            $sent = array_filter($sent, $callback);
        }

        if (empty($sent)) {
            throw new AssertionFailedError("No requests were sent to {$resource}.");
        }
    }

    /**
     * Assert that no requests were sent to a specific resource.
     *
     * @param  class-string  $resource  The resource class name
     */
    public function assertNotSent(string $resource): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === $resource);

        if (! empty($sent)) {
            throw new AssertionFailedError("Requests were sent to {$resource}.");
        }
    }

    /**
     * Assert that no requests were sent at all.
     */
    public function assertNothingSent(): void
    {
        if (! empty($this->requests)) {
            throw new AssertionFailedError('Requests were sent.');
        }
    }

    /**
     * Assert that a specific number of requests were sent to a resource.
     *
     * @param  class-string  $resource  The resource class name
     * @param  int  $times  The expected number of requests
     */
    private function assertSentTimes(string $resource, int $times): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === $resource);

        if (count($sent) !== $times) {
            throw new AssertionFailedError("Expected {$times} requests to {$resource}, but {$count} were sent.");
        }
    }
}
