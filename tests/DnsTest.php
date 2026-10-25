<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\DnsRrsets\ActionResponse as RRSetActionResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\CreateResponse as RRSetCreateResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\DeleteResponse as RRSetDeleteResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\GetResponse as RRSetGetResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\ListResponse as RRSetListResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\RRSet;
use Boci\HetznerLaravel\Responses\DnsRrsets\UpdateResponse as RRSetUpdateResponse;
use Boci\HetznerLaravel\Responses\DnsZoneActions\ActionResponse;
use Boci\HetznerLaravel\Responses\DnsZoneActions\ListActionsResponse;
use Boci\HetznerLaravel\Responses\DnsZones\CreateResponse;
use Boci\HetznerLaravel\Responses\DnsZones\DeleteResponse;
use Boci\HetznerLaravel\Responses\DnsZones\DnsZone;
use Boci\HetznerLaravel\Responses\DnsZones\ExportResponse;
use Boci\HetznerLaravel\Responses\DnsZones\ImportResponse;
use Boci\HetznerLaravel\Responses\DnsZones\ListResponse;
use Boci\HetznerLaravel\Responses\DnsZones\RetrieveResponse;
use Boci\HetznerLaravel\Responses\DnsZones\UpdateResponse;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * DNS Test Suite
 *
 * This test suite covers all functionality related to the DNS resource,
 * including DNS zones, zone actions, and RRSets based on the Hetzner Cloud DNS API.
 */
final class DnsTest extends TestCase
{
    /**
     * Test listing DNS zones with fake data
     */
    public function test_can_list_dns_zones_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->dnsZones()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertIsArray($response->zones());
        $this->assertCount(1, $response->zones());

