<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\PrimaryIPs;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;
use Boci\HetznerLaravel\Responses\ServerActions\Action;

/**
 * Delete Primary IP Response
 *
 * This response class represents the response from deleting
 * a primary IP in the Hetzner Cloud API.
 */
final class DeleteResponse extends Response
{
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
            'action' => [
                'id' => 1,
                'command' => 'delete_primary_ip',
                'status' => 'running',
                'progress' => 0,
                'started' => '2023-01-01T00:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => (int) ($parameters['primaryIpId'] ?? '1'),
                        'type' => 'primary_ip',
                    ],
                ],
                'error' => null,
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
