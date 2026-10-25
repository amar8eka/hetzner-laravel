<?php

namespace Boci\HetznerLaravel\Responses\Certificates;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

final class ListResponse extends Response
{
    /**
     * @return Certificate[]
     */
    public function certificates(): array
    {
        return array_map(
            fn (array $certificate) => new Certificate($certificate),
            $this->data['certificates']
        );
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'certificates' => [
                [
                    'id' => 1,
                    'name' => 'test-certificate',
                    'type' => 'uploaded',
                    'certificate' => '-----BEGIN CERTIFICATE-----\n...',
                    'private_key' => '-----BEGIN PRIVATE KEY-----\n...',
                    'fingerprint' => 'SHA256:abcdef1234567890',
                    'not_valid_before' => '2023-01-01T00:00:00+00:00',
                    'not_valid_after' => '2024-01-01T00:00:00+00:00',
                    'domain_names' => ['example.com', 'www.example.com'],
                    'labels' => [],
                    'created' => '2023-01-01T00:00:00+00:00',
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
