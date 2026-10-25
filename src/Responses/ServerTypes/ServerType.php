<?php

namespace Boci\HetznerLaravel\Responses\ServerTypes;

/**
 * Server Type Response
 *
 * This response class represents a server type response from
 * the Hetzner Cloud API.
 */
final class ServerType
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private readonly array $data,
    ) {}

    /**
     * Get the server type ID.
     */
    public function id(): int
    {
        return $this->data['id'];
    }

    /**
     * Get the server type name.
     */
    public function name(): string
    {
        return $this->data['name'];
    }

    /**
     * Get the server type description.
     */
    public function description(): string
    {
        return $this->data['description'];
    }

    /**
     * Get the number of CPU cores.
     */
    public function cores(): int
    {
        return $this->data['cores'];
    }

    /**
     * Get the memory amount in GB.
     */
    public function memory(): float
    {
        return $this->data['memory'];
    }

    /**
     * Get the disk size in GB.
     */
    public function disk(): int
    {
        return $this->data['disk'];
    }

    /**
     * Get the pricing information.
     *
     * @return array<string, mixed>
     */
    public function prices(): array
    {
        return $this->data['prices'];
    }

    /**
     * Get the storage type.
     */
    public function storageType(): string
    {
        return $this->data['storage_type'];
    }

    /**
     * Get the CPU type.
     */
    public function cpuType(): string
    {
        return $this->data['cpu_type'];
    }

    /**
     * Get the category, e.g. regular_purpose, cost_optimized or general_purpose.
     */
    public function category(): ?string
    {
        return $this->data['category'] ?? null;
    }

    /**
     * Get the locations this server type is offered in, with their
     * availability, recommendation and deprecation per location.
     *
     * @return array<int, array<string, mixed>>
     */
    public function locations(): array
    {
        return $this->data['locations'] ?? [];
    }

    /**
     * Get the deprecation information, or null when it is not deprecated.
     *
     * @deprecated Hetzner deprecates server types per location now, use locations() instead.
     *
     * @return array{announced: string, unavailable_after: string}|null
     */
    public function deprecation(): ?array
    {
        return $this->data['deprecation'] ?? null;
    }

    /**
     * Convert the server type to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
