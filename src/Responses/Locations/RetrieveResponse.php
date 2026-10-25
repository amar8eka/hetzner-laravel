<?php

namespace Boci\HetznerLaravel\Responses\Locations;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Retrieve Location Response
 *
 * This response class represents the response from retrieving
 * a location in the Hetzner Cloud API.
 */
final class RetrieveResponse extends Response
{
    /**
     * Get the location from the response.
     */
    public function location(): Location
    {
        return new Location($this->data['location']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $locationId = $parameters['locationId'] ?? 'nbg1';

        $locations = [
            'nbg1' => [
                'id' => 1,
                'name' => 'nbg1',
                'description' => 'Nuremberg DC 1',
                'country' => 'DE',
                'city' => 'Nuremberg',
                'latitude' => 49.4521,
                'longitude' => 11.0767,
                'network_zone' => 'eu-central',
            ],
            'fsn1' => [
                'id' => 2,
                'name' => 'fsn1',
                'description' => 'Falkenstein DC 1',
                'country' => 'DE',
                'city' => 'Falkenstein',
                'latitude' => 50.4761,
                'longitude' => 12.3703,
                'network_zone' => 'eu-central',
            ],
            'hel1' => [
                'id' => 3,
                'name' => 'hel1',
                'description' => 'Helsinki DC 1',
                'country' => 'FI',
                'city' => 'Helsinki',
                'latitude' => 60.1699,
                'longitude' => 24.9384,
                'network_zone' => 'eu-central',
            ],
            'ash' => [
                'id' => 4,
                'name' => 'ash',
                'description' => 'Ashburn',
                'country' => 'US',
                'city' => 'Ashburn',
                'latitude' => 39.0438,
                'longitude' => -77.4874,
                'network_zone' => 'us-east',
            ],
            'hil' => [
                'id' => 5,
                'name' => 'hil',
                'description' => 'Hillsboro',
                'country' => 'US',
                'city' => 'Hillsboro',
                'latitude' => 45.5192,
                'longitude' => -122.9892,
                'network_zone' => 'us-west',
            ],
        ];

        $locationData = $locations[$locationId] ?? $locations['nbg1'];

        $data = [
            'location' => $locationData,
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
