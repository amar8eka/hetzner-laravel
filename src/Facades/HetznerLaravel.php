<?php

namespace Boci\HetznerLaravel\Facades;

use Boci\HetznerLaravel\HetznerManager;
use Illuminate\Support\Facades\Facade;

/**
 * Hetzner Laravel Facade
 *
 * This facade provides static access to the Hetzner Cloud API client and its resources.
 * It allows for convenient access to all Hetzner Cloud API functionality through
 * Laravel's facade system, for the default project or any configured project.
 *
 * @method static \Boci\HetznerLaravel\Client project(?string $name = null) Get the client for a configured project
 * @method static \Boci\HetznerLaravel\Client withToken(string $token, ?string $baseUri = null) Get a client for a runtime token
 * @method static string getDefaultProject() Get the name of the default project
 * @method static \Boci\HetznerLaravel\HetznerManager setDefaultProject(string $name) Set the default project
 * @method static array<int, string> projects() Get the names of all configured projects
 * @method static \Boci\HetznerLaravel\HetznerManager forgetProject(?string $name = null) Forget a resolved project client
 * @method static \Boci\HetznerLaravel\Resources\Servers servers() Get the servers resource
 * @method static \Boci\HetznerLaravel\Resources\Images images() Get the images resource
 * @method static \Boci\HetznerLaravel\Resources\Locations locations() Get the locations resource
 * @method static \Boci\HetznerLaravel\Resources\ServerTypes serverTypes() Get the server types resource
 * @method static \Boci\HetznerLaravel\Resources\SshKeys sshKeys() Get the SSH keys resource
 * @method static \Boci\HetznerLaravel\Resources\Certificates certificates() Get the certificates resource
 * @method static \Boci\HetznerLaravel\Resources\Networks networks() Get the networks resource
 * @method static \Boci\HetznerLaravel\Resources\Firewalls firewalls() Get the firewalls resource
 * @method static \Boci\HetznerLaravel\Resources\FloatingIPs floatingIPs() Get the floating IPs resource
 * @method static \Boci\HetznerLaravel\Resources\Actions actions() Get the actions resource
 * @method static \Boci\HetznerLaravel\Resources\Billing billing() Get the billing resource
 * @method static \Boci\HetznerLaravel\Resources\Volumes volumes() Get the volumes resource
 * @method static \Boci\HetznerLaravel\Resources\PlacementGroups placementGroups() Get the placement groups resource
 * @method static \Boci\HetznerLaravel\Resources\PrimaryIPs primaryIPs() Get the primary IPs resource
 * @method static \Boci\HetznerLaravel\Resources\LoadBalancers loadBalancers() Get the load balancers resource
 * @method static \Boci\HetznerLaravel\Resources\LoadBalancerTypes loadBalancerTypes() Get the load balancer types resource
 * @method static \Boci\HetznerLaravel\Resources\ISOs isos() Get the ISOs resource
 * @method static \Boci\HetznerLaravel\Resources\DnsZones dnsZones() Get the DNS zones resource
 */
class HetznerLaravel extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return HetznerManager::class;
    }
}
