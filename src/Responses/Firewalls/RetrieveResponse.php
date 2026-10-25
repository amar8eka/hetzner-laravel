<?php

namespace Boci\HetznerLaravel\Responses\Firewalls;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Retrieve Firewall Response
 *
 * This response class represents the response from retrieving
 * a specific firewall from the Hetzner Cloud API.
 */
final class RetrieveResponse extends Response
{
    /**
     * Get the firewall from the response.
     */
    public function firewall(): Firewall
    {
        return new Firewall($this->data['firewall']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $firewallId = $parameters['firewallId'] ?? '1';

        $data = [
            'firewall' => [
                'id' => (int) $firewallId,
                'name' => 'test-firewall-'.$firewallId,
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
                'applied_to' => [],
                'labels' => [],
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
