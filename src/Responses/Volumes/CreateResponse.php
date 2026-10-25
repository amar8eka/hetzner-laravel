<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\Volumes;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;
use Boci\HetznerLaravel\Responses\ServerActions\Action;

/**
 * Create Volume Response
 *
 * This response class represents the response from creating
 * a volume in the Hetzner Cloud API.
 */
final class CreateResponse extends Response
{
    /**
     * Get the volume from the response.
     */
    public function volume(): Volume
    {
        return new Volume($this->data['volume']);
    }

    /**
     * Get the action from the response.
     */
    public function action(): ?Action
    {
        if (! isset($this->data['action'])) {
            return null;
        }

        return new Action($this->data['action']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'volume' => [
                'id' => 1,
                'name' => $parameters['name'] ?? 'test-volume',
                'status' => 'creating',
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
                'size' => $parameters['size'] ?? 10,
                'linux_device' => '/dev/disk/by-id/scsi-0HC_Volume_1',
                'protection' => [
                    'delete' => false,
                ],
                'labels' => $parameters['labels'] ?? [],
                'created' => '2023-01-01T00:00:00+00:00',
            ],
            'action' => [
                'id' => 1,
                'command' => 'create_volume',
                'status' => 'running',
                'progress' => 0,
                'started' => '2023-01-01T00:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 1,
                        'type' => 'volume',
                    ],
                ],
                'error' => null,
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
