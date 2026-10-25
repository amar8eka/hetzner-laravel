<?php

namespace Boci\HetznerLaravel\Responses\SshKeys;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Create SSH Key Response
 *
 * This response class represents the response from creating
 * an SSH key in the Hetzner Cloud API.
 */
final class CreateResponse extends Response
{
    /**
     * Get the SSH key from the response.
     */
    public function sshKey(): SshKey
    {
        return new SshKey($this->data['ssh_key']);
    }

    /**
     * Create a fake response for testing purposes.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for customization
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'ssh_key' => [
                'id' => 1,
                'name' => $parameters['name'] ?? 'test-ssh-key',
                'fingerprint' => 'SHA256:nThbg6kXUpJWGl7E1IGOCspRomTxdCARLviKw6E5SY8',
                'public_key' => $parameters['public_key'] ?? 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC...',
                'labels' => $parameters['labels'] ?? [],
                'created' => '2023-01-01T00:00:00+00:00',
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
