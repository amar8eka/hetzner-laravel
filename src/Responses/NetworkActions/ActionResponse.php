<?php

namespace Boci\HetznerLaravel\Responses\NetworkActions;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Network Action Response
 *
 * This response class represents the response from a network action
 * in the Hetzner Cloud API.
 */
final class ActionResponse extends Response
{
    /**
     * Get the action from the response.
     *
     * @return array<string, mixed>
     */
    public function action(): array
    {
        return $this->data['action'] ?? [];
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $command = $parameters['command'] ?? 'add_route';
        $networkId = $parameters['networkId'] ?? '1';

        $data = [
            'action' => [
                'id' => 1,
                'command' => $command,
                'status' => 'running',
                'progress' => 0,
                'started' => '2016-01-30T23:50:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => (int) $networkId,
                        'type' => 'network',
                    ],
                ],
                'error' => null,
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
