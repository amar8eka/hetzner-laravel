<?php

namespace Boci\HetznerLaravel\Responses\Firewalls;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Create Firewall Response
 *
 * This response class represents the response from creating
 * a firewall in the Hetzner Cloud API.
 */
final class CreateResponse extends Response
{
    /**
     * Get the firewall from the response.
     */
    public function firewall(): Firewall
    {
        return new Firewall($this->data['firewall']);
    }

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
        $data = [
            'firewall' => [
                'id' => 1,
                'name' => $parameters['name'] ?? 'test-firewall',
                'created' => '2023-01-01T00:00:00+00:00',
                'rules' => $parameters['rules'] ?? [
                    [
                        'direction' => 'in',
                        'source_ips' => ['0.0.0.0/0', '::/0'],
                        'destination_ips' => [],
                        'source_ports' => [],
                        'destination_ports' => ['80', '443'],
                        'protocol' => 'tcp',
                        'action' => 'accept',
                        'description' => 'Allow HTTP and HTTPS',
                    ],
                ],
                'applied_to' => [],
                'labels' => $parameters['labels'] ?? [],
            ],
            'action' => [
                'id' => 1,
                'command' => 'create_firewall',
                'status' => 'success',
                'progress' => 100,
                'started' => '2023-01-01T00:00:00+00:00',
                'finished' => '2023-01-01T00:00:01+00:00',
                'resources' => [
                    [
                        'id' => 1,
                        'type' => 'firewall',
                    ],
                ],
                'error' => null,
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
