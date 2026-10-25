<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\DnsZones;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Delete DNS Zone Response
 *
 * This response class represents the response from deleting
 * a DNS zone in the Hetzner Cloud API.
 */
final class DeleteResponse extends Response
{
    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
