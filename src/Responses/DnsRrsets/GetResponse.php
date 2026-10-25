<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\DnsRrsets;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Get DNS RRSet Response
 *
 * This response class represents the response from getting
 * a DNS RRSet in the Hetzner Cloud API.
 */
final class GetResponse extends Response
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
                'ttl' => 3600,
                'records' => [
                    [
                        'value' => '192.168.1.1',
                        'comment' => 'Main server',
                    ],
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
