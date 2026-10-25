<?php

namespace Boci\HetznerLaravel\Responses\FloatingIPs;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

final class DeleteResponse extends Response
{
    // Empty response for delete operations

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
