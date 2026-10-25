<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\Actions;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Get Action Response
 *
 * This response class represents the response from getting
 * a specific action from the Hetzner Cloud API.
 */
final class GetActionResponse extends Response
{
    /**
     * Get the action from the response.
     */
    public function action(): Action
    {
        return new Action($this->data['action']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $actionId = $parameters['actionId'] ?? '123';

        $data = [
            'action' => [
                'id' => (int) $actionId,
                'command' => 'create_server',
                'status' => 'success',
                'progress' => 100,
                'started' => '2023-01-01T12:00:00+00:00',
                'finished' => '2023-01-01T12:01:00+00:00',
                'resources' => [
                    [
                        'id' => 1,
                        'type' => 'server',
                    ],
                ],
                'error' => null,
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
