<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\PrimaryIPs;

/**
 * Primary IP Response
 *
 * This response class represents a primary IP response from
 * the Hetzner Cloud API.
 */
final class PrimaryIP
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private readonly array $data
    ) {}

    /**
     * Get the primary IP ID.
     */
    public function id(): int
    {
        return $this->data['id'];
    }

    /**
     * Get the IP address.
     */
    public function ip(): string
    {
        return $this->data['ip'];
    }

    /**
     * Get the IP type.
     */
    public function type(): string
    {
        return $this->data['type'];
    }

    /**
     * Get the assignee ID.
     */
    public function assigneeId(): ?int
    {
        return $this->data['assignee_id'] ?? null;
    }

    /**
     * Get the assignee type.
     */
    public function assigneeType(): ?string
    {
        return $this->data['assignee_type'] ?? null;
    }

    /**
     * Check if auto delete is enabled.
     */
    public function autoDelete(): bool
    {
        return $this->data['auto_delete'] ?? false;
    }

    /**
     * Check if the IP is blocked.
     */
    public function blocked(): bool
    {
        return $this->data['blocked'] ?? false;
    }

    /**
     * Get when the primary IP was created.
     */
    public function created(): string
    {
        return $this->data['created'];
    }

    /**
     * Get the location information.
     *
     * @return array<string, mixed>|null
     */
    public function location(): ?array
    {
        return $this->data['location'] ?? $this->data['datacenter']['location'] ?? null;
    }

    /**
     * Get the datacenter information.
     *
     * @deprecated The Hetzner Cloud API no longer returns the datacenter since 1 July 2026, use location() instead.
     *
     * @return array<string, mixed>|null
     */
    public function datacenter(): ?array
    {
        return $this->data['datacenter'] ?? null;
    }

    /**
     * Get the DNS PTR records.
     *
     * @return array<string, mixed>
     */
    public function dnsPtr(): array
    {
        return $this->data['dns_ptr'] ?? [];
    }

    /**
     * Get the home location information.
     *
     * @return array<string, mixed>
     */
    public function homeLocation(): array
    {
        return $this->data['home_location'];
    }

    /**
     * Get the labels.
     *
     * @return array<string, mixed>
     */
    public function labels(): array
    {
        return $this->data['labels'] ?? [];
    }

    /**
     * Get the primary IP name.
     */
    public function name(): string
    {
        return $this->data['name'];
    }

    /**
     * Get the protection settings.
     *
     * @return array<string, mixed>
     */
    public function protection(): array
    {
        return $this->data['protection'] ?? [];
    }

    /**
     * Convert the primary IP to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
