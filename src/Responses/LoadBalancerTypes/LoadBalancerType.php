<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\LoadBalancerTypes;

/**
 * Load Balancer Type Response
 *
 * This response class represents a load balancer type response from
 * the Hetzner Cloud API.
 */
final class LoadBalancerType
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private readonly array $data
    ) {}

    /**
     * Get the load balancer type ID.
     */
    public function id(): int
    {
        return $this->data['id'];
    }

    /**
     * Get the load balancer type name.
     */
    public function name(): string
    {
        return $this->data['name'];
    }

    /**
     * Get the load balancer type description.
     */
    public function description(): string
    {
        return $this->data['description'];
    }

    /**
     * Get the maximum number of connections.
     */
    public function maxConnections(): int
    {
        return $this->data['max_connections'];
    }

    /**
     * Get the maximum number of services.
     */
    public function maxServices(): int
    {
        return $this->data['max_services'];
    }

    /**
     * Get the maximum number of targets.
     */
    public function maxTargets(): int
    {
        return $this->data['max_targets'];
    }

    /**
     * Get the maximum number of assigned certificates.
     */
    public function maxAssignedCertificates(): int
    {
        return $this->data['max_assigned_certificates'];
    }

    /**
     * Get the pricing information.
     *
     * @return array<string, mixed>
     */
    public function prices(): array
    {
        return $this->data['prices'] ?? [];
    }

    /**
     * Get the deprecation information, or null when it is not deprecated.
     *
     * @return array{announced: string, unavailable_after: string}|null
     */
    public function deprecation(): ?array
    {
        return $this->data['deprecation'] ?? null;
    }

    /**
     * Convert the load balancer type to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
