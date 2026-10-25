<?php

namespace Tests;

use Boci\HetznerLaravel\Client;
use Boci\HetznerLaravel\Facades\HetznerLaravel;
use Boci\HetznerLaravel\HetznerLaravelServiceProvider;
use Boci\HetznerLaravel\HetznerManager;
use Boci\HetznerLaravel\Resources\Servers;
use Illuminate\Foundation\Application;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use ReflectionProperty;

/**
 * Projects Test Suite
 *
 * Test suite for using multiple Hetzner Cloud projects, each with its own
 * API token, through the manager, the facade and the service container.
 */
class ProjectsTest extends TestCase
{
    /**
     * Get the package providers.
     *
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [HetznerLaravelServiceProvider::class];
    }

    /**
     * Define the environment setup.
     *
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('hetzner-laravel', [
            'default' => 'production',
            'projects' => [
                'production' => ['token' => 'production-token'],
                'staging' => ['token' => 'staging-token', 'base_url' => 'https://staging.example.com'],
                'empty' => ['token' => null],
            ],
        ]);
    }

    /**
     * Test that the default project is used when no project is given
     */
    public function test_uses_default_project_by_default(): void
    {
        $this->assertSame('production', HetznerLaravel::getDefaultProject());
        $this->assertSame('Bearer production-token', $this->authorization(HetznerLaravel::project()));
    }

    /**
     * Test that a named project uses its own token and base URL
     */
    public function test_named_project_uses_its_own_token(): void
    {
        $client = HetznerLaravel::project('staging');

        $this->assertSame('Bearer staging-token', $this->authorization($client));
        $this->assertSame('https://staging.example.com', (string) $this->httpClient($client)->getConfig('base_uri'));
    }

    /**
     * Test that project clients are resolved once
     */
    public function test_project_clients_are_cached(): void
    {
        $this->assertSame(HetznerLaravel::project('staging'), HetznerLaravel::project('staging'));
        $this->assertNotSame(HetznerLaravel::project('staging'), HetznerLaravel::project('production'));
    }

    /**
     * Test that a forgotten project client is built again
     */
    public function test_can_forget_project_clients(): void
    {
        $client = HetznerLaravel::project('staging');

        HetznerLaravel::forgetProject('staging');

        $this->assertNotSame($client, HetznerLaravel::project('staging'));
    }

    /**
     * Test that runtime tokens get their own client every time
     */
    public function test_with_token_creates_uncached_client(): void
    {
        $client = HetznerLaravel::withToken('tenant-token');

        $this->assertSame('Bearer tenant-token', $this->authorization($client));
        $this->assertNotSame($client, HetznerLaravel::withToken('tenant-token'));
    }

    /**
     * Test that the default project can be changed at runtime
     */
    public function test_can_change_default_project(): void
    {
        HetznerLaravel::setDefaultProject('staging');

        $this->assertSame('Bearer staging-token', $this->authorization(HetznerLaravel::project()));
        $this->assertSame('Bearer staging-token', $this->authorization(app(Client::class)));
    }

    /**
     * Test that resources are passed through to the default project
     */
    public function test_facade_resources_use_default_project(): void
    {
        $this->assertInstanceOf(Servers::class, HetznerLaravel::servers());
    }

    /**
     * Test that the client can still be injected
     */
    public function test_client_resolves_from_container(): void
    {
        $this->assertSame('Bearer production-token', $this->authorization(app(Client::class)));
        $this->assertSame('Bearer production-token', $this->authorization(app('hetzner.client')));
        $this->assertInstanceOf(HetznerManager::class, app('hetzner'));
    }

    /**
     * Test that all configured project names are listed
     */
    public function test_lists_configured_projects(): void
    {
        $this->assertSame(['production', 'staging', 'empty'], HetznerLaravel::projects());
    }

    /**
     * Test that an unknown project throws an exception
     */
    public function test_unknown_project_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hetzner project [unknown] is not configured.');

        HetznerLaravel::project('unknown');
    }

    /**
     * Test that a project without a token throws an exception
     */
    public function test_project_without_token_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hetzner project [empty] has no API token.');

        HetznerLaravel::project('empty');
    }

    /**
     * Test that a configuration published before multiple projects still works
     */
    public function test_legacy_token_configuration_is_used_for_default_project(): void
    {
        $manager = new HetznerManager([
            'token' => 'legacy-token',
            'default' => 'default',
            'projects' => [
                'default' => ['token' => 'package-default-token'],
            ],
        ]);

        $this->assertSame(['default'], $manager->projects());
        $this->assertSame('Bearer legacy-token', $this->authorization($manager->project()));
    }

    /**
     * Get the Guzzle client of a Hetzner client.
     */
    private function httpClient(Client $client): \GuzzleHttp\Client
    {
        return (new ReflectionProperty($client, 'httpClient'))->getValue($client);
    }

    /**
     * Get the Authorization header a Hetzner client sends.
     */
    private function authorization(Client $client): string
    {
        return $this->httpClient($client)->getConfig('headers')['Authorization'];
    }
}
