<?php

declare(strict_types=1);

namespace Tests;

use Boci\HetznerLaravel\Responses\Certificates\Certificate;
use Boci\HetznerLaravel\Responses\Certificates\CreateResponse;
use Boci\HetznerLaravel\Responses\Certificates\DeleteResponse;
use Boci\HetznerLaravel\Responses\Certificates\ListResponse;
use Boci\HetznerLaravel\Responses\Certificates\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Certificates\UpdateResponse;
use Boci\HetznerLaravel\Testing\CertificatesFake;
use Boci\HetznerLaravel\Testing\TestCase;
use GuzzleHttp\Psr7\Response;

/**
 * Certificates Test Suite
 *
 * This test suite covers all functionality related to the Certificates resource,
 * including listing, creating, getting, updating, and deleting SSL/TLS certificates.
 */
final class CertificatesTest extends TestCase
{
    /**
     * Test listing certificates with fake data
     */
    public function test_can_list_certificates_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->certificates()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->certificates());

        $certificates = $response->certificates();
        $this->assertInstanceOf(Certificate::class, $certificates[0]);
        $this->assertEquals(1, $certificates[0]->id());
        $this->assertEquals('test-certificate', $certificates[0]->name());
        $this->assertEquals('uploaded', $certificates[0]->type());

        $this->assertRequestWasMade($requests, 'certificates', 'list');
    }

    /**
     * Test listing certificates with custom response
     */
    public function test_can_list_certificates_with_custom_response(): void
    {
        $customData = [
            'certificates' => [
                [
                    'id' => 1,
                    'name' => 'web-certificate',
                    'type' => 'uploaded',
                    'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...\n-----END CERTIFICATE-----',
                    'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...\n-----END PRIVATE KEY-----',
                    'fingerprint' => 'SHA256:1234567890abcdef1234567890abcdef12345678',
                    'not_valid_before' => '2023-01-01T00:00:00+00:00',
                    'not_valid_after' => '2024-01-01T00:00:00+00:00',
                    'domain_names' => ['example.com', 'www.example.com'],
                    'labels' => ['environment' => 'production'],
                    'created' => '2023-01-01T00:00:00+00:00',
                ],
                [
                    'id' => 2,
                    'name' => 'api-certificate',
                    'type' => 'uploaded',
                    'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEB...\n-----END CERTIFICATE-----',
                    'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQD...\n-----END PRIVATE KEY-----',
                    'fingerprint' => 'SHA256:abcdef1234567890abcdef1234567890abcdef12',
                    'not_valid_before' => '2023-02-01T00:00:00+00:00',
                    'not_valid_after' => '2024-02-01T00:00:00+00:00',
                    'domain_names' => ['api.example.com'],
                    'labels' => ['environment' => 'production', 'service' => 'api'],
                    'created' => '2023-02-01T00:00:00+00:00',
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
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->certificates()->list();

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(2, $response->certificates());

        $certificates = $response->certificates();
        $this->assertEquals('web-certificate', $certificates[0]->name());
        $this->assertEquals('api-certificate', $certificates[1]->name());

        $domainNames = $certificates[0]->domainNames();
        $this->assertCount(2, $domainNames);
        $this->assertContains('example.com', $domainNames);
        $this->assertContains('www.example.com', $domainNames);

        $labels = $certificates[1]->labels();
        $this->assertEquals('production', $labels['environment']);
        $this->assertEquals('api', $labels['service']);

        $this->assertRequestWasMade($requests, 'certificates', 'list');
    }

    /**
     * Test listing certificates with query parameters
     */
    public function test_can_list_certificates_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 2,
            'per_page' => 10,
            'name' => 'web-certificate',
            'type' => 'uploaded',
        ];

        $response = $client->certificates()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertRequestWasMade($requests, 'certificates', 'list');
    }

    /**
     * Test creating a certificate
     */
    public function test_can_create_certificate(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'test-certificate',
            'type' => 'uploaded',
            'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...\n-----END CERTIFICATE-----',
            'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...\n-----END PRIVATE KEY-----',
            'labels' => ['environment' => 'test'],
        ];

        $response = $client->certificates()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);

        $certificate = $response->certificate();
        $this->assertInstanceOf(Certificate::class, $certificate);
        $this->assertEquals(1, $certificate->id());
        $this->assertEquals('test-certificate', $certificate->name());
        $this->assertEquals('uploaded', $certificate->type());

        $this->assertRequestWasMade($requests, 'certificates', 'create');
    }

    /**
     * Test creating a certificate with custom response
     */
    public function test_can_create_certificate_with_custom_response(): void
    {
        $customData = [
            'certificate' => [
                'id' => 123,
                'name' => 'custom-certificate',
                'type' => 'uploaded',
                'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEC...\n-----END CERTIFICATE-----',
                'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQE...\n-----END PRIVATE KEY-----',
                'fingerprint' => 'SHA256:custom1234567890abcdef1234567890abcdef',
                'not_valid_before' => '2023-01-01T12:00:00+00:00',
                'not_valid_after' => '2024-01-01T12:00:00+00:00',
                'domain_names' => ['custom.example.com', 'www.custom.example.com'],
                'labels' => ['environment' => 'development', 'team' => 'backend'],
                'created' => '2023-01-01T12:00:00+00:00',
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'name' => 'custom-certificate',
            'type' => 'uploaded',
            'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEC...\n-----END CERTIFICATE-----',
            'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQE...\n-----END PRIVATE KEY-----',
        ];

        $response = $client->certificates()->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $response);

        $certificate = $response->certificate();
        $this->assertEquals(123, $certificate->id());
        $this->assertEquals('custom-certificate', $certificate->name());

        $domainNames = $certificate->domainNames();
        $this->assertCount(2, $domainNames);
        $this->assertContains('custom.example.com', $domainNames);

        $labels = $certificate->labels();
        $this->assertEquals('development', $labels['environment']);
        $this->assertEquals('backend', $labels['team']);

        $this->assertRequestWasMade($requests, 'certificates', 'create');
    }

    /**
     * Test getting a specific certificate
     */
    public function test_can_get_specific_certificate(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $certificateId = '123';
        $response = $client->certificates()->retrieve($certificateId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);

        $certificate = $response->certificate();
        $this->assertInstanceOf(Certificate::class, $certificate);
        $this->assertEquals(123, $certificate->id());
        $this->assertEquals('test-certificate', $certificate->name());

        $this->assertRequestWasMade($requests, 'certificates', 'retrieve');
    }

    /**
     * Test getting a specific certificate with custom response
     */
    public function test_can_get_specific_certificate_with_custom_response(): void
    {
        $customData = [
            'certificate' => [
                'id' => 456,
                'name' => 'production-certificate',
                'type' => 'uploaded',
                'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQED...\n-----END CERTIFICATE-----',
                'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQF...\n-----END PRIVATE KEY-----',
                'fingerprint' => 'SHA256:production1234567890abcdef1234567890',
                'not_valid_before' => '2023-01-01T10:00:00+00:00',
                'not_valid_after' => '2024-01-01T10:00:00+00:00',
                'domain_names' => ['production.example.com', 'api.production.example.com', 'admin.production.example.com'],
                'labels' => ['environment' => 'production', 'team' => 'devops', 'critical' => 'true'],
                'created' => '2023-01-01T10:00:00+00:00',
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $certificateId = '456';
        $response = $client->certificates()->retrieve($certificateId);

        $this->assertInstanceOf(RetrieveResponse::class, $response);

        $certificate = $response->certificate();
        $this->assertEquals(456, $certificate->id());
        $this->assertEquals('production-certificate', $certificate->name());

        $domainNames = $certificate->domainNames();
        $this->assertCount(3, $domainNames);
        $this->assertContains('production.example.com', $domainNames);
        $this->assertContains('api.production.example.com', $domainNames);
        $this->assertContains('admin.production.example.com', $domainNames);

        $labels = $certificate->labels();
        $this->assertEquals('production', $labels['environment']);
        $this->assertEquals('devops', $labels['team']);
        $this->assertEquals('true', $labels['critical']);

        $this->assertRequestWasMade($requests, 'certificates', 'retrieve');
    }

    /**
     * Test updating a certificate
     */
    public function test_can_update_certificate(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $certificateId = '123';
        $parameters = [
            'name' => 'updated-certificate-name',
            'labels' => ['environment' => 'staging', 'updated' => 'true'],
        ];

        $response = $client->certificates()->update($certificateId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);

        $certificate = $response->certificate();
        $this->assertInstanceOf(Certificate::class, $certificate);
        $this->assertEquals(123, $certificate->id());
        $this->assertEquals('updated-certificate-name', $certificate->name());

        $this->assertRequestWasMade($requests, 'certificates', 'update');
    }

    /**
     * Test updating a certificate with custom response
     */
    public function test_can_update_certificate_with_custom_response(): void
    {
        $customData = [
            'certificate' => [
                'id' => 789,
                'name' => 'updated-production-certificate',
                'type' => 'uploaded',
                'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEE...\n-----END CERTIFICATE-----',
                'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQG...\n-----END PRIVATE KEY-----',
                'fingerprint' => 'SHA256:updated1234567890abcdef1234567890ab',
                'not_valid_before' => '2023-01-01T10:00:00+00:00',
                'not_valid_after' => '2024-01-01T10:00:00+00:00',
                'domain_names' => ['updated.example.com', 'www.updated.example.com'],
                'labels' => ['environment' => 'production', 'updated' => 'true', 'version' => '2.0'],
                'created' => '2023-01-01T10:00:00+00:00',
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $certificateId = '789';
        $parameters = [
            'name' => 'updated-production-certificate',
            'labels' => ['environment' => 'production', 'updated' => 'true', 'version' => '2.0'],
        ];

        $response = $client->certificates()->update($certificateId, $parameters);

        $this->assertInstanceOf(UpdateResponse::class, $response);

        $certificate = $response->certificate();
        $this->assertEquals(789, $certificate->id());
        $this->assertEquals('updated-production-certificate', $certificate->name());

        $labels = $certificate->labels();
        $this->assertEquals('production', $labels['environment']);
        $this->assertEquals('true', $labels['updated']);
        $this->assertEquals('2.0', $labels['version']);

        $this->assertRequestWasMade($requests, 'certificates', 'update');
    }

    /**
     * Test deleting a certificate
     */
    public function test_can_delete_certificate(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $certificateId = '123';
        $response = $client->certificates()->delete($certificateId);

        $this->assertInstanceOf(DeleteResponse::class, $response);

        $this->assertRequestWasMade($requests, 'certificates', 'delete');
    }

    /**
     * Test certificate response structure validation
     */
    public function test_certificate_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->certificates()->list();
        $certificates = $response->certificates();

        foreach ($certificates as $certificate) {
            $this->assertIsInt($certificate->id());
            $this->assertIsString($certificate->name());
            $this->assertIsString($certificate->type());
            $this->assertIsString($certificate->created());
            $this->assertIsArray($certificate->domainNames());
            $this->assertIsArray($certificate->labels());

            // Optional fields
            $this->assertIsString($certificate->certificate());
            $this->assertIsString($certificate->privateKey());
            $this->assertIsString($certificate->fingerprint());
            $this->assertIsString($certificate->notValidBefore());
            $this->assertIsString($certificate->notValidAfter());
        }
    }

    /**
     * Test certificate domain names structure
     */
    public function test_certificate_domain_names_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->certificates()->list();
        $certificates = $response->certificates();

        foreach ($certificates as $certificate) {
            $domainNames = $certificate->domainNames();
            $this->assertIsArray($domainNames);

            foreach ($domainNames as $domainName) {
                $this->assertIsString($domainName);
                $this->assertNotEmpty($domainName);
            }
        }
    }

    /**
     * Test certificate labels structure
     */
    public function test_certificate_labels_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->certificates()->list();
        $certificates = $response->certificates();

        foreach ($certificates as $certificate) {
            $labels = $certificate->labels();
            $this->assertIsArray($labels);

            foreach ($labels as $key => $value) {
                $this->assertIsString($key);
                $this->assertIsString($value);
            }
        }
    }

    /**
     * Test handling API exceptions
     */
    public function test_can_handle_certificates_api_exceptions(): void
    {
        $exception = new \Exception('API connection failed');

        $requests = [];
        $responses = [$exception];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API connection failed');

        $client->certificates()->list();
    }

    /**
     * Test handling error responses
     */
    public function test_can_handle_certificates_error_responses(): void
    {
        $errorResponse = new Response(400, [], json_encode([
            'error' => [
                'code' => 'invalid_request',
                'message' => 'Invalid certificate parameters provided',
            ],
        ]) ?: '');

        $requests = [];
        $responses = [$errorResponse];
        $client = $this->fakeClient($responses, $requests);

        // The fake client will return the error response as-is
        $response = $client->certificates()->create(['name' => 'invalid']);
        $this->assertInstanceOf(CreateResponse::class, $response);

        $this->assertRequestWasMade($requests, 'certificates', 'create');
    }

    /**
     * Test using individual certificates fake
     */
    public function test_using_individual_certificates_fake(): void
    {
        $responses = [];
        $requests = [];

        $certificatesFake = new CertificatesFake($responses, $requests);

        // Test various certificate operations
        $certificatesFake->list();
        $certificatesFake->create(['name' => 'test-certificate']);
        $certificatesFake->retrieve('123');
        $certificatesFake->update('123', ['name' => 'updated']);
        $certificatesFake->delete('123');

        // Assert all requests were made
        $this->assertCount(5, $requests);

        // Test resource-specific assertions
        $certificatesFake->assertSent(function ($request) {
            return $request['method'] === 'list';
        });

        $certificatesFake->assertSent(function ($request) {
            return $request['method'] === 'create' &&
                   $request['parameters']['name'] === 'test-certificate';
        });

        $certificatesFake->assertSent(function ($request) {
            return $request['method'] === 'retrieve' &&
                   $request['parameters']['certificateId'] === '123';
        });
    }

    /**
     * Test mixed response types for certificates
     */
    public function test_can_handle_mixed_certificates_response_types(): void
    {
        $responses = [
            new Response(200, [], json_encode([
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
                        'domain_names' => ['example.com'],
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
            new \Exception('Network timeout'),
        ];

        $requests = [];
        $client = $this->fakeClient($responses, $requests);

        // First call should succeed
        $response = $client->certificates()->list();
        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertCount(1, $response->certificates());

        // Second call should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Network timeout');
        $client->certificates()->retrieve('456');
    }

    /**
     * Test certificates with complex parameters
     */
    public function test_can_list_certificates_with_complex_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $parameters = [
            'page' => 3,
            'per_page' => 50,
            'name' => 'production',
            'type' => 'uploaded',
            'sort' => 'id:desc',
        ];

        $response = $client->certificates()->list($parameters);

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertRequestWasMade($requests, 'certificates', 'list');
    }

    /**
     * Test certificate workflow simulation
     */
    public function test_certificate_workflow_simulation(): void
    {
        $responses = [
            // Create certificate
            new Response(200, [], json_encode([
                'certificate' => [
                    'id' => 1,
                    'name' => 'web-certificate',
                    'type' => 'uploaded',
                    'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...\n-----END CERTIFICATE-----',
                    'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...\n-----END PRIVATE KEY-----',
                    'fingerprint' => 'SHA256:web1234567890abcdef1234567890abcdef',
                    'not_valid_before' => '2023-01-01T10:00:00+00:00',
                    'not_valid_after' => '2024-01-01T10:00:00+00:00',
                    'domain_names' => ['example.com', 'www.example.com'],
                    'labels' => ['environment' => 'production'],
                    'created' => '2023-01-01T10:00:00+00:00',
                ],
            ]) ?: ''),
            // Get certificate
            new Response(200, [], json_encode([
                'certificate' => [
                    'id' => 1,
                    'name' => 'web-certificate',
                    'type' => 'uploaded',
                    'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...\n-----END CERTIFICATE-----',
                    'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...\n-----END PRIVATE KEY-----',
                    'fingerprint' => 'SHA256:web1234567890abcdef1234567890abcdef',
                    'not_valid_before' => '2023-01-01T10:00:00+00:00',
                    'not_valid_after' => '2024-01-01T10:00:00+00:00',
                    'domain_names' => ['example.com', 'www.example.com'],
                    'labels' => ['environment' => 'production'],
                    'created' => '2023-01-01T10:00:00+00:00',
                ],
            ]) ?: ''),
            // Update certificate
            new Response(200, [], json_encode([
                'certificate' => [
                    'id' => 1,
                    'name' => 'updated-web-certificate',
                    'type' => 'uploaded',
                    'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...\n-----END CERTIFICATE-----',
                    'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...\n-----END PRIVATE KEY-----',
                    'fingerprint' => 'SHA256:web1234567890abcdef1234567890abcdef',
                    'not_valid_before' => '2023-01-01T10:00:00+00:00',
                    'not_valid_after' => '2024-01-01T10:00:00+00:00',
                    'domain_names' => ['example.com', 'www.example.com'],
                    'labels' => ['environment' => 'production', 'updated' => 'true'],
                    'created' => '2023-01-01T10:00:00+00:00',
                ],
            ]) ?: ''),
        ];

        $requests = [];
        $client = $this->fakeClient($responses, $requests);

        // Simulate workflow: create certificate, get details, then update
        $createResponse = $client->certificates()->create([
            'name' => 'web-certificate',
            'type' => 'uploaded',
            'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...\n-----END CERTIFICATE-----',
            'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...\n-----END PRIVATE KEY-----',
        ]);

        $certificate = $createResponse->certificate();
        $this->assertEquals(1, $certificate->id());
        $this->assertEquals('web-certificate', $certificate->name());

        $getResponse = $client->certificates()->retrieve('1');
        $retrievedCertificate = $getResponse->certificate();
        $this->assertEquals($certificate->id(), $retrievedCertificate->id());

        $updateResponse = $client->certificates()->update('1', [
            'name' => 'updated-web-certificate',
            'labels' => ['environment' => 'production', 'updated' => 'true'],
        ]);
        $updatedCertificate = $updateResponse->certificate();
        $this->assertEquals('updated-web-certificate', $updatedCertificate->name());

        // Verify all requests were made
        $this->assertRequestWasMade($requests, 'certificates', 'create');
        $this->assertRequestWasMade($requests, 'certificates', 'retrieve');
        $this->assertRequestWasMade($requests, 'certificates', 'update');
    }

    /**
     * Test certificates resource assertions
     */
    public function test_certificates_resource_assertions(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $client->certificates()->list(['name' => 'production']);
        $client->certificates()->create(['name' => 'test-certificate']);
        $client->certificates()->retrieve('999');
        $client->certificates()->update('999', ['name' => 'updated']);
        $client->certificates()->delete('999');

        // Test that specific requests were made
        $this->assertRequestWasMade($requests, 'certificates', 'list');
        $this->assertRequestWasMade($requests, 'certificates', 'create');
        $this->assertRequestWasMade($requests, 'certificates', 'retrieve');
        $this->assertRequestWasMade($requests, 'certificates', 'update');
        $this->assertRequestWasMade($requests, 'certificates', 'delete');

        // Test that no other requests were made
        $this->assertNoRequestWasMade($requests, 'servers');
        $this->assertNoRequestWasMade($requests, 'firewalls');
    }

    /**
     * Test certificates with specific certificate data
     */
    public function test_certificates_with_specific_certificate_data(): void
    {
        $customData = [
            'certificate' => [
                'id' => 200,
                'name' => 'wildcard-certificate',
                'type' => 'uploaded',
                'certificate' => '-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEF...\n-----END CERTIFICATE-----',
                'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQH...\n-----END PRIVATE KEY-----',
                'fingerprint' => 'SHA256:wildcard1234567890abcdef1234567890',
                'not_valid_before' => '2023-01-01T15:00:00+00:00',
                'not_valid_after' => '2024-01-01T15:00:00+00:00',
                'domain_names' => ['*.example.com', 'example.com', 'api.example.com', 'admin.example.com'],
                'labels' => ['environment' => 'production', 'type' => 'wildcard', 'team' => 'devops'],
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->certificates()->retrieve('200');
        $certificate = $response->certificate();

        $this->assertEquals(200, $certificate->id());
        $this->assertEquals('wildcard-certificate', $certificate->name());
        $this->assertEquals('uploaded', $certificate->type());
        $this->assertEquals('2023-01-01T15:00:00+00:00', $certificate->notValidBefore());
        $this->assertEquals('2024-01-01T15:00:00+00:00', $certificate->notValidAfter());

        $domainNames = $certificate->domainNames();
        $this->assertCount(4, $domainNames);
        $this->assertContains('*.example.com', $domainNames);
        $this->assertContains('example.com', $domainNames);
        $this->assertContains('api.example.com', $domainNames);
        $this->assertContains('admin.example.com', $domainNames);

        $labels = $certificate->labels();
        $this->assertEquals('production', $labels['environment']);
        $this->assertEquals('wildcard', $labels['type']);
        $this->assertEquals('devops', $labels['team']);

        $this->assertRequestWasMade($requests, 'certificates', 'retrieve');
    }

    /**
     * Test certificate with different types
     */
    public function test_certificate_with_different_types(): void
    {
        $customData = [
            'certificates' => [
                [
                    'id' => 1,
                    'name' => 'uploaded-cert',
                    'type' => 'uploaded',
                    'certificate' => '-----BEGIN CERTIFICATE-----\n...',
                    'private_key' => '-----BEGIN PRIVATE KEY-----\n...',
                    'fingerprint' => 'SHA256:uploaded1234567890abcdef1234567890',
                    'not_valid_before' => '2023-01-01T00:00:00+00:00',
                    'not_valid_after' => '2024-01-01T00:00:00+00:00',
                    'domain_names' => ['uploaded.example.com'],
                    'labels' => ['type' => 'uploaded'],
                    'created' => '2023-01-01T00:00:00+00:00',
                ],
                [
                    'id' => 2,
                    'name' => 'managed-cert',
                    'type' => 'managed',
                    'certificate' => null,
                    'private_key' => null,
                    'fingerprint' => 'SHA256:managed1234567890abcdef1234567890',
                    'not_valid_before' => '2023-01-01T00:00:00+00:00',
                    'not_valid_after' => '2024-01-01T00:00:00+00:00',
                    'domain_names' => ['managed.example.com'],
                    'labels' => ['type' => 'managed'],
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
                    'total_entries' => 2,
                ],
            ],
        ];

        $requests = [];
        $responses = [
            new Response(200, [], json_encode($customData) ?: ''),
        ];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->certificates()->list();
        $certificates = $response->certificates();

        $this->assertCount(2, $certificates);

        $uploadedCert = $certificates[0];
        $this->assertEquals('uploaded', $uploadedCert->type());
        $this->assertNotNull($uploadedCert->certificate());
        $this->assertNotNull($uploadedCert->privateKey());

        $managedCert = $certificates[1];
        $this->assertEquals('managed', $managedCert->type());
        $this->assertNull($managedCert->certificate());
        $this->assertNull($managedCert->privateKey());

        $this->assertRequestWasMade($requests, 'certificates', 'list');
    }
}
