<?php

namespace Boci\HetznerLaravel\Responses;

use Boci\HetznerLaravel\Contracts\RequestContract;
use Boci\HetznerLaravel\Meta\MetaInformation;
use Psr\Http\Message\ResponseInterface;

/**
 * Abstract Response Class
 *
 * This abstract class provides the base functionality for all API response classes.
 * It handles response data parsing and provides access to metadata information.
 */
abstract class Response
{
    /**
     * @var array<string, mixed> The response data
     */
    protected readonly array $data;

    /** @var MetaInformation The response metadata */
    protected readonly MetaInformation $meta;

    /**
     * Create a new response instance.
     *
     * @param  array<string, mixed>  $data  The response data
     * @param  MetaInformation  $meta  The response metadata
     */
    public function __construct(
        array $data,
        MetaInformation $meta,
    ) {
        $this->data = $data;
        $this->meta = $meta;
    }

    /**
     * Create a response instance from an HTTP response.
     *
     * @param  ResponseInterface  $response  The HTTP response
     * @param  RequestContract  $request  The original request
     *
     * @throws \InvalidArgumentException When the response contains invalid JSON
     */
    public static function from(ResponseInterface $response, RequestContract $request): static
    {
        $responseBody = $response->getBody()->getContents();
        $data = json_decode($responseBody, true);

        // Handle cases where the response body is empty or invalid JSON
        if ($data === null && $responseBody !== '') {
            throw new \InvalidArgumentException('Invalid JSON response from API');
        }

        // If response body is empty, use empty array
        if ($data === null) {
            $data = [];
        }

        $meta = MetaInformation::from($response);

        // @phpstan-ignore-next-line
        return new static($data, $meta);
    }

    /**
     * Convert the response data to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Get the response metadata.
     */
    public function meta(): MetaInformation
    {
        return $this->meta;
    }

    /**
     * Get a property from the response data.
     *
     * @param  string  $name  The property name
     */
    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Check if a property exists in the response data.
     *
     * @param  string  $name  The property name
     */
    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Create a fake response instance for testing.
     *
     * @param  array<string, mixed>  $parameters  The parameters used to create the fake response
     */
    public static function fake(array $parameters = []): self
    {
        // Create minimal fake data based on the response type
        $data = static::generateFakeData($parameters);
        $meta = new MetaInformation;

        // @phpstan-ignore-next-line
        return new static($data, $meta);
    }

    /**
     * Generate fake data for the response.
     * Override this method in subclasses to provide specific fake data.
     *
     * @param  array<string, mixed>  $parameters  The parameters used to generate fake data
     * @return array<string, mixed>
     */
    protected static function generateFakeData(array $parameters = []): array
    {
        return [];
    }
}
