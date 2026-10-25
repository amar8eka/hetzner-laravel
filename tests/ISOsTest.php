<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\ISOs\ISO;
use Boci\HetznerLaravel\Responses\ISOs\ListResponse;
use Boci\HetznerLaravel\Responses\ISOs\RetrieveResponse;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

/**
 * ISOs Test Suite
 *
 * This test suite covers all functionality related to the ISOs resource,
 * including listing and retrieving ISOs.
 */
final class ISOsTest extends TestCase
{
    /**
     * Test listing ISOs with fake data
     */
    public function test_can_list_isos_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->isos()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertIsArray($response->isos());
        $this->assertCount(1, $response->isos());

        $iso = $response->isos()[0];
        $this->assertInstanceOf(ISO::class, $iso);
        $this->assertEquals(1, $iso->id());
        $this->assertEquals('ubuntu-20.04-server-amd64', $iso->name());
        $this->assertEquals('Ubuntu 20.04 Server 64-bit', $iso->description());
        $this->assertEquals('public', $iso->type());
        $this->assertNull($iso->deprecated());

        $pagination = $response->pagination();
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(25, $pagination['per_page']);
        $this->assertEquals(1, $pagination['total']);
        $this->assertEquals(1, $pagination['last_page']);
        $this->assertFalse($pagination['has_more_pages']);

