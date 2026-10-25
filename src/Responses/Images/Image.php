<?php

namespace Boci\HetznerLaravel\Responses\Images;

/**
 * Image Response
 *
 * This response class represents an image response from
 * the Hetzner Cloud API.
 */
final class Image
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private readonly array $data,
    ) {}

    /**
     * Get the image ID.
     */
    public function id(): int
    {
        return $this->data['id'];
    }

    /**
     * Get the image type.
     */
    public function type(): string
    {
        return $this->data['type'];
    }

    /**
     * Get the image status.
     */
    public function status(): string
    {
        return $this->data['status'];
    }

    /**
     * Get the image name.
     */
    public function name(): string
    {
        return $this->data['name'];
    }

    /**
     * Get the image description.
     */
    public function description(): string
    {
        return $this->data['description'];
    }

    /**
     * Get the image size.
     */
    public function imageSize(): ?float
    {
        return $this->data['image_size'] ?? null;
    }

    /**
     * Get the disk size.
     */
    public function diskSize(): float
    {
        return $this->data['disk_size'];
    }

    /**
     * Get when the image was created.
     */
    public function created(): string
    {
        return $this->data['created'];
    }

    /**
     * Get the source information for the image.
     *
     * @return array<string, mixed>|null
     */
    public function createdFrom(): ?array
    {
        return $this->data['created_from'] ?? null;
    }

    /**
     * Get the server ID this image is bound to.
     */
    public function boundTo(): ?int
    {
        return $this->data['bound_to'] ?? null;
    }

    /**
     * Get the operating system flavor.
     */
    public function osFlavor(): string
    {
        return $this->data['os_flavor'];
    }

    /**
     * Get the operating system version.
     */
    public function osVersion(): ?string
    {
        return $this->data['os_version'] ?? null;
    }

    /**
     * Check if rapid deploy is enabled.
     */
    public function rapidDeploy(): bool
    {
        return $this->data['rapid_deploy'];
    }

    /**
     * Get the protection settings for the image.
     *
     * @return array<string, mixed>
     */
    public function protection(): array
    {
        return $this->data['protection'];
    }

    /**
     * Get the labels for the image.
     *
     * @return array<string, mixed>
     */
    public function labels(): array
    {
        return $this->data['labels'] ?? [];
    }

    /**
     * Get when the image was deleted.
     */
    public function deleted(): ?string
    {
        return $this->data['deleted'] ?? null;
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
     * Get when the image was deprecated.
     *
     * @deprecated The Hetzner Cloud API removes this field on 2 November 2026, use deprecation() instead.
     */
    public function deprecated(): ?string
    {
        return $this->data['deprecated'] ?? null;
    }

    /**
     * Convert the image to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
