<?php

namespace Boci\HetznerLaravel\Responses\Servers;

/**
 * Server Response
 *
 * This class represents a server object returned from the Hetzner Cloud API.
 * It provides convenient access to server properties and data.
 */
final class Server
{
    /**
     * Create a new server response instance.
     *
     * @param  array<string, mixed>  $data  The server data from the API
     */
    public function __construct(
        private readonly array $data,
    ) {}

    /**
     * Get the server ID.
     */
    public function id(): int
    {
        return $this->data['id'];
    }

    /**
     * Get the server name.
     */
    public function name(): string
    {
        return $this->data['name'];
    }

    /**
     * Get the server status.
     */
    public function status(): string
    {
        return $this->data['status'];
    }

    /**
     * Get the server creation date.
     */
    public function created(): string
    {
        return $this->data['created'];
    }

    /**
     * Get the public network configuration.
     *
     * @return array<string, mixed>
     */
    public function publicNet(): array
    {
        return $this->data['public_net'];
    }

    /**
     * Get the private network configuration.
     *
     * @return array<string, mixed>
     */
    public function privateNet(): array
    {
        return $this->data['private_net'] ?? [];
    }

    /**
     * Get the server type information.
     *
     * @return array<string, mixed>
     */
    public function serverType(): array
    {
        return $this->data['server_type'];
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
     * Get the image information.
     *
     * @return array<string, mixed>
     */
    public function image(): array
    {
        return $this->data['image'];
    }

    /**
     * Get the ISO information.
     *
     * @return array<string, mixed>|null
     */
    public function iso(): ?array
    {
        return $this->data['iso'] ?? null;
    }

    /**
     * Check if rescue mode is enabled.
     */
    public function rescueEnabled(): bool
    {
        return $this->data['rescue_enabled'];
    }

    /**
     * Check if the server is locked.
     */
    public function locked(): bool
    {
        return $this->data['locked'];
    }

    /**
     * Get the backup window.
     */
    public function backupWindow(): ?string
    {
        return $this->data['backup_window'] ?? null;
    }

    /**
     * Get the outgoing traffic amount.
     */
    public function outgoingTraffic(): ?int
    {
        return $this->data['outgoing_traffic'] ?? null;
    }

    /**
     * Get the ingoing traffic amount.
     */
    public function ingoingTraffic(): ?int
    {
        return $this->data['ingoing_traffic'] ?? null;
    }

    /**
     * Get the included traffic amount.
     */
    public function includedTraffic(): ?int
    {
        return $this->data['included_traffic'] ?? null;
    }

    /**
     * Get the protection settings.
     *
     * @return array<string, mixed>
     */
    public function protection(): array
    {
        return $this->data['protection'];
    }

    /**
     * Get the server labels.
     *
     * @return array<string, mixed>
     */
    public function labels(): array
    {
        return $this->data['labels'] ?? [];
    }

    /**
     * Get the attached volumes.
     *
     * @return array<string, mixed>
     */
    public function volumes(): array
    {
        return $this->data['volumes'] ?? [];
    }

    /**
     * Get the attached load balancers.
     *
     * @return array<string, mixed>
     */
    public function loadBalancers(): array
    {
        return $this->data['load_balancers'] ?? [];
    }

    /**
     * Convert the server data to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
