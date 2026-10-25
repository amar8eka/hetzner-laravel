<?php

namespace Tests;

use Boci\HetznerLaravel\Requests\Servers\RetrieveRequest;
use Boci\HetznerLaravel\Responses\Images\Image;
use Boci\HetznerLaravel\Responses\LoadBalancerTypes\LoadBalancerType;
use Boci\HetznerLaravel\Responses\PrimaryIPs\PrimaryIP;
use Boci\HetznerLaravel\Responses\Servers\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Servers\Server;
use Boci\HetznerLaravel\Responses\ServerTypes\ServerType;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Psr7\Response;

/**
 * Response Test Suite
 *
 * Test suite for the shared behaviour of the base response class,
 * such as magic property access to the response data.
 */
class ResponseTest extends TestCase
{
    /**
     * Test that response data can be read as properties
     */
    public function test_can_access_response_data_as_properties(): void
    {
        $response = RetrieveResponse::from(
            new Response(200, [], (string) json_encode(['server' => ['id' => 42, 'name' => 'web-1']])),
            new RetrieveRequest('42')
        );

        $this->assertTrue(isset($response->server));
        $this->assertSame(42, $response->server['id']);
        $this->assertSame('web-1', $response->server['name']);
    }

    /**
     * Test that missing response data returns null
     */
    public function test_missing_response_data_returns_null(): void
    {
        $response = RetrieveResponse::fake();

        $this->assertFalse(isset($response->missing));
        $this->assertNull($response->__get('missing'));
    }

    /**
     * Test that servers returned without a datacenter expose their location
     */
    public function test_server_location_without_datacenter(): void
    {
        $server = new Server([
            'id' => 42,
            'location' => ['id' => 1, 'name' => 'fsn1', 'city' => 'Falkenstein'],
        ]);

        $this->assertSame('fsn1', $server->location()['name'] ?? null);
        $this->assertNull($server->datacenter());
    }

    /**
     * Test that the location falls back to the datacenter location of older responses
     */
    public function test_server_location_falls_back_to_datacenter(): void
    {
        $server = new Server([
            'datacenter' => ['name' => 'nbg1-dc3', 'location' => ['name' => 'nbg1']],
        ]);

        $this->assertSame('nbg1', $server->location()['name'] ?? null);
    }

    /**
     * Test that primary IPs returned without a datacenter expose their location
     */
    public function test_primary_ip_location_without_datacenter(): void
    {
        $primaryIp = new PrimaryIP([
            'location' => ['name' => 'hel1'],
        ]);

        $this->assertSame('hel1', $primaryIp->location()['name'] ?? null);
        $this->assertNull($primaryIp->datacenter());
    }

    /**
     * Test the category and per-location details of server types
     */
    public function test_server_type_category_and_locations(): void
    {
        $serverType = new ServerType([
            'name' => 'cpx22',
            'category' => 'regular_purpose',
            'locations' => [
                ['id' => 1, 'name' => 'fsn1', 'deprecation' => null, 'available' => true, 'recommended' => true],
            ],
        ]);

        $this->assertSame('regular_purpose', $serverType->category());
        $this->assertSame('fsn1', $serverType->locations()[0]['name']);
        $this->assertTrue($serverType->locations()[0]['available']);
        $this->assertNull($serverType->deprecation());
    }

    /**
     * Test the deprecation details of images and load balancer types
     */
    public function test_deprecation_details(): void
    {
        $deprecation = ['announced' => '2026-08-31T00:00:00Z', 'unavailable_after' => '2026-11-30T00:00:00Z'];

        $image = new Image(['name' => 'debian-11', 'deprecation' => $deprecation]);
        $loadBalancerType = new LoadBalancerType(['name' => 'lb11']);

        $this->assertSame($deprecation, $image->deprecation());
        $this->assertNull($loadBalancerType->deprecation());
    }
}