        $this->assertRequestWasMade($requests, 'isos', 'list');
    }

    /**
     * Test listing ISOs with custom response
     */
    public function test_can_list_isos_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'isos' => [
                    [
                        'id' => 42,
                        'name' => 'debian-11-amd64',
                        'description' => 'Debian 11 64-bit',
                        'type' => 'public',
                        'architecture' => 'x86',
                        'deprecated' => null,
                    ],
                    [
                        'id' => 43,
                        'name' => 'centos-8-amd64',
                        'description' => 'CentOS 8 64-bit',
                        'type' => 'public',
                        'architecture' => 'x86',
                        'deprecated' => '2021-12-31T00:00:00+00:00',
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 2,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->isos()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(2, $response->isos());

        $firstIso = $response->isos()[0];
        $this->assertEquals(42, $firstIso->id());
        $this->assertEquals('debian-11-amd64', $firstIso->name());
        $this->assertEquals('Debian 11 64-bit', $firstIso->description());
        $this->assertEquals('public', $firstIso->type());
        $this->assertNull($firstIso->deprecated());

        $secondIso = $response->isos()[1];
        $this->assertEquals(43, $secondIso->id());
        $this->assertEquals('centos-8-amd64', $secondIso->name());
        $this->assertEquals('CentOS 8 64-bit', $secondIso->description());
        $this->assertEquals('public', $secondIso->type());
        $this->assertEquals('2021-12-31T00:00:00+00:00', $secondIso->deprecated());

        $pagination = $response->pagination();
        $this->assertEquals(2, $pagination['total']);
        $this->assertEquals(2, $pagination['to']);

        $this->assertRequestWasMade($requests, 'isos', 'list');
    }

    /**
     * Test listing ISOs with query parameters
     */
    public function test_can_list_isos_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'ubuntu',
            'type' => 'public',
        ];

        $response = $client->isos()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);

        $this->assertRequestWasMade($requests, 'isos', 'list');
    }

    /**
     * Test retrieving a specific ISO
     */
    public function test_can_retrieve_specific_iso(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $isoId = '42';
        $response = $client->isos()->retrieve($isoId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $this->assertInstanceOf(ISO::class, $response->iso());

        $iso = $response->iso();
        $this->assertEquals(42, $iso->id());
        $this->assertEquals('ubuntu-20.04-server-amd64', $iso->name());
        $this->assertEquals('Ubuntu 20.04 Server 64-bit', $iso->description());
        $this->assertEquals('public', $iso->type());
        $this->assertNull($iso->deprecated());

        $this->assertRequestWasMade($requests, 'isos', 'retrieve');
    }

    /**
     * Test retrieving ISO with custom response
     */
    public function test_can_retrieve_iso_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'iso' => [
                    'id' => 99,
                    'name' => 'custom-iso',
                    'description' => 'Custom ISO for testing',
                    'type' => 'private',
                    'architecture' => 'arm',
                    'deprecated' => '2023-12-31T00:00:00+00:00',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $isoId = '99';
        $response = $client->isos()->retrieve($isoId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $iso = $response->iso();

        $this->assertEquals(99, $iso->id());
        $this->assertEquals('custom-iso', $iso->name());
        $this->assertEquals('Custom ISO for testing', $iso->description());
        $this->assertEquals('private', $iso->type());
        $this->assertEquals('2023-12-31T00:00:00+00:00', $iso->deprecated());

        $this->assertRequestWasMade($requests, 'isos', 'retrieve');
    }

    /**
     * Test ISO response structure
     */
    public function test_iso_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->isos()->list();
        $iso = $response->isos()[0];

        // Test all ISO properties
        $this->assertIsInt($iso->id());
        $this->assertIsString($iso->name());
        $this->assertIsString($iso->description());
        $this->assertIsString($iso->type());
        $this->assertIsString($iso->deprecated() ?? 'not-null');

        // Test ISO types
        $this->assertContains($iso->type(), ['public', 'private']);

        // Test toArray method
        $isoArray = $iso->toArray();
        $this->assertIsArray($isoArray);
        $this->assertArrayHasKey('id', $isoArray);
        $this->assertArrayHasKey('name', $isoArray);
        $this->assertArrayHasKey('description', $isoArray);
        $this->assertArrayHasKey('type', $isoArray);
    }

    /**
     * Test handling ISO API exception
     */
    public function test_can_handle_iso_api_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('ISO not found', new Request('GET', '/isos/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('ISO not found');

        $client->isos()->retrieve('999');
    }

    /**
     * Test handling ISO list exception
     */
    public function test_can_handle_iso_list_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Rate limit exceeded', new Request('GET', '/isos')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->isos()->list();
    }

    /**
     * Test handling mixed ISO response types
     */
    public function test_can_handle_mixed_iso_response_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'isos' => [
                    [
                        'id' => 1,
                        'name' => 'ubuntu-20.04',
                        'description' => 'Ubuntu 20.04',
                        'type' => 'public',
                        'architecture' => 'x86',
                        'deprecated' => null,
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
            ]) ?: ''),
            new RequestException('ISO not found', new Request('GET', '/isos/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $listResponse = $client->isos()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->isos());

        // Second call should throw exception
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('ISO not found');
        $client->isos()->retrieve('999');
    }

    /**
     * Test using individual ISO fake
     */
    public function test_using_individual_iso_fake(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $isosFake = $client->isos();

        $response = $isosFake->list(['page' => 1, 'per_page' => 5]);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->isos());

        $isosFake->assertSent(function (array $request) {
            return $request['resource'] === 'isos' &&
                   $request['method'] === 'list' &&
                   isset($request['parameters']['page']) &&
                   $request['parameters']['page'] === 1 &&
                   isset($request['parameters']['per_page']) &&
                   $request['parameters']['per_page'] === 5;
        });
    }

    /**
     * Test ISO fake assert not sent
     */
    public function test_iso_fake_assert_not_sent(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $isosFake = $client->isos();

        // No requests made yet
        $isosFake->assertNotSent();

        // Make a request
        $isosFake->list();

        // Now it should fail
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Requests were sent to isos.');
        $isosFake->assertNotSent();
    }

    /**
     * Test ISO pagination structure
     */
    public function test_iso_pagination_structure(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'isos' => [
                    [
                        'id' => 1,
                        'name' => 'ubuntu-20.04',
                        'description' => 'Ubuntu 20.04',
                        'type' => 'public',
                        'architecture' => 'x86',
                        'deprecated' => null,
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'page' => 2,
                        'per_page' => 10,
                        'previous_page' => 1,
                        'next_page' => 3,
                        'last_page' => 5,
                        'total_entries' => 50,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->isos()->list();
        $pagination = $response->pagination();

        $this->assertEquals(2, $pagination['current_page']);
        $this->assertEquals(10, $pagination['per_page']);
        $this->assertEquals(50, $pagination['total']);
        $this->assertEquals(5, $pagination['last_page']);
        $this->assertEquals(11, $pagination['from']); // (2-1) * 10 + 1
        $this->assertEquals(20, $pagination['to']); // min(2 * 10, 50)
        $this->assertTrue($pagination['has_more_pages']);

        $this->assertArrayHasKey('links', $pagination);
        $this->assertArrayHasKey('first', $pagination['links']);
        $this->assertArrayHasKey('last', $pagination['links']);
        $this->assertArrayHasKey('prev', $pagination['links']);
        $this->assertArrayHasKey('next', $pagination['links']);
    }

    /**
     * Test ISO deprecated handling
     */
    public function test_iso_deprecated_handling(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'isos' => [
                    [
                        'id' => 1,
                        'name' => 'old-ubuntu',
                        'description' => 'Old Ubuntu version',
                        'type' => 'public',
                        'architecture' => 'x86',
                        'deprecated' => '2021-12-31T00:00:00+00:00',
                    ],
                    [
                        'id' => 2,
                        'name' => 'current-ubuntu',
                        'description' => 'Current Ubuntu version',
                        'type' => 'public',
                        'architecture' => 'x86',
                        'deprecated' => null,
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 2,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->isos()->list();
        $isos = $response->isos();

        $this->assertCount(2, $isos);

        // First ISO is deprecated
        $this->assertNotNull($isos[0]->deprecated());
        $this->assertEquals('2021-12-31T00:00:00+00:00', $isos[0]->deprecated());

        // Second ISO is not deprecated
        $this->assertNull($isos[1]->deprecated());
    }

    /**
     * Test ISO architecture types
     */
    public function test_iso_architecture_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'isos' => [
                    [
                        'id' => 1,
                        'name' => 'ubuntu-x86',
                        'description' => 'Ubuntu x86',
                        'type' => 'public',
                        'architecture' => 'x86',
                        'deprecated' => null,
                    ],
                    [
                        'id' => 2,
                        'name' => 'ubuntu-arm',
                        'description' => 'Ubuntu ARM',
                        'type' => 'public',
                        'architecture' => 'arm',
                        'deprecated' => null,
                    ],
                ],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 2,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->isos()->list();
        $isos = $response->isos();

        $this->assertCount(2, $isos);

        // Test architecture types
        $this->assertEquals('x86', $isos[0]->toArray()['architecture']);
        $this->assertEquals('arm', $isos[1]->toArray()['architecture']);
    }

    /**
     * Test ISO workflow simulation
     */
    public function test_iso_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List ISOs response
            new Response(200, [], json_encode([
                'isos' => [
                    [
                        'id' => 1,
                        'name' => 'ubuntu-20.04-server-amd64',
                        'description' => 'Ubuntu 20.04 Server 64-bit',
                        'type' => 'public',
                        'architecture' => 'x86',
                        'deprecated' => null,
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
            ]) ?: ''),
            // Retrieve specific ISO response
            new Response(200, [], json_encode([
                'iso' => [
                    'id' => 1,
                    'name' => 'ubuntu-20.04-server-amd64',
                    'description' => 'Ubuntu 20.04 Server 64-bit',
                    'type' => 'public',
                    'architecture' => 'x86',
                    'deprecated' => null,
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list ISOs, then get details of the first one
        $listResponse = $client->isos()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->isos());

        $firstIso = $listResponse->isos()[0];
        $isoId = (string) $firstIso->id();

        $retrieveResponse = $client->isos()->retrieve($isoId);
        $this->assertInstanceOf(RetrieveResponse::class, $retrieveResponse);
        $this->assertEquals($firstIso->id(), $retrieveResponse->iso()->id());
        $this->assertEquals($firstIso->name(), $retrieveResponse->iso()->name());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'isos', 'list');
        $this->assertRequestWasMade($requests, 'isos', 'retrieve');
    }

    /**
     * Test ISO error response handling
     */
    public function test_iso_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('ISO not found', new Request('GET', '/isos/nonexistent')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('ISO not found');
        $client->isos()->retrieve('nonexistent');
    }

    /**
     * Test ISO empty list response
     */
    public function test_iso_empty_list_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'isos' => [],
                'meta' => [
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 25,
                        'previous_page' => null,
                        'next_page' => null,
                        'last_page' => 1,
                        'total_entries' => 0,
                    ],
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->isos()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(0, $response->isos());
        $this->assertEmpty($response->isos());

        $pagination = $response->pagination();
        $this->assertEquals(0, $pagination['total']);
        $this->assertEquals(0, $pagination['to']);
    }
}
