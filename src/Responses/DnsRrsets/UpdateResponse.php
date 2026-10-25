<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\DnsRrsets;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Update DNS RRSet Response
 *
 * This response class represents the response from updating
 * a DNS RRSet in the Hetzner Cloud API.
 */
final class UpdateResponse extends Response
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
        $rrName = $parameters['rrName'] ?? '@';
        $rrType = $parameters['rrType'] ?? 'A';

        $data = [
            'rrset' => [
                'name' => $rrName,
                'type' => $rrType,
                'ttl' => $parameters['ttl'] ?? 3600,
                'records' => $parameters['records'] ?? [
                    [
                        'value' => '192.168.1.2',
                        'comment' => 'Updated record',
                    ],
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
