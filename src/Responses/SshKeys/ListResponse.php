<?php

namespace Boci\HetznerLaravel\Responses\SshKeys;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List SSH Keys Response
 *
 * This response class represents the response from listing
 * SSH keys in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the SSH keys from the response.
     *
     * @return SshKey[]
     */
    public function sshKeys(): array
    {
        return array_map(
            fn (array $sshKey): SshKey => new SshKey($sshKey),
            $this->data['ssh_keys'] ?? []
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
     * @param  array<string, mixed>  $parameters
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'ssh_keys' => [
                [
                    'id' => 1,
                    'name' => 'my-ssh-key',
                    'fingerprint' => 'SHA256:nThbg6kXUpJWGl7E1IGOCspRomTxdCARLviKw6E5SY8',
                    'public_key' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC...',
                    'labels' => [],
                    'created' => '2023-01-01T00:00:00+00:00',
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
