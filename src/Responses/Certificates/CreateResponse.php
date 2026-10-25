<?php

namespace Boci\HetznerLaravel\Responses\Certificates;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Create Certificate Response
 *
 * This response class represents the response from creating
 * a certificate in the Hetzner Cloud API.
 */
final class CreateResponse extends Response
{
    /**
     * Get the certificate from the response.
     */
    public function certificate(): Certificate
    {
        return new Certificate($this->data['certificate']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'certificate' => [
                'id' => (int) ($parameters['certificateId'] ?? 1),
                'name' => $parameters['name'] ?? 'test-certificate',
                'type' => $parameters['type'] ?? 'uploaded',
                'certificate' => $parameters['certificate'] ?? '-----BEGIN CERTIFICATE-----\n...',
                'private_key' => $parameters['private_key'] ?? '-----BEGIN PRIVATE KEY-----\n...',
                'fingerprint' => 'SHA256:abcdef1234567890',
                'not_valid_before' => '2023-01-01T00:00:00+00:00',
                'not_valid_after' => '2024-01-01T00:00:00+00:00',
                'domain_names' => $parameters['domain_names'] ?? ['example.com'],
                'labels' => $parameters['labels'] ?? [],
                'created' => '2023-01-01T00:00:00+00:00',
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
