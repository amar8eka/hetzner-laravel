<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\DnsRrsets;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Create DNS RRSet Response
 *
 * This response class represents the response from creating
 * a DNS RRSet in the Hetzner Cloud API.
 */
final class CreateResponse extends Response
{
    /**
     * Get the RRSet from the response.
     */
    public function rrset(): RRSet
    {
        return new RRSet($this->data['rrset']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'rrset' => [
                'name' => $parameters['name'] ?? '@',
                'type' => $parameters['type'] ?? 'A',
                'ttl' => $parameters['ttl'] ?? 3600,
                'records' => $parameters['records'] ?? [
                    [
                        'value' => '192.168.1.1',
                        'comment' => 'New record',
                    ],
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
