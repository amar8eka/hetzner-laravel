<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\DnsRrsets;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List DNS RRSets Response
 *
 * This response class represents the response from listing
 * DNS RRSets in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the RRSets from the response.
     *
     * @return RRSet[]
     */
    public function rrsets(): array
    {
        return array_map(
            fn (array $rrset) => new RRSet($rrset),
            $this->data['rrsets'] ?? []
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
            'rrsets' => [
                [
                    'name' => '@',
                    'type' => 'A',
                    'ttl' => 3600,
                    'records' => [
                        [
                            'value' => '192.168.1.1',
                            'comment' => 'Main server',
                        ],
                    ],
                ],
                [
                    'name' => 'www',
                    'type' => 'A',
                    'ttl' => 3600,
                    'records' => [
                        [
                            'value' => '192.168.1.1',
                            'comment' => 'Web server',
                        ],
                    ],
                ],
                [
                    'name' => '@',
                    'type' => 'MX',
                    'ttl' => 3600,
                    'records' => [
                        [
                            'value' => '10 mail.example.com',
                            'comment' => 'Mail server',
                        ],
                    ],
                ],
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'per_page' => 25,
                    'previous_page' => null,
                    'next_page' => null,
                    'last_page' => 1,
                    'total_entries' => 3,
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
