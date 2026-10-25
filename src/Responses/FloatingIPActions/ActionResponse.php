<?php

namespace Boci\HetznerLaravel\Responses\FloatingIPActions;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Floating IP Action Response
 *
 * This response class represents the response from floating IP actions
 * in the Hetzner Cloud API.
 */
final class ActionResponse extends Response
{
    /**
     * Get the action data from the response.
     *
     * @return array<string, mixed>
     */
    public function action(): array
    {
        return $this->data['action'];
    }

    /**
     * Get the action ID.
     */
    public function id(): int
    {
        return $this->data['action']['id'];
    }

    /**
     * Get the action command.
     */
    public function command(): string
    {
        return $this->data['action']['command'];
    }

    /**
     * Get the action status.
     */
    public function status(): string
    {
        return $this->data['action']['status'];
    }

    /**
     * Get the action progress percentage.
     */
    public function progress(): int
    {
        return $this->data['action']['progress'];
    }

    /**
     * Get when the action started.
     */
    public function started(): string
    {
        return $this->data['action']['started'];
    }

    /**
     * Get when the action finished.
     */
    public function finished(): ?string
    {
        return $this->data['action']['finished'] ?? null;
    }

    /**
     * Get the action resources.
     *
     * @return array<string, mixed>
     */
    public function resources(): array
    {
        return $this->data['action']['resources'] ?? [];
    }

    /**
     * Get the action error information.
     *
     * @return array<string, mixed>|null
     */
    public function error(): ?array
    {
        return $this->data['action']['error'] ?? null;
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $command = $parameters['command'] ?? 'assign_floating_ip';
        $floatingIpId = $parameters['floatingIpId'] ?? '1';
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
                        'id' => (int) $floatingIpId,
                        'type' => 'floating_ip',
                    ],
                ],
                'error' => null,
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
