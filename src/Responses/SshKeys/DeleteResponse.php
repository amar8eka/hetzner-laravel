<?php

namespace Boci\HetznerLaravel\Responses\SshKeys;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Delete SSH Key Response
 *
 * This response class represents the response from deleting
 * an SSH key in the Hetzner Cloud API.
 */
final class DeleteResponse extends Response
{
    /**
     * Create a fake response for testing purposes.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for customization
     */
    public static function fake(array $parameters = []): self
    {
        $data = [];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
