<?php

namespace Boci\HetznerLaravel\Responses\Firewalls;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Firewalls Response
 *
 * This response class represents the response from listing
 * firewalls from the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the firewalls from the response.
     *
     * @return Firewall[]
     */
    public function firewalls(): array
    {
        return array_map(
            fn (array $firewall): Firewall => new Firewall($firewall),
            $this->data['firewalls'] ?? []
        );
    }

    /**
     * Get the pagination information from the response.
     *
     * @return array<string, mixed>
     */
    public function pagination(): array
    {
        $meta = $this->data['meta'] ?? [];
        $pagination = $meta['pagination'] ?? [];

        return [
            'current_page' => $pagination['page'] ?? 1,
            'per_page' => $pagination['per_page'] ?? 25,
            'total' => $pagination['total_entries'] ?? 0,
            'last_page' => $pagination['last_page'] ?? 1,
            'from' => (($pagination['page'] ?? 1) - 1) * ($pagination['per_page'] ?? 25) + 1,
            'to' => min(($pagination['page'] ?? 1) * ($pagination['per_page'] ?? 25), $pagination['total_entries'] ?? 0),
            'has_more_pages' => ($pagination['next_page'] ?? null) !== null,
            'links' => [
                'first' => $pagination['page'] > 1 ? '?page=1' : null,
                'last' => $pagination['last_page'] > 1 ? '?page='.$pagination['last_page'] : null,
                'prev' => $pagination['previous_page'] ? '?page='.$pagination['previous_page'] : null,
                'next' => $pagination['next_page'] ? '?page='.$pagination['next_page'] : null,
            ],
        ];
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'firewalls' => [
                [
                    'id' => 1,
                    'name' => 'test-firewall-1',
                    'created' => '2023-01-01T00:00:00+00:00',
                    'rules' => [
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
                    'applied_to' => [
                        [
                            'type' => 'server',
                            'server' => [
                                'id' => 1,
                                'name' => 'test-server-1',
                            ],
                        ],
                    ],
                    'labels' => [],
                ],
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'per_page' => 25,
                    'previous_page' => null,
                    'next_page' => null,
                    'last_page' => 1,
                    'total_entries' => 1,
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
