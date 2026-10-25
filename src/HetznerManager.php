<?php

namespace Boci\HetznerLaravel;

use InvalidArgumentException;

/**
 * Hetzner Manager
 *
 * This class manages Hetzner Cloud API clients for multiple projects.
 * Every Hetzner Cloud API token belongs to exactly one project, so each
 * configured project gets its own client with its own token.
 *
 * @mixin Client
 */
class HetznerManager
{
    /** @var string The default base URI for the Hetzner Cloud API */
    private const DEFAULT_BASE_URI = 'https://api.hetzner.cloud';

    /**
     * @var array<string, Client> The resolved clients, keyed by project name
     */
    private array $clients = [];

    /** @var string|null The default project overridden at runtime */
    private ?string $defaultProject = null;

    /**
     * Create a new manager instance.
     *
     * @param  array<string, mixed>  $config  The package configuration
     */
    public function __construct(
        private readonly array $config,
    ) {}

    /**
     * Get the client for a configured project.
     *
     * @param  string|null  $name  The project name, or null for the default project
     *
     * @throws InvalidArgumentException When the project is not configured or has no token
     */
    public function project(?string $name = null): Client
    {
        $name ??= $this->getDefaultProject();

        return $this->clients[$name] ??= $this->resolve($name);
    }

    /**
     * Get a client for a token that is only known at runtime.
     *
     * The client is not cached, so tokens stored per tenant or per user
     * never leak between projects.
     *
     * @param  string  $token  The Hetzner Cloud API token
     * @param  string|null  $baseUri  Optional base URI for the API
     */
    public function withToken(string $token, ?string $baseUri = null): Client
    {
        return $this->make($token, $baseUri ?? self::DEFAULT_BASE_URI);
    }

    /**
     * Get the name of the default project.
     */
    public function getDefaultProject(): string
    {
        return $this->defaultProject ?? $this->config['default'] ?? 'default';
    }

    /**
     * Set the default project.
     *
     * @param  string  $name  The project name
     */
    public function setDefaultProject(string $name): self
    {
        $this->defaultProject = $name;

        return $this;
    }

    /**
     * Get the names of all configured projects.
     *
     * @return array<int, string>
     */
    public function projects(): array
    {
        return array_keys($this->projectsConfig());
    }

    /**
     * Forget a resolved project client, so the next call builds a new one.
     *
     * @param  string|null  $name  The project name, or null to forget all clients
     */
    public function forgetProject(?string $name = null): self
    {
        if ($name === null) {
            $this->clients = [];
        } else {
            unset($this->clients[$name]);
        }

        return $this;
    }

    /**
     * Resolve the client for a configured project.
     *
     * @param  string  $name  The project name
     *
     * @throws InvalidArgumentException When the project is not configured or has no token
     */
    private function resolve(string $name): Client
    {
        $projects = $this->projectsConfig();

        if (! isset($projects[$name])) {
            throw new InvalidArgumentException("Hetzner project [{$name}] is not configured.");
        }

        $token = $projects[$name]['token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new InvalidArgumentException("Hetzner project [{$name}] has no API token.");
        }

        return $this->make($token, $projects[$name]['base_url'] ?? self::DEFAULT_BASE_URI);
    }

    /**
     * Build a new client.
     *
     * @param  string  $token  The Hetzner Cloud API token
     * @param  string  $baseUri  The base URI for the API
     */
    private function make(string $token, string $baseUri): Client
    {
        return Client::factory()
            ->withApiKey($token)
            ->withBaseUri($baseUri)
            ->make();
    }

    /**
     * Get the configured projects.
     *
     * Configurations published before multiple projects were supported only
     * have a top-level token, which is then used for the default project.
     *
     * @return array<string, array<string, mixed>>
     */
    private function projectsConfig(): array
    {
        $projects = $this->config['projects'] ?? [];

        if (array_key_exists('token', $this->config)) {
            $default = $this->config['default'] ?? 'default';

            $projects[$default] = [
                'token' => $this->config['token'],
                'base_url' => $this->config['base_url'] ?? self::DEFAULT_BASE_URI,
            ];
        }

        return $projects;
    }

    /**
     * Pass method calls to the default project's client.
     *
     * @param  string  $method  The method name
     * @param  array<int, mixed>  $parameters  The method parameters
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->project()->{$method}(...$parameters);
    }
}
