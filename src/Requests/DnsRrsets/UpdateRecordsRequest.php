<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Requests\DnsRrsets;

use Boci\HetznerLaravel\Requests\Request;

/**
 * Update DNS RRSet Records Request
 */
final class UpdateRecordsRequest extends Request
{
    /**
     * Create a new update DNS RRSet records request instance.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function __construct(string $zoneIdOrName, string $rrName, string $rrType, array $parameters)
    {
        parent::__construct($parameters);
        $this->zoneIdOrName = $zoneIdOrName;
        $this->rrName = $rrName;
        $this->rrType = $rrType;
    }

    /**
     * Get the HTTP method for this request.
     */
    public function method(): string
    {
        return 'POST';
    }

    /**
     * Get the URI for this request.
     */
    public function uri(): string
    {
        return "/v1/zones/{$this->zoneIdOrName}/rrsets/{$this->rrName}/{$this->rrType}/actions/update_records";
    }

    private string $zoneIdOrName;

    private string $rrName;

    private string $rrType;
}
