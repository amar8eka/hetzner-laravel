<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\SshKeys\CreateResponse;
use Boci\HetznerLaravel\Responses\SshKeys\DeleteResponse;
use Boci\HetznerLaravel\Responses\SshKeys\ListResponse;
use Boci\HetznerLaravel\Responses\SshKeys\RetrieveResponse;
use Boci\HetznerLaravel\Responses\SshKeys\SshKey;
use Boci\HetznerLaravel\Responses\SshKeys\UpdateResponse;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

/**
 * SSH Keys Test Suite
 *
 * This test suite covers all functionality related to the SSH Keys resource,
 * including listing, creating, getting, updating, and deleting SSH keys.
 */
final class SshKeysTest extends TestCase
{
    /**
     * Test listing SSH keys with fake data
     */
    public function test_can_list_ssh_keys_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->sshKeys()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertIsArray($response->sshKeys());
        $this->assertCount(1, $response->sshKeys());

        $sshKeys = $response->sshKeys();
        $this->assertInstanceOf(SshKey::class, $sshKeys[0]);
        $this->assertEquals(1, $sshKeys[0]->id());
        $this->assertEquals('my-ssh-key', $sshKeys[0]->name());
        $this->assertEquals('SHA256:nThbg6kXUpJWGl7E1IGOCspRomTxdCARLviKw6E5SY8', $sshKeys[0]->fingerprint());
        $this->assertEquals('ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC...', $sshKeys[0]->publicKey());
        $this->assertIsArray($sshKeys[0]->labels());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $sshKeys[0]->created());

        $this->assertRequestWasMade($requests, 'ssh_keys', 'list');
    }

    /**
     * Test listing SSH keys with custom response
     */
    public function test_can_list_ssh_keys_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'ssh_keys' => [
                    [
                        'id' => 42,
                        'name' => 'custom-ssh-key',
                        'fingerprint' => 'SHA256:customFingerprint123456789',
                        'public_key' => 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIG...',
                        'labels' => [
                            'environment' => 'production',
                            'team' => 'devops',
                        ],
                        'created' => '2023-06-15T10:30:00+00:00',
                    ],
                    [
                        'id' => 43,
                        'name' => 'another-ssh-key',
                        'fingerprint' => 'SHA256:anotherFingerprint987654321',
                        'public_key' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQD...',
                        'labels' => [
                            'environment' => 'staging',
                            'team' => 'backend',
                        ],
                        'created' => '2023-06-16T14:45:00+00:00',
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

        $response = $client->sshKeys()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(2, $response->sshKeys());

        $sshKey1 = $response->sshKeys()[0];
        $this->assertEquals(42, $sshKey1->id());
        $this->assertEquals('custom-ssh-key', $sshKey1->name());
        $this->assertEquals('SHA256:customFingerprint123456789', $sshKey1->fingerprint());
        $this->assertEquals('ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIG...', $sshKey1->publicKey());
        $this->assertEquals('production', $sshKey1->labels()['environment']);
        $this->assertEquals('devops', $sshKey1->labels()['team']);

        $sshKey2 = $response->sshKeys()[1];
        $this->assertEquals(43, $sshKey2->id());
        $this->assertEquals('another-ssh-key', $sshKey2->name());
        $this->assertEquals('staging', $sshKey2->labels()['environment']);
        $this->assertEquals('backend', $sshKey2->labels()['team']);

        $this->assertRequestWasMade($requests, 'ssh_keys', 'list');
    }

    /**
     * Test listing SSH keys with query parameters
     */
    public function test_can_list_ssh_keys_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'my-ssh-key',
        ];

        $response = $client->sshKeys()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);

        $this->assertRequestWasMade($requests, 'ssh_keys', 'list');
    }

    /**
     * Test creating an SSH key
     */
    public function test_can_create_ssh_key(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'new-ssh-key',
            'public_key' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC...',
            'labels' => [
                'environment' => 'production',
                'team' => 'backend',
            ],
        ];

        $response = $client->sshKeys()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $this->assertInstanceOf(SshKey::class, $response->sshKey());

        $sshKey = $response->sshKey();
        $this->assertEquals(1, $sshKey->id());
        $this->assertEquals('new-ssh-key', $sshKey->name());
        $this->assertEquals('ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC...', $sshKey->publicKey());
        $this->assertEquals('SHA256:nThbg6kXUpJWGl7E1IGOCspRomTxdCARLviKw6E5SY8', $sshKey->fingerprint());

        $this->assertRequestWasMade($requests, 'ssh_keys', 'create');
    }

    /**
     * Test creating SSH key with custom response
     */
    public function test_can_create_ssh_key_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'ssh_key' => [
                    'id' => 99,
                    'name' => 'custom-new-ssh-key',
                    'fingerprint' => 'SHA256:customNewFingerprint123456',
                    'public_key' => 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIG...',
                    'labels' => [
                        'environment' => 'development',
                        'team' => 'frontend',
                    ],
                    'created' => '2023-06-20T09:15:00+00:00',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'custom-new-ssh-key',
            'public_key' => 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIG...',
        ];

        $response = $client->sshKeys()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);
        $sshKey = $response->sshKey();

        $this->assertEquals(99, $sshKey->id());
        $this->assertEquals('custom-new-ssh-key', $sshKey->name());
        $this->assertEquals('SHA256:customNewFingerprint123456', $sshKey->fingerprint());
        $this->assertEquals('ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIG...', $sshKey->publicKey());
        $this->assertEquals('development', $sshKey->labels()['environment']);
        $this->assertEquals('frontend', $sshKey->labels()['team']);

        $this->assertRequestWasMade($requests, 'ssh_keys', 'create');
    }

    /**
     * Test getting a specific SSH key
     */
    public function test_can_get_specific_ssh_key(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $sshKeyId = '42';
        $response = $client->sshKeys()->retrieve($sshKeyId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $this->assertInstanceOf(SshKey::class, $response->sshKey());

        $sshKey = $response->sshKey();
        $this->assertEquals(42, $sshKey->id());
        $this->assertEquals('test-ssh-key', $sshKey->name());
        $this->assertEquals('SHA256:nThbg6kXUpJWGl7E1IGOCspRomTxdCARLviKw6E5SY8', $sshKey->fingerprint());
        $this->assertEquals('ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC...', $sshKey->publicKey());
        $this->assertIsArray($sshKey->labels());
        $this->assertEquals('2023-01-01T00:00:00+00:00', $sshKey->created());

        $this->assertRequestWasMade($requests, 'ssh_keys', 'retrieve');
    }

    /**
     * Test getting SSH key with custom response
     */
    public function test_can_get_ssh_key_with_custom_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'ssh_key' => [
                    'id' => 123,
                    'name' => 'detailed-ssh-key',
                    'fingerprint' => 'SHA256:detailedFingerprint123456789',
                    'public_key' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQD...',
                    'labels' => [
                        'environment' => 'production',
                        'team' => 'infrastructure',
                        'project' => 'web-app',
                    ],
                    'created' => '2023-05-10T16:20:00+00:00',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $sshKeyId = '123';
        $response = $client->sshKeys()->retrieve($sshKeyId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);
        $sshKey = $response->sshKey();

        $this->assertEquals(123, $sshKey->id());
        $this->assertEquals('detailed-ssh-key', $sshKey->name());
        $this->assertEquals('SHA256:detailedFingerprint123456789', $sshKey->fingerprint());
        $this->assertEquals('ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQD...', $sshKey->publicKey());
        $this->assertEquals('production', $sshKey->labels()['environment']);
        $this->assertEquals('infrastructure', $sshKey->labels()['team']);
        $this->assertEquals('web-app', $sshKey->labels()['project']);

        $this->assertRequestWasMade($requests, 'ssh_keys', 'retrieve');
    }

    /**
     * Test updating an SSH key
     */
    public function test_can_update_ssh_key(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $sshKeyId = '42';
        $parameters = [
            'name' => 'updated-ssh-key',
            'labels' => [
                'environment' => 'staging',
                'team' => 'frontend',
            ],
        ];

        $response = $client->sshKeys()->update($sshKeyId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);
        $this->assertInstanceOf(SshKey::class, $response->sshKey());

        $sshKey = $response->sshKey();
        $this->assertEquals(42, $sshKey->id());
        $this->assertEquals('updated-ssh-key', $sshKey->name());
        $this->assertEquals('staging', $sshKey->labels()['environment']);
        $this->assertEquals('frontend', $sshKey->labels()['team']);

        $this->assertRequestWasMade($requests, 'ssh_keys', 'update');
    }

    /**
     * Test deleting an SSH key
     */
    public function test_can_delete_ssh_key(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $sshKeyId = '42';
        $response = $client->sshKeys()->delete($sshKeyId);

        $this->assertInstanceOf(DeleteResponse::class, $response);

        $this->assertRequestWasMade($requests, 'ssh_keys', 'delete');
    }

    /**
     * Test SSH key response structure
     */
    public function test_ssh_key_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->sshKeys()->list();
        $sshKeys = $response->sshKeys();

        foreach ($sshKeys as $sshKey) {
            // Test all SSH key properties
            $this->assertIsInt($sshKey->id());
            $this->assertIsString($sshKey->name());
            $this->assertIsString($sshKey->fingerprint());
            $this->assertIsString($sshKey->publicKey());
            $this->assertIsArray($sshKey->labels());
            $this->assertIsString($sshKey->created());

            // Test fingerprint format
            $this->assertStringStartsWith('SHA256:', $sshKey->fingerprint());

            // Test public key format
            $this->assertMatchesRegularExpression('/^ssh-(rsa|ed25519|ecdsa) /', $sshKey->publicKey());

            // Test labels structure
            foreach ($sshKey->labels() as $key => $value) {
                $this->assertIsString($key);
                $this->assertIsString($value);
            }

            // Test created date format
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/', $sshKey->created());
        }
    }

    /**
     * Test handling SSH key API exception
     */
    public function test_can_handle_ssh_key_api_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('SSH key not found', new Request('GET', '/ssh_keys/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('SSH key not found');

        $client->sshKeys()->retrieve('999');
    }

    /**
     * Test handling SSH key list exception
     */
    public function test_can_handle_ssh_key_list_exception(): void
    {
        $requests = [];
        $responses = [
            new RequestException('Rate limit exceeded', new Request('GET', '/ssh_keys')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $client->sshKeys()->list();
    }

    /**
     * Test handling mixed SSH key response types
     */
    public function test_can_handle_mixed_ssh_key_response_types(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
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
            ]) ?: ''),
            new RequestException('SSH key not found', new Request('GET', '/ssh_keys/999')),
        ];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $listResponse = $client->sshKeys()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->sshKeys());

        // Second call should throw exception
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('SSH key not found');
        $client->sshKeys()->retrieve('999');
    }

    /**
     * Test using individual SSH key fake
     */
    public function test_using_individual_ssh_key_fake(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $sshKeysFake = $client->sshKeys();

        $response = $sshKeysFake->list(['page' => 1, 'per_page' => 5]);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->sshKeys());

        $sshKeysFake->assertSent(function (array $request) {
            return $request['resource'] === 'ssh_keys' &&
                   $request['method'] === 'list' &&
                   isset($request['parameters']['page']) &&
                   $request['parameters']['page'] === 1 &&
                   isset($request['parameters']['per_page']) &&
                   $request['parameters']['per_page'] === 5;
        });
    }

    /**
     * Test SSH key fake assert not sent
     */
    public function test_ssh_key_fake_assert_not_sent(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $sshKeysFake = $client->sshKeys();

        // No requests made yet
        $sshKeysFake->assertNotSent();

        // Make a request
        $sshKeysFake->list();

        // Now it should fail
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Requests were sent to ssh_keys.');
        $sshKeysFake->assertNotSent();
    }

    /**
     * Test SSH key workflow simulation
     */
    public function test_ssh_key_workflow_simulation(): void
    {
        $requests = [];
        $responses = [
            // List SSH keys response
            new Response(200, [], json_encode([
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
            ]) ?: ''),
            // Get specific SSH key response
            new Response(200, [], json_encode([
                'ssh_key' => [
                    'id' => 1,
                    'name' => 'my-ssh-key',
                    'fingerprint' => 'SHA256:nThbg6kXUpJWGl7E1IGOCspRomTxdCARLviKw6E5SY8',
                    'public_key' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC...',
                    'labels' => [],
                    'created' => '2023-01-01T00:00:00+00:00',
                ],
            ]) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        // Simulate a workflow: list SSH keys, then get details of the first one
        $listResponse = $client->sshKeys()->list();
        $this->assertInstanceOf(ListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->sshKeys());

        $firstSshKey = $listResponse->sshKeys()[0];
        $sshKeyId = (string) $firstSshKey->id();

        $getResponse = $client->sshKeys()->retrieve($sshKeyId);
        $this->assertInstanceOf(RetrieveResponse::class, $getResponse);
        $this->assertEquals($firstSshKey->id(), $getResponse->sshKey()->id());
        $this->assertEquals($firstSshKey->name(), $getResponse->sshKey()->name());

        // Verify both requests were made
        $this->assertRequestWasMade($requests, 'ssh_keys', 'list');
        $this->assertRequestWasMade($requests, 'ssh_keys', 'retrieve');
    }

    /**
     * Test SSH key error response handling
     */
    public function test_ssh_key_error_response_handling(): void
    {
        $requests = [];
        $responses = [
            new RequestException('SSH key not found', new Request('GET', '/ssh_keys/nonexistent')),
        ];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('SSH key not found');
        $client->sshKeys()->retrieve('nonexistent');
    }

    /**
     * Test SSH key empty list response
     */
    public function test_ssh_key_empty_list_response(): void
    {
        $requests = [];
        $responses = [
            new Response(200, [], json_encode([
                'ssh_keys' => [],
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

        $response = $client->sshKeys()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(0, $response->sshKeys());
        $this->assertEmpty($response->sshKeys());
    }

    /**
     * Test SSH key pagination
     */
    public function test_ssh_key_pagination(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->sshKeys()->list();
        $pagination = $response->pagination();

        // Test pagination structure
        $this->assertIsArray($pagination);
        $this->assertArrayHasKey('current_page', $pagination);
        $this->assertArrayHasKey('per_page', $pagination);
        $this->assertArrayHasKey('total', $pagination);
        $this->assertArrayHasKey('last_page', $pagination);
        $this->assertArrayHasKey('from', $pagination);
        $this->assertArrayHasKey('to', $pagination);
        $this->assertArrayHasKey('has_more_pages', $pagination);
        $this->assertArrayHasKey('links', $pagination);

        // Test pagination values
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(25, $pagination['per_page']);
        $this->assertEquals(1, $pagination['total']);
        $this->assertEquals(1, $pagination['last_page']);
        $this->assertFalse($pagination['has_more_pages']);

        // Test links structure
        $this->assertIsArray($pagination['links']);
        $this->assertArrayHasKey('first', $pagination['links']);
        $this->assertArrayHasKey('last', $pagination['links']);
        $this->assertArrayHasKey('prev', $pagination['links']);
        $this->assertArrayHasKey('next', $pagination['links']);
    }

    /**
     * Test SSH key to array conversion
     */
    public function test_ssh_key_to_array_conversion(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->sshKeys()->list();
        $sshKeys = $response->sshKeys();

        foreach ($sshKeys as $sshKey) {
            $array = $sshKey->toArray();

            $this->assertIsArray($array);
            $this->assertArrayHasKey('id', $array);
            $this->assertArrayHasKey('name', $array);
            $this->assertArrayHasKey('fingerprint', $array);
            $this->assertArrayHasKey('public_key', $array);
            $this->assertArrayHasKey('labels', $array);
            $this->assertArrayHasKey('created', $array);

            $this->assertEquals($sshKey->id(), $array['id']);
            $this->assertEquals($sshKey->name(), $array['name']);
            $this->assertEquals($sshKey->fingerprint(), $array['fingerprint']);
            $this->assertEquals($sshKey->publicKey(), $array['public_key']);
        }
    }

    /**
     * Test SSH key fingerprint validation
     */
    public function test_ssh_key_fingerprint_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->sshKeys()->list();
        $sshKeys = $response->sshKeys();

        foreach ($sshKeys as $sshKey) {
            $fingerprint = $sshKey->fingerprint();

            // Test that fingerprint starts with SHA256:
            $this->assertStringStartsWith('SHA256:', $fingerprint);

            // Test that fingerprint has correct length (SHA256: + 43 base64 characters)
            $this->assertEquals(50, strlen($fingerprint));

            // Test that the part after SHA256: exists and is not empty
            $hashPart = substr($fingerprint, 7);
            $this->assertNotEmpty($hashPart);
        }
    }

    /**
     * Test SSH key public key validation
     */
    public function test_ssh_key_public_key_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->sshKeys()->list();
        $sshKeys = $response->sshKeys();

        foreach ($sshKeys as $sshKey) {
            $publicKey = $sshKey->publicKey();

            // Test that public key starts with ssh-rsa, ssh-ed25519, or ssh-ecdsa
            $this->assertMatchesRegularExpression('/^ssh-(rsa|ed25519|ecdsa) /', $publicKey);

            // Test that public key has at least 3 parts (type, key, comment)
            $parts = explode(' ', $publicKey);
            $this->assertGreaterThanOrEqual(2, count($parts));

            // Test that the key part exists and is not empty
            if (count($parts) >= 2) {
                $keyPart = $parts[1];
                $this->assertNotEmpty($keyPart);
            }
        }
    }

    /**
     * Test SSH key creation with different key types
     */
    public function test_ssh_key_creation_with_different_key_types(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        // Test RSA key
        $rsaParameters = [
            'name' => 'rsa-ssh-key',
            'public_key' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC...',
            'labels' => [
                'type' => 'rsa',
            ],
        ];

        $rsaResponse = $client->sshKeys()->create($rsaParameters);
        $this->assertInstanceOf(CreateResponse::class, $rsaResponse);
        $this->assertEquals('rsa-ssh-key', $rsaResponse->sshKey()->name());

        // Test ED25519 key
        $ed25519Parameters = [
            'name' => 'ed25519-ssh-key',
            'public_key' => 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIG...',
            'labels' => [
                'type' => 'ed25519',
            ],
        ];

        $ed25519Response = $client->sshKeys()->create($ed25519Parameters);
        $this->assertInstanceOf(CreateResponse::class, $ed25519Response);
        $this->assertEquals('ed25519-ssh-key', $ed25519Response->sshKey()->name());
    }

    /**
     * Test SSH key labels validation
     */
    public function test_ssh_key_labels_validation(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->sshKeys()->list();
        $sshKeys = $response->sshKeys();

        foreach ($sshKeys as $sshKey) {
            $labels = $sshKey->labels();
            $this->assertIsArray($labels);

            foreach ($labels as $key => $value) {
                $this->assertIsString($key);
                $this->assertIsString($value);
                $this->assertNotEmpty($key);
                $this->assertNotEmpty($value);
            }
        }
    }
}
