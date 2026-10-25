<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\DnsZones;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Export DNS Zone Response
 *
 * This response class represents the response from exporting
 * a DNS zone in the Hetzner Cloud API.
 */
final class ExportResponse extends Response
{
    /**
     * Get the zone file content from the response.
     */
    public function zoneFile(): string
    {
        return $this->data['zone_file'] ?? '';
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'zone_file' => $parameters['zoneFile'] ?? '; Zone file for example.com
$ORIGIN example.com.
$TTL 3600
@   IN  SOA ns1.example.com. admin.example.com. (
    2023010100  ; serial
    3600        ; refresh
    1800        ; retry
    604800      ; expire
    86400       ; minimum
)
@   IN  NS  ns1.example.com.
@   IN  NS  ns2.example.com.
@   IN  A   192.168.1.1
www IN  A   192.168.1.1
',
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
