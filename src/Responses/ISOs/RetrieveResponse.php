<?php

namespace Boci\HetznerLaravel\Responses\ISOs;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Retrieve ISO Response
 *
 * This response class represents the response from retrieving
 * an ISO in the Hetzner Cloud API.
 */
final class RetrieveResponse extends Response
{
    /**
     * Get the ISO from the response.
     */
    public function iso(): ISO
    {
        return new ISO($this->data['iso']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $isoId = $parameters['isoId'] ?? '1';

        $data = [
            'iso' => [
                'id' => (int) $isoId,
                'name' => 'ubuntu-20.04-server-amd64',
                'description' => 'Ubuntu 20.04 Server 64-bit',
                'type' => 'public',
                'architecture' => 'x86',
                'deprecated' => null,
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
