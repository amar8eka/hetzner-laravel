<?php

namespace Boci\HetznerLaravel\Responses\ServerTypes;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Retrieve Server Type Response
 *
 * This response class represents the response from retrieving
 * a server type in the Hetzner Cloud API.
 */
final class RetrieveResponse extends Response
{
    /**
     * Get the server type from the response.
     */
    public function serverType(): ServerType
    {
        return new ServerType($this->data['server_type']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $serverTypeId = $parameters['serverTypeId'] ?? '1';

        $data = [
            'server_type' => [
                'id' => (int) $serverTypeId,
                'name' => 'cx11',
                'description' => 'CX11',
                'cores' => 1,
                'memory' => 4.0,
                'disk' => 20,
                'prices' => [
                    [
                        'location' => 'fsn1',
                        'price_hourly' => [
                            'net' => '1.0000000000',
                            'gross' => '1.1900000000000000',
                        ],
                        'price_monthly' => [
                            'net' => '1.0000000000',
                            'gross' => '1.1900000000000000',
                        ],
                    ],
                ],
                'storage_type' => 'local',
                'cpu_type' => 'shared',
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
