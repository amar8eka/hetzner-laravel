<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\DnsZones;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List DNS Zones Response
 *
 * This response class represents the response from listing
 * DNS zones in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the DNS zones from the response.
     *
     * @return DnsZone[]
     */
    public function zones(): array
    {
        return array_map(
            fn (array $zone) => new DnsZone($zone),
            $this->data['zones'] ?? []
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
            'zones' => [
                [
                    'id' => 'example.com',
                    'name' => 'example.com',
                    'ttl' => 3600,
                    'created' => '2023-01-01T00:00:00+00:00',
                    'modified' => '2023-01-01T00:00:00+00:00',
                    'is_secondary_dns' => false,
                    'legacy_dns_host' => 'legacy-dns.example.com',
                    'legacy_ns' => ['ns1.example.com', 'ns2.example.com'],
                    'ns' => ['ns1.example.com', 'ns2.example.com'],
                    'owner' => 'admin@example.com',
                    'paused' => false,
                    'permission' => 'full_access',
                    'project' => 'my-project',
                    'registrar' => 'example-registrar',
                    'status' => 'verified',
                    'verified' => '2023-01-01T00:00:00+00:00',
                    'records_count' => 5,
                    'is_primary_dns' => true,
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