        $zones = $response->zones();
        $this->assertInstanceOf(DnsZone::class, $zones[0]);
        $this->assertEquals('example.com', $zones[0]->id());
        $this->assertEquals('example.com', $zones[0]->name());
        $this->assertEquals(3600, $zones[0]->ttl());
        $this->assertFalse($zones[0]->isSecondaryDns());
        $this->assertFalse($zones[0]->paused());
        $this->assertEquals('full_access', $zones[0]->permission());
        $this->assertEquals('my-project', $zones[0]->project());
        $this->assertEquals('verified', $zones[0]->status());
        $this->assertEquals(5, $zones[0]->recordsCount());
        $this->assertTrue($zones[0]->isPrimaryDns());
        $this->assertIsArray($zones[0]->ns());
        $this->assertIsArray($zones[0]->legacyNs());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $zones[0]->created());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $zones[0]->modified());

        $this->assertRequestWasMade($requests, 'dns_zones', 'list');
    }

    /**
     * Test listing DNS zones with custom response
     */
    public function test_can_list_dns_zones_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'zones' => [
                    [
                        'id' => 'custom.com',
                        'name' => 'custom.com',
                        'ttl' => 7200,
                        'created' => '2023-06-15T10:30:00+00:00',
                        'modified' => '2023-06-15T10:30:00+00:00',
                        'is_secondary_dns' => true,
                        'legacy_dns_host' => 'legacy-dns.custom.com',
                        'legacy_ns' => ['ns1.custom.com', 'ns2.custom.com'],
                        'ns' => ['ns1.custom.com', 'ns2.custom.com'],
                        'owner' => 'admin@custom.com',
                        'paused' => true,
                        'permission' => 'read_only',
                        'project' => 'custom-project',
                        'registrar' => 'custom-registrar',
                        'status' => 'pending',
                        'verified' => null,
                        'records_count' => 10,
                        'is_primary_dns' => false,
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 1,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->dnsZones()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->zones());

        $zone = $response->zones()[0];
        $this->assertEquals('custom.com', $zone->id());
        $this->assertEquals('custom.com', $zone->name());
        $this->assertEquals(7200, $zone->ttl());
        $this->assertTrue($zone->isSecondaryDns());
        $this->assertTrue($zone->paused());
        $this->assertEquals('read_only', $zone->permission());
        $this->assertEquals('custom-project', $zone->project());
        $this->assertEquals('pending', $zone->status());
        $this->assertNull($zone->verified());
        $this->assertEquals(10, $zone->recordsCount());
        $this->assertFalse($zone->isPrimaryDns());

        $this->assertRequestWasMade($requests, 'dns_zones', 'list');
    }

    /**
     * Test listing DNS zones with query parameters
     */
    public function test_can_list_dns_zones_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'my-zone.com',
        ];

        $response = $client->dnsZones()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);

        $this->assertRequestWasMade($requests, 'dns_zones', 'list');
    }

    /**
     * Test creating a DNS zone
     */
    public function test_can_create_dns_zone(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'new-zone.com',
            'ttl' => 3600,
        ];

        $response = $client->dnsZones()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $this->assertInstanceOf(DnsZone::class, $response->zone());

        $zone = $response->zone();
        $this->assertEquals('new-zone.com', $zone->id());
        $this->assertEquals('new-zone.com', $zone->name());
        $this->assertEquals(3600, $zone->ttl());
        $this->assertFalse($zone->isSecondaryDns());
        $this->assertFalse($zone->paused());
        $this->assertEquals('full_access', $zone->permission());
        $this->assertEquals('my-project', $zone->project());
        $this->assertEquals('verified', $zone->status());
        $this->assertEquals(0, $zone->recordsCount());
        $this->assertTrue($zone->isPrimaryDns());

        $this->assertRequestWasMade($requests, 'dns_zones', 'create');
    }

    /**
     * Test creating DNS zone with custom response
     */
    public function test_can_create_dns_zone_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'zone' => [
                    'id' => 'custom-new-zone.com',
                    'name' => 'custom-new-zone.com',
                    'ttl' => 1800,
                    'created' => '2023-06-20T09:15:00+00:00',
                    'modified' => '2023-06-20T09:15:00+00:00',
                    'is_secondary_dns' => false,
                    'legacy_dns_host' => 'legacy-dns.example.com',
                    'legacy_ns' => ['ns1.example.com', 'ns2.example.com'],
                    'ns' => ['ns1.example.com', 'ns2.example.com'],
                    'owner' => 'admin@example.com',
                    'paused' => false,
                    'permission' => 'full_access',
                    'project' => 'my-project',
                    'registrar' => 'example-registrar',
                    'status' => 'verified',
                    'verified' => '2023-06-20T09:15:00+00:00',
                    'records_count' => 0,
                    'is_primary_dns' => true,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'custom-new-zone.com',
            'ttl' => 1800,
        ];

        $response = $client->dnsZones()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $zone = $response->zone();

        $this->assertEquals('custom-new-zone.com', $zone->id());
        $this->assertEquals('custom-new-zone.com', $zone->name());
        $this->assertEquals(1800, $zone->ttl());
        $this->assertEquals('2023-06-20T09:15:00+00:00', $zone->created());
        $this->assertEquals('2023-06-20T09:15:00+00:00', $zone->modified());

        $this->assertRequestWasMade($requests, 'dns_zones', 'create');
    }

    /**
     * Test getting a specific DNS zone
     */
    public function test_can_get_specific_dns_zone(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $response = $client->dnsZones()->retrieve($zoneIdOrName);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $this->assertInstanceOf(DnsZone::class, $response->zone());

        $zone = $response->zone();
        $this->assertEquals('example.com', $zone->id());
        $this->assertEquals('example.com', $zone->name());
        $this->assertEquals(3600, $zone->ttl());
        $this->assertFalse($zone->isSecondaryDns());
        $this->assertFalse($zone->paused());
        $this->assertEquals('full_access', $zone->permission());
        $this->assertEquals('my-project', $zone->project());
        $this->assertEquals('verified', $zone->status());
        $this->assertEquals(5, $zone->recordsCount());
        $this->assertTrue($zone->isPrimaryDns());
        $this->assertIsArray($zone->ns());
        $this->assertIsArray($zone->legacyNs());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $zone->created());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $zone->modified());

        $this->assertRequestWasMade($requests, 'dns_zones', 'get');
    }

    /**
     * Test getting DNS zone with custom response
     */
    public function test_can_get_dns_zone_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'zone' => [
                    'id' => 'detailed-zone.com',
                    'name' => 'detailed-zone.com',
                    'ttl' => 7200,
                    'created' => '2023-05-10T16:20:00+00:00',
                    'modified' => '2023-05-10T16:20:00+00:00',
                    'is_secondary_dns' => false,
                    'legacy_dns_host' => 'legacy-dns.example.com',
                    'legacy_ns' => ['ns1.example.com', 'ns2.example.com'],
                    'ns' => ['ns1.example.com', 'ns2.example.com'],
                    'owner' => 'admin@example.com',
                    'paused' => false,
                    'permission' => 'full_access',
                    'project' => 'my-project',
                    'registrar' => 'example-registrar',
                    'status' => 'verified',
                    'verified' => '2023-05-10T16:20:00+00:00',
                    'records_count' => 15,
                    'is_primary_dns' => true,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'detailed-zone.com';
        $response = $client->dnsZones()->retrieve($zoneIdOrName);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $zone = $response->zone();

        $this->assertEquals('detailed-zone.com', $zone->id());
        $this->assertEquals('detailed-zone.com', $zone->name());
        $this->assertEquals(7200, $zone->ttl());
        $this->assertEquals('2023-05-10T16:20:00+00:00', $zone->created());
        $this->assertEquals('2023-05-10T16:20:00+00:00', $zone->modified());
        $this->assertEquals(15, $zone->recordsCount());

        $this->assertRequestWasMade($requests, 'dns_zones', 'get');
    }

    /**
     * Test updating a DNS zone
     */
    public function test_can_update_dns_zone(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $parameters = [
            'ttl' => 7200,
        ];

        $response = $client->dnsZones()->update($zoneIdOrName, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);
        $this->assertInstanceOf(DnsZone::class, $response->zone());

        $zone = $response->zone();
        $this->assertEquals('example.com', $zone->id());
        $this->assertEquals('example.com', $zone->name());
        $this->assertEquals(7200, $zone->ttl());

        $this->assertRequestWasMade($requests, 'dns_zones', 'update');
    }

    /**
     * Test deleting a DNS zone
     */
    public function test_can_delete_dns_zone(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $response = $client->dnsZones()->delete($zoneIdOrName);

        $this->assertInstanceOf(DeleteResponse::class, $response);

        $this->assertRequestWasMade($requests, 'dns_zones', 'delete');
    }

    /**
     * Test exporting a DNS zone file
     */
    public function test_can_export_dns_zone(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $response = $client->dnsZones()->export($zoneIdOrName);

        $this->assertInstanceOf(ExportResponse::class, $response);
        $this->assertIsString($response->zoneFile());
        $this->assertStringContainsString('example.com', $response->zoneFile());
        $this->assertStringContainsString('$ORIGIN', $response->zoneFile());
        $this->assertStringContainsString('$TTL', $response->zoneFile());
        $this->assertStringContainsString('SOA', $response->zoneFile());
        $this->assertStringContainsString('NS', $response->zoneFile());
        $this->assertStringContainsString('A', $response->zoneFile());

        $this->assertRequestWasMade($requests, 'dns_zones', 'export');
    }

    /**
     * Test importing a DNS zone file
     */
    public function test_can_import_dns_zone(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'imported.com',
            'zone_file' => '; Zone file for imported.com
$ORIGIN imported.com.
$TTL 3600
@   IN  SOA ns1.imported.com. admin.imported.com. (
    2023010100  ; serial
    3600        ; refresh
    1800        ; retry
    604800      ; expire
    86400       ; minimum
)
@   IN  NS  ns1.imported.com.
@   IN  A   192.168.1.1
',
        ];

        $response = $client->dnsZones()->import($parameters);

        $this->assertInstanceOf(ImportResponse::class, $response);
        $this->assertInstanceOf(DnsZone::class, $response->zone());

        $zone = $response->zone();
        $this->assertEquals('imported.com', $zone->id());
        $this->assertEquals('imported.com', $zone->name());
        $this->assertEquals(3600, $zone->ttl());
        $this->assertEquals(3, $zone->recordsCount());

        $this->assertRequestWasMade($requests, 'dns_zones', 'import');
    }

    /**
     * Test DNS zone response structure
     */
    public function test_dns_zone_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->dnsZones()->list();
        $zones = $response->zones();

        foreach ($zones as $zone) {
            // Test all DNS zone properties
            $this->assertIsString($zone->id());
            $this->assertIsString($zone->name());
            $this->assertIsInt($zone->ttl());
            $this->assertIsBool($zone->isSecondaryDns());
            $this->assertIsBool($zone->paused());
            $this->assertIsString($zone->permission());
            $this->assertIsString($zone->project());
            $this->assertIsString($zone->status());
            $this->assertIsInt($zone->recordsCount());
            $this->assertIsBool($zone->isPrimaryDns());
            $this->assertIsArray($zone->ns());
            $this->assertIsArray($zone->legacyNs());
            $this->assertIsString($zone->created());
            $this->assertIsString($zone->modified());

            // Test DNS zone status values
            $this->assertContains($zone->status(), ['verified', 'pending', 'failed']);

            // Test DNS zone permission values
            $this->assertContains($zone->permission(), ['full_access', 'read_only']);

            // Test nameservers structure
            foreach ($zone->ns() as $ns) {
                $this->assertIsString($ns);
                $this->assertMatchesRegularExpression('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $ns);
            }

            // Test created and modified date format
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/', $zone->created());
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/', $zone->modified());
        }
    }

    /**
     * Test handling DNS zone API exception
     */
    public function test_can_handle_dns_zone_api_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('DNS zone not found', new Request('GET', '/zones/nonexistent.com')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('DNS zone not found');

        $client->dnsZones()->retrieve('nonexistent.com');
    }

    /**
     * Test handling DNS zone list exception
     */
    public function test_can_handle_dns_zone_list_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Rate limit exceeded', new Request('GET', '/zones')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->dnsZones()->list();
    }

    /**
     * Test handling mixed DNS zone response types
     */
    public function test_can_handle_mixed_dns_zone_response_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'zones' => [
                    [
                        'id' => 'example.com',
                        'name' => 'example.com',
                        'ttl' => 3600,
                        'created' => '2023-01-01T00:00:00+00:00',
                        'modified' => '2023-01-01T00:00:00+00:00',
                        'is_secondary_dns' => false,
                        'legacy_dns_host' => 'legacy-dns.example.com',
                        'legacy_ns' => ['ns1.example.com', 'ns2.example.com'],
                        'ns' => ['ns1.example.com', 'ns2.example.com'],
                        'owner' => 'admin@example.com',
                        'paused' => false,
                        'permission' => 'full_access',
                        'project' => 'my-project',
                        'registrar' => 'example-registrar',
                        'status' => 'verified',
                        'verified' => '2023-01-01T00:00:00+00:00',
                        'records_count' => 5,
                        'is_primary_dns' => true,
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 1,
                    ],
                ],
            ]) ?: ''),
            new RequestException('DNS zone not found', new Request('GET', '/zones/nonexistent.com')),
        ];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $listResponse = $client->dnsZones()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->zones());

        // Second call should throw exception
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('DNS zone not found');
        $client->dnsZones()->retrieve('nonexistent.com');
    }

    /**
     * Test using individual DNS zone fake
     */
    public function test_using_individual_dns_zone_fake(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $dnsZonesFake = $client->dnsZones();

        $response = $dnsZonesFake->list(['page' => 1, 'per_page' => 5]);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->zones());

        $dnsZonesFake->assertSent(function (array $request) {
            return $request['resource'] === 'dns_zones' &&
                   $request['method'] === 'list' &&
                   isset($request['parameters']['page']) &&
                   $request['parameters']['page'] === 1 &&
                   isset($request['parameters']['per_page']) &&
                   $request['parameters']['per_page'] === 5;
        });
    }

    /**
     * Test DNS zone fake assert not sent
     */
    public function test_dns_zone_fake_assert_not_sent(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $dnsZonesFake = $client->dnsZones();

        // No requests made yet
        $dnsZonesFake->assertNotSent();

        // Make a request
        $dnsZonesFake->list();

        // Now it should fail
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Requests were sent to dns_zones.');
        $dnsZonesFake->assertNotSent();
    }

    /**
     * Test DNS zone workflow simulation
     */
    public function test_dns_zone_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List DNS zones response
            new Response(200, [], json_encode([
                'zones' => [
                    [
                        'id' => 'example.com',
                        'name' => 'example.com',
                        'ttl' => 3600,
                        'created' => '2023-01-01T00:00:00+00:00',
                        'modified' => '2023-01-01T00:00:00+00:00',
                        'is_secondary_dns' => false,
                        'legacy_dns_host' => 'legacy-dns.example.com',
                        'legacy_ns' => ['ns1.example.com', 'ns2.example.com'],
                        'ns' => ['ns1.example.com', 'ns2.example.com'],
                        'owner' => 'admin@example.com',
                        'paused' => false,
                        'permission' => 'full_access',
                        'project' => 'my-project',
                        'registrar' => 'example-registrar',
                        'status' => 'verified',
                        'verified' => '2023-01-01T00:00:00+00:00',
                        'records_count' => 5,
                        'is_primary_dns' => true,
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 1,
                    ],
                ],
            ]) ?: ''),
            // Get specific DNS zone response
            new Response(200, [], json_encode([
                'zone' => [
                    'id' => 'example.com',
                    'name' => 'example.com',
                    'ttl' => 3600,
                    'created' => '2023-01-01T00:00:00+00:00',
                    'modified' => '2023-01-01T00:00:00+00:00',
                    'is_secondary_dns' => false,
                    'legacy_dns_host' => 'legacy-dns.example.com',
                    'legacy_ns' => ['ns1.example.com', 'ns2.example.com'],
                    'ns' => ['ns1.example.com', 'ns2.example.com'],
                    'owner' => 'admin@example.com',
                    'paused' => false,
                    'permission' => 'full_access',
                    'project' => 'my-project',
                    'registrar' => 'example-registrar',
                    'status' => 'verified',
                    'verified' => '2023-01-01T00:00:00+00:00',
                    'records_count' => 5,
                    'is_primary_dns' => true,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list DNS zones, then get details of the first one
        $listResponse = $client->dnsZones()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->zones());

        $firstZone = $listResponse->zones()[0];
        $zoneIdOrName = $firstZone->id();

        $getResponse = $client->dnsZones()->retrieve($zoneIdOrName);
        $this->assertInstanceOf(RetrieveResponse::class, $getResponse);
        $this->assertEquals($firstZone->id(), $getResponse->zone()->id());
        $this->assertEquals($firstZone->name(), $getResponse->zone()->name());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'dns_zones', 'list');
        $this->assertRequestWasMade($requests, 'dns_zones', 'get');
    }

    /**
     * Test DNS zone error response handling
     */
    public function test_dns_zone_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('DNS zone not found', new Request('GET', '/zones/nonexistent.com')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('DNS zone not found');
        $client->dnsZones()->retrieve('nonexistent.com');
    }

    /**
     * Test DNS zone empty list response
     */
    public function test_dns_zone_empty_list_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'zones' => [],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 0,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->dnsZones()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(0, $response->zones());
        $this->assertEmpty($response->zones());
    }

    /**
     * Test DNS zone pagination
     */
    public function test_dns_zone_pagination(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->dnsZones()->list();
        $pagination = $response->pagination();

        // Test pagination structure
        $this->assertIsArray($pagination);
        $this->assertArrayHasKey('current_page', $pagination);
        $this->assertArrayHasKey('per_page', $pagination);
        $this->assertArrayHasKey('total', $pagination);
        $this->assertArrayHasKey('last_page', $pagination);
        $this->assertArrayHasKey('from', $pagination);
        $this->assertArrayHasKey('to', $pagination);
        $this->assertArrayHasKey('has_more_pages', $pagination);
        $this->assertArrayHasKey('links', $pagination);

        // Test pagination values
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(25, $pagination['per_page']);
        $this->assertEquals(1, $pagination['total']);
        $this->assertEquals(1, $pagination['last_page']);
        $this->assertFalse($pagination['has_more_pages']);

        // Test links structure
        $this->assertIsArray($pagination['links']);
        $this->assertArrayHasKey('first', $pagination['links']);
        $this->assertArrayHasKey('last', $pagination['links']);
        $this->assertArrayHasKey('prev', $pagination['links']);
        $this->assertArrayHasKey('next', $pagination['links']);
    }

    /**
     * Test DNS zone to array conversion
     */
    public function test_dns_zone_to_array_conversion(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->dnsZones()->list();
        $zones = $response->zones();

        foreach ($zones as $zone) {
            $array = $zone->toArray();

            $this->assertIsArray($array);
            $this->assertArrayHasKey('id', $array);
            $this->assertArrayHasKey('name', $array);
            $this->assertArrayHasKey('ttl', $array);
            $this->assertArrayHasKey('created', $array);
            $this->assertArrayHasKey('modified', $array);
            $this->assertArrayHasKey('is_secondary_dns', $array);
            $this->assertArrayHasKey('paused', $array);
            $this->assertArrayHasKey('permission', $array);
            $this->assertArrayHasKey('project', $array);
            $this->assertArrayHasKey('status', $array);
            $this->assertArrayHasKey('records_count', $array);
            $this->assertArrayHasKey('is_primary_dns', $array);
            $this->assertArrayHasKey('ns', $array);
            $this->assertArrayHasKey('legacy_ns', $array);

            $this->assertEquals($zone->id(), $array['id']);
            $this->assertEquals($zone->name(), $array['name']);
            $this->assertEquals($zone->ttl(), $array['ttl']);
            $this->assertEquals($zone->created(), $array['created']);
            $this->assertEquals($zone->modified(), $array['modified']);
        }
    }

    /**
     * Test DNS zone actions - list actions for a zone
     */
    public function test_can_list_dns_zone_actions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $response = $client->dnsZones()->actions()->list($zoneIdOrName);

        $this->assertInstanceOf(ListActionsResponse::class, $response);
        $this->assertIsArray($response->actions());
        $this->assertCount(2, $response->actions());

        $actions = $response->actions();
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\DnsZoneActions\Action::class, $actions[0]);
        $this->assertEquals(1, $actions[0]->id());
        $this->assertEquals('change_nameservers', $actions[0]->command());
        $this->assertEquals('success', $actions[0]->status());
        $this->assertEquals(100, $actions[0]->progress());

        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\DnsZoneActions\Action::class, $actions[1]);
        $this->assertEquals(2, $actions[1]->id());
        $this->assertEquals('change_protection', $actions[1]->command());
        $this->assertEquals('running', $actions[1]->status());
        $this->assertEquals(50, $actions[1]->progress());

        $this->assertRequestWasMade($requests, 'dns_zone_actions', 'list');
    }

    /**
     * Test DNS zone actions - get action for a zone
     */
    public function test_can_get_dns_zone_action(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $actionId = '123';
        $response = $client->dnsZones()->actions()->retrieve($zoneIdOrName, $actionId);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertInstanceOf(\Boci\HetznerLaravel\Responses\DnsZoneActions\Action::class, $response->action());

        $action = $response->action();
        $this->assertEquals(123, $action->id());
        $this->assertEquals('change_nameservers', $action->command());
        $this->assertEquals('running', $action->status());
        $this->assertEquals(0, $action->progress());

        $this->assertRequestWasMade($requests, 'dns_zone_actions', 'get');
    }

    /**
     * Test DNS zone actions - change nameservers
     */
    public function test_can_change_dns_zone_nameservers(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $parameters = [
            'nameservers' => ['ns1.new.com', 'ns2.new.com'],
        ];

        $response = $client->dnsZones()->actions()->changeNameservers($zoneIdOrName, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('change_nameservers', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'dns_zone_actions', 'change_nameservers');
    }

    /**
     * Test DNS zone actions - change protection
     */
    public function test_can_change_dns_zone_protection(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $parameters = [
            'delete' => true,
        ];

        $response = $client->dnsZones()->actions()->changeProtection($zoneIdOrName, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('change_protection', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'dns_zone_actions', 'change_protection');
    }

    /**
     * Test DNS zone actions - change default TTL
     */
    public function test_can_change_dns_zone_default_ttl(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $parameters = [
            'ttl' => 7200,
        ];

        $response = $client->dnsZones()->actions()->changeDefaultTtl($zoneIdOrName, $parameters);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('change_default_ttl', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'dns_zone_actions', 'change_default_ttl');
    }

    /**
     * Test DNS zone actions workflow simulation
     */
    public function test_dns_zone_actions_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // Change nameservers response
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 1,
                    'command' => 'change_nameservers',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2023-01-01T00:00:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 'example.com',
                            'type' => 'zone',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
            // Change protection response
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 2,
                    'command' => 'change_protection',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2023-01-01T00:01:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 'example.com',
                            'type' => 'zone',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';

        // Simulate a workflow: change nameservers, then change protection
        $changeNameserversResponse = $client->dnsZones()->actions()->changeNameservers($zoneIdOrName, ['nameservers' => ['ns1.new.com', 'ns2.new.com']]);
        $this->assertInstanceOf(ActionResponse::class, $changeNameserversResponse);
        $this->assertEquals('change_nameservers', $changeNameserversResponse->action()->command());

        $changeProtectionResponse = $client->dnsZones()->actions()->changeProtection($zoneIdOrName, ['delete' => true]);
        $this->assertInstanceOf(ActionResponse::class, $changeProtectionResponse);
        $this->assertEquals('change_protection', $changeProtectionResponse->action()->command());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'dns_zone_actions', 'change_nameservers');
        $this->assertRequestWasMade($requests, 'dns_zone_actions', 'change_protection');
    }

    /**
     * Test DNS RRSets - list RRSets for a zone
     */
    public function test_can_list_dns_rrsets(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $response = $client->dnsZones()->rrsets()->list($zoneIdOrName);

        $this->assertInstanceOf(RRSetListResponse::class, $response);
        $this->assertIsArray($response->rrsets());
        $this->assertCount(3, $response->rrsets());

        $rrsets = $response->rrsets();
        $this->assertInstanceOf(RRSet::class, $rrsets[0]);
        $this->assertEquals('@', $rrsets[0]->name());
        $this->assertEquals('A', $rrsets[0]->type());
        $this->assertEquals(3600, $rrsets[0]->ttl());
        $this->assertIsArray($rrsets[0]->records());

        $this->assertInstanceOf(RRSet::class, $rrsets[1]);
        $this->assertEquals('www', $rrsets[1]->name());
        $this->assertEquals('A', $rrsets[1]->type());
        $this->assertEquals(3600, $rrsets[1]->ttl());

        $this->assertInstanceOf(RRSet::class, $rrsets[2]);
        $this->assertEquals('@', $rrsets[2]->name());
        $this->assertEquals('MX', $rrsets[2]->type());
        $this->assertEquals(3600, $rrsets[2]->ttl());

        $this->assertRequestWasMade($requests, 'dns_rrsets', 'list');
    }

    /**
     * Test DNS RRSets - create RRSet
     */
    public function test_can_create_dns_rrset(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $parameters = [
            'name' => 'api',
            'type' => 'A',
            'ttl' => 3600,
            'records' => [
                [
                    'value' => '192.168.1.100',
                    'comment' => 'API server',
                ],
            ],
        ];

        $response = $client->dnsZones()->rrsets()->create($zoneIdOrName, $parameters);

        $this->assertInstanceOf(RRSetCreateResponse::class, $response);
        $this->assertInstanceOf(RRSet::class, $response->rrset());

        $rrset = $response->rrset();
        $this->assertEquals('api', $rrset->name());
        $this->assertEquals('A', $rrset->type());
        $this->assertEquals(3600, $rrset->ttl());
        $this->assertIsArray($rrset->records());

        $this->assertRequestWasMade($requests, 'dns_rrsets', 'create');
    }

    /**
     * Test DNS RRSets - get RRSet
     */
    public function test_can_get_dns_rrset(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $rrName = '@';
        $rrType = 'A';
        $response = $client->dnsZones()->rrsets()->retrieve($zoneIdOrName, $rrName, $rrType);

        $this->assertInstanceOf(RRSetGetResponse::class, $response);
        $this->assertInstanceOf(RRSet::class, $response->rrset());

        $rrset = $response->rrset();
        $this->assertEquals('@', $rrset->name());
        $this->assertEquals('A', $rrset->type());
        $this->assertEquals(3600, $rrset->ttl());
        $this->assertIsArray($rrset->records());

        $this->assertRequestWasMade($requests, 'dns_rrsets', 'get');
    }

    /**
     * Test DNS RRSets - update RRSet
     */
    public function test_can_update_dns_rrset(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $rrName = '@';
        $rrType = 'A';
        $parameters = [
            'ttl' => 7200,
            'records' => [
                [
                    'value' => '192.168.1.2',
                    'comment' => 'Updated server',
                ],
            ],
        ];

        $response = $client->dnsZones()->rrsets()->update($zoneIdOrName, $rrName, $rrType, $parameters);

        $this->assertInstanceOf(RRSetUpdateResponse::class, $response);
        $this->assertInstanceOf(RRSet::class, $response->rrset());

        $rrset = $response->rrset();
        $this->assertEquals('@', $rrset->name());
        $this->assertEquals('A', $rrset->type());
        $this->assertEquals(7200, $rrset->ttl());

        $this->assertRequestWasMade($requests, 'dns_rrsets', 'update');
    }

    /**
     * Test DNS RRSets - delete RRSet
     */
    public function test_can_delete_dns_rrset(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $rrName = '@';
        $rrType = 'A';
        $response = $client->dnsZones()->rrsets()->delete($zoneIdOrName, $rrName, $rrType);

        $this->assertInstanceOf(RRSetDeleteResponse::class, $response);

        $this->assertRequestWasMade($requests, 'dns_rrsets', 'delete');
    }

    /**
     * Test DNS RRSets - change protection
     */
    public function test_can_change_dns_rrset_protection(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $rrName = '@';
        $rrType = 'A';
        $parameters = [
            'delete' => true,
        ];

        $response = $client->dnsZones()->rrsets()->changeProtection($zoneIdOrName, $rrName, $rrType, $parameters);

        $this->assertInstanceOf(RRSetActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('change_protection', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'dns_rrsets', 'change_protection');
    }

    /**
     * Test DNS RRSets - change TTL
     */
    public function test_can_change_dns_rrset_ttl(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $rrName = '@';
        $rrType = 'A';
        $parameters = [
            'ttl' => 7200,
        ];

        $response = $client->dnsZones()->rrsets()->changeTtl($zoneIdOrName, $rrName, $rrType, $parameters);

        $this->assertInstanceOf(RRSetActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('change_ttl', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'dns_rrsets', 'change_ttl');
    }

    /**
     * Test DNS RRSets - set records
     */
    public function test_can_set_dns_rrset_records(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $rrName = '@';
        $rrType = 'A';
        $parameters = [
            'records' => [
                [
                    'value' => '192.168.1.10',
                    'comment' => 'New server',
                ],
            ],
        ];

        $response = $client->dnsZones()->rrsets()->setRecords($zoneIdOrName, $rrName, $rrType, $parameters);

        $this->assertInstanceOf(RRSetActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('set_rrset_records', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'dns_rrsets', 'set_records');
    }

    /**
     * Test DNS RRSets - add records
     */
    public function test_can_add_dns_rrset_records(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $rrName = '@';
        $rrType = 'A';
        $parameters = [
            'records' => [
                [
                    'value' => '192.168.1.20',
                    'comment' => 'Additional server',
                ],
            ],
        ];

        $response = $client->dnsZones()->rrsets()->addRecords($zoneIdOrName, $rrName, $rrType, $parameters);

        $this->assertInstanceOf(RRSetActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('add_rrset_records', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'dns_rrsets', 'add_records');
    }

    /**
     * Test updating the comments of DNS RRSet records
     */
    public function test_can_update_dns_rrset_records(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'records' => [
                [
                    'value' => '192.168.1.20',
                    'comment' => 'Primary web server',
                ],
            ],
        ];

        $response = $client->dnsZones()->rrsets()->updateRecords('example.com', '@', 'A', $parameters);

        $this->assertInstanceOf(RRSetActionResponse::class, $response);
        $this->assertEquals('update_rrset_records', $response->action()->command());

        $this->assertRequestWasMade($requests, 'dns_rrsets', 'update_records', function ($request) {
            return $request['parameters']['records'][0]['comment'] === 'Primary web server';
        });

        $request = new \Boci\HetznerLaravel\Requests\DnsRrsets\UpdateRecordsRequest('example.com', '@', 'A', $parameters);
        $this->assertSame('POST', $request->method());
        $this->assertSame('/v1/zones/example.com/rrsets/@/A/actions/update_records', $request->uri());
    }

    /**
     * Test DNS RRSets - remove records
     */
    public function test_can_remove_dns_rrset_records(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';
        $rrName = '@';
        $rrType = 'A';
        $parameters = [
            'records' => [
                [
                    'value' => '192.168.1.1',
                    'comment' => 'Old server',
                ],
            ],
        ];

        $response = $client->dnsZones()->rrsets()->removeRecords($zoneIdOrName, $rrName, $rrType, $parameters);

        $this->assertInstanceOf(RRSetActionResponse::class, $response);
        $action = $response->action();

        $this->assertEquals(1, $action->id());
        $this->assertEquals('remove_rrset_records', $action->command());
        $this->assertEquals('running', $action->status());

        $this->assertRequestWasMade($requests, 'dns_rrsets', 'remove_records');
    }

    /**
     * Test DNS RRSets workflow simulation
     */
    public function test_dns_rrsets_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // Create RRSet response
            new Response(200, [], json_encode([
                'rrset' => [
                    'name' => 'api',
                    'type' => 'A',
                    'ttl' => 3600,
                    'records' => [
                        [
                            'value' => '192.168.1.100',
                            'comment' => 'API server',
                        ],
                    ],
                ],
            ]) ?: ''),
            // Set records response
            new Response(200, [], json_encode([
                'action' => [
                    'id' => 1,
                    'command' => 'set_rrset_records',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2023-01-01T00:00:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 'example.com',
                            'type' => 'zone',
                        ],
                    ],
                    'error' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $zoneIdOrName = 'example.com';

        // Simulate a workflow: create RRSet, then set records
        $createResponse = $client->dnsZones()->rrsets()->create($zoneIdOrName, [
            'name' => 'api',
            'type' => 'A',
            'ttl' => 3600,
            'records' => [
                [
                    'value' => '192.168.1.100',
                    'comment' => 'API server',
                ],
            ],
        ]);
        $this->assertInstanceOf(RRSetCreateResponse::class, $createResponse);
        $this->assertEquals('api', $createResponse->rrset()->name());

        $setRecordsResponse = $client->dnsZones()->rrsets()->setRecords($zoneIdOrName, 'api', 'A', [
            'records' => [
                [
                    'value' => '192.168.1.200',
                    'comment' => 'Updated API server',
                ],
            ],
        ]);
        $this->assertInstanceOf(RRSetActionResponse::class, $setRecordsResponse);
        $this->assertEquals('set_rrset_records', $setRecordsResponse->action()->command());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'dns_rrsets', 'create');
        $this->assertRequestWasMade($requests, 'dns_rrsets', 'set_records');
    }

    /**
     * Test DNS RRSet response structure
     */
    public function test_dns_rrset_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->dnsZones()->rrsets()->list('example.com');
        $rrsets = $response->rrsets();

        foreach ($rrsets as $rrset) {
            // Test all RRSet properties
            $this->assertIsString($rrset->name());
            $this->assertIsString($rrset->type());
            $this->assertIsInt($rrset->ttl());
            $this->assertIsArray($rrset->records());

            // Test RRSet type values
            $this->assertContains($rrset->type(), ['A', 'AAAA', 'CAA', 'CNAME', 'DS', 'HINFO', 'HTTPS', 'MX', 'NS', 'PTR', 'RP', 'SOA', 'SRV', 'SVCB', 'TLSA', 'TXT']);

            // Test records structure
            foreach ($rrset->records() as $record) {
                $this->assertIsArray($record);
                $this->assertArrayHasKey('value', $record);
                $this->assertArrayHasKey('comment', $record);
                $this->assertIsString($record['value']);
                $this->assertIsString($record['comment']);
            }

            // Test TTL range
            $this->assertGreaterThanOrEqual(60, $rrset->ttl());
            $this->assertLessThanOrEqual(2147483647, $rrset->ttl());
        }
    }

    /**
     * Test DNS RRSet to array conversion
     */
    public function test_dns_rrset_to_array_conversion(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->dnsZones()->rrsets()->list('example.com');
        $rrsets = $response->rrsets();

        foreach ($rrsets as $rrset) {
            $array = $rrset->toArray();

            $this->assertIsArray($array);
            $this->assertArrayHasKey('name', $array);
            $this->assertArrayHasKey('type', $array);
            $this->assertArrayHasKey('ttl', $array);
            $this->assertArrayHasKey('records', $array);

            $this->assertEquals($rrset->name(), $array['name']);
            $this->assertEquals($rrset->type(), $array['type']);
            $this->assertEquals($rrset->ttl(), $array['ttl']);
            $this->assertEquals($rrset->records(), $array['records']);
        }
    }

    /**
     * Test DNS zone nameserver validation
     */
    public function test_dns_zone_nameserver_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->dnsZones()->list();
        $zones = $response->zones();

        foreach ($zones as $zone) {
            $ns = $zone->ns();
            $legacyNs = $zone->legacyNs();

            // Test nameservers structure
            $this->assertIsArray($ns);
            $this->assertIsArray($legacyNs);

            // Test nameserver format
            foreach ($ns as $nameserver) {
                $this->assertIsString($nameserver);
                $this->assertMatchesRegularExpression('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $nameserver);
            }

            foreach ($legacyNs as $nameserver) {
                $this->assertIsString($nameserver);
                $this->assertMatchesRegularExpression('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $nameserver);
            }
        }
    }

    /**
     * Test DNS zone file format validation
     */
    public function test_dns_zone_file_format_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->dnsZones()->export('example.com');
        $zoneFile = $response->zoneFile();

        // Test zone file structure
        $this->assertIsString($zoneFile);
        $this->assertStringContainsString('$ORIGIN', $zoneFile);
        $this->assertStringContainsString('$TTL', $zoneFile);
        $this->assertStringContainsString('SOA', $zoneFile);
        $this->assertStringContainsString('NS', $zoneFile);

        // Test zone file format
        $lines = explode("\n", $zoneFile);
        $hasOrigin = false;
        $hasTtl = false;
        $hasSoa = false;
        $hasNs = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '$ORIGIN')) {
                $hasOrigin = true;
            }
            if (str_starts_with($line, '$TTL')) {
                $hasTtl = true;
            }
            if (str_contains($line, 'SOA')) {
                $hasSoa = true;
            }
            if (str_contains($line, 'NS')) {
                $hasNs = true;
            }
        }

        $this->assertTrue($hasOrigin, 'Zone file should contain $ORIGIN directive');
        $this->assertTrue($hasTtl, 'Zone file should contain $TTL directive');
        $this->assertTrue($hasSoa, 'Zone file should contain SOA record');
        $this->assertTrue($hasNs, 'Zone file should contain NS record');
    }
}
