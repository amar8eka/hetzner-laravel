<?php

namespace Tests;

use Boci\HetznerLaravel\Responses\Servers\RetrieveResponse;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Psr7\Response;

/**
 * Response Test Suite
 *
 * Test suite for the shared behaviour of the base response class,
 * such as magic property access to the response data.
 */
class ResponseTest extends TestCase
{
    /**
     * Test that response data can be read as properties
     */
    public function test_can_access_response_data_as_properties(): void
    {
        $response = RetrieveResponse::from(
            new Response(200, [], json_encode(['server' => ['id' => 42, 'name' => 'web-1']])),
            new \Boci\HetznerLaravel\Requests\Servers\RetrieveRequest('42')
        );

        $this->assertTrue(isset($response->server));
        $this->assertSame(42, $response->server['id']);
        $this->assertSame('web-1', $response->server['name']);
    }

    /**
     * Test that missing response data returns null
     */
    public function test_missing_response_data_returns_null(): void
    {
        $response = RetrieveResponse::fake();

        $this->assertFalse(isset($response->missing));
        $this->assertNull($response->missing);
    }
}
