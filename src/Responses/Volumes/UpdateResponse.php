<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\Volumes;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Update Volume Response
 *
 * This response class represents the response from updating
 * a volume in the Hetzner Cloud API.
 */
final class UpdateResponse extends Response
{
    /**
     * Get the volume from the response.
     */
    public function volume(): Volume
    {
        return new Volume($this->data['volume']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $volumeId = $parameters['volumeId'] ?? '1';

        $data = [
            'volume' => [
                'id' => (int) $volumeId,
                'name' => $parameters['name'] ?? 'updated-volume',
                'status' => 'available',
                'server' => null,
                'location' => [
                    'id' => 1,
                    'name' => 'fsn1',
                    'description' => 'Falkenstein DC Park 1',
                    'country' => 'DE',
                    'city' => 'Falkenstein',
                    'latitude' => 50.4762,
                    'longitude' => 12.3707,
                    'network_zone' => 'eu-central',
                ],
                'size' => 10,
                'linux_device' => '/dev/disk/by-id/scsi-0HC_Volume_1',
                'protection' => [
                    'delete' => false,
                ],
                'labels' => $parameters['labels'] ?? [],
                'created' => '2023-01-01T00:00:00+00:00',
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
