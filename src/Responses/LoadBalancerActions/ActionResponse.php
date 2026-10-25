<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\LoadBalancerActions;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Load Balancer Action Response
 *
 * This response class represents the response from a load balancer action
 * in the Hetzner Cloud API.
 */
final class ActionResponse extends Response
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
        $command = $parameters['command'] ?? 'test_action';
        $loadBalancerId = $parameters['loadBalancerId'] ?? '1';
        $actionId = $parameters['actionId'] ?? '1';

        $data = [
            'action' => [
                'id' => (int) $actionId,
                'command' => $command,
                'status' => 'running',
                'progress' => 0,
                'started' => '2023-01-01T00:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => (int) $loadBalancerId,
                        'type' => 'load_balancer',
                    ],
                ],
                'error' => null,
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
