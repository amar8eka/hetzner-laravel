<?php

namespace Tests;

use Boci\HetznerLaravel\Responses\Billing\ListPricingResponse;
use Boci\HetznerLaravel\Testing\TestCase;
use Exception;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Billing Test Suite
 *
 * Comprehensive test suite for the Hetzner Cloud Billing API functionality.
 * This test file covers all billing-related operations including pricing information
 * retrieval, error handling, and parameter validation.
 */
class BillingTest extends TestCase
{
    /**
     * Test listing pricing information with auto-generated fake data
     */
    public function test_can_list_pricing_with_fake_data(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing();

        $this->assertInstanceOf(
            ListPricingResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }

    /**
     * Test listing pricing information with custom response data
     */
    public function test_can_list_pricing_with_custom_response(): void
    {
        $customResponse = new Response(200, [], json_encode([
            'pricing' => [
                'currency' => 'USD',
                'vat_rate' => '20.00',
                'image' => [
                    'price_per_gb_month' => [
                        'net' => '0.0050000000',
                        'gross' => '0.0060000000',
                    ],
                ],
                'floating_ip' => [
                    'price_monthly' => [
                        'net' => '1.5000000000',
                        'gross' => '1.8000000000',
                    ],
                ],
                'floating_ip_type' => [
                    'ipv4' => [
                        'price_monthly' => [
                            'net' => '1.5000000000',
                            'gross' => '1.8000000000',
                        ],
                    ],
                    'ipv6' => [
                        'price_monthly' => [
                            'net' => '1.5000000000',
                            'gross' => '1.8000000000',
                        ],
                    ],
                ],
                'load_balancer_type' => [
                    'lb11' => [
                        'price_monthly' => [
                            'net' => '4.0000000000',
                            'gross' => '4.8000000000',
                        ],
                    ],
                    'lb21' => [
                        'price_monthly' => [
                            'net' => '7.0000000000',
                            'gross' => '8.4000000000',
                        ],
                    ],
                    'lb31' => [
                        'price_monthly' => [
                            'net' => '13.0000000000',
                            'gross' => '15.6000000000',
                        ],
                    ],
                ],
                'primary_ip' => [
                    'price_monthly' => [
                        'net' => '1.5000000000',
                        'gross' => '1.8000000000',
                    ],
                ],
                'primary_ip_type' => [
                    'ipv4' => [
                        'price_monthly' => [
                            'net' => '1.5000000000',
                            'gross' => '1.8000000000',
                        ],
                    ],
                    'ipv6' => [
                        'price_monthly' => [
                            'net' => '1.5000000000',
                            'gross' => '1.8000000000',
                        ],
                    ],
                ],
                'server_type' => [
                    'cx11' => [
                        'price_monthly' => [
                            'net' => '3.5000000000',
                            'gross' => '4.2000000000',
                        ],
                    ],
                    'cx21' => [
                        'price_monthly' => [
                            'net' => '7.0000000000',
                            'gross' => '8.4000000000',
                        ],
                    ],
                    'cx31' => [
                        'price_monthly' => [
                            'net' => '14.0000000000',
                            'gross' => '16.8000000000',
                        ],
                    ],
                    'cx41' => [
                        'price_monthly' => [
                            'net' => '28.0000000000',
                            'gross' => '33.6000000000',
                        ],
                    ],
                    'cx51' => [
                        'price_monthly' => [
                            'net' => '56.0000000000',
                            'gross' => '67.2000000000',
                        ],
                    ],
                ],
                'traffic' => [
                    'price_per_tb' => [
                        'net' => '1.2000000000',
                        'gross' => '1.4400000000',
                    ],
                ],
                'volume' => [
                    'price_per_gb_month' => [
                        'net' => '0.0500000000',
                        'gross' => '0.0600000000',
                    ],
                ],
            ],
        ]) ?: '');

        $requests = [];
        $responses = [$customResponse];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing();

        $pricing = $response->pricing();
        $this->assertEquals('USD', $pricing->currency());
        $this->assertEquals('20.00', $pricing->vatRate());

        // Test server type pricing
        $serverTypes = $pricing->serverType();
        $this->assertEquals('3.5000000000', $serverTypes['cx11']['price_monthly']['net']);
        $this->assertEquals('4.2000000000', $serverTypes['cx11']['price_monthly']['gross']);

        // Test volume pricing
        $volume = $pricing->volume();
        $this->assertEquals('0.0500000000', $volume['price_per_gb_month']['net']);
        $this->assertEquals('0.0600000000', $volume['price_per_gb_month']['gross']);

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }

    /**
     * Test listing pricing information with query parameters
     */
    public function test_can_list_pricing_with_query_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing([
            'currency' => 'USD',
            'include_tax' => true,
        ]);

        $this->assertInstanceOf(
            ListPricingResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing', function ($request) {
            return $request['parameters']['currency'] === 'USD' &&
                   $request['parameters']['include_tax'] === true;
        });
    }

    /**
     * Test pricing response structure and methods
     */
    public function test_pricing_response_structure(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing();
        $pricing = $response->pricing();

        // Test basic pricing information
        $this->assertIsString($pricing->currency());
        $this->assertIsString($pricing->vatRate());

        // Test all pricing categories exist
        $this->assertIsArray($pricing->image());
        $this->assertIsArray($pricing->floatingIp());
        $this->assertIsArray($pricing->floatingIpType());
        $this->assertIsArray($pricing->loadBalancerType());
        $this->assertIsArray($pricing->primaryIp());
        $this->assertIsArray($pricing->primaryIpType());
        $this->assertIsArray($pricing->serverType());
        $this->assertIsArray($pricing->traffic());
        $this->assertIsArray($pricing->volume());

        // Test toArray method
        $pricingArray = $pricing->toArray();
        $this->assertIsArray($pricingArray);
        $this->assertArrayHasKey('currency', $pricingArray);
        $this->assertArrayHasKey('vat_rate', $pricingArray);

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }

    /**
     * Test server type pricing details
     */
    public function test_server_type_pricing_details(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing();
        $pricing = $response->pricing();
        $serverTypes = $pricing->serverType();

        // Test that common server types exist
        $this->assertArrayHasKey('cx11', $serverTypes);
        $this->assertArrayHasKey('cx21', $serverTypes);
        $this->assertArrayHasKey('cx31', $serverTypes);

        // Test pricing structure for each server type
        foreach (['cx11', 'cx21', 'cx31'] as $serverType) {
            $this->assertArrayHasKey('price_monthly', $serverTypes[$serverType]);
            $this->assertArrayHasKey('net', $serverTypes[$serverType]['price_monthly']);
            $this->assertArrayHasKey('gross', $serverTypes[$serverType]['price_monthly']);

            // Test that prices are numeric strings
            $this->assertIsString($serverTypes[$serverType]['price_monthly']['net']);
            $this->assertIsString($serverTypes[$serverType]['price_monthly']['gross']);
        }

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }

    /**
     * Test load balancer type pricing details
     */
    public function test_load_balancer_type_pricing_details(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing();
        $pricing = $response->pricing();
        $loadBalancerTypes = $pricing->loadBalancerType();

        // Test that common load balancer types exist
        $this->assertArrayHasKey('lb11', $loadBalancerTypes);
        $this->assertArrayHasKey('lb21', $loadBalancerTypes);
        $this->assertArrayHasKey('lb31', $loadBalancerTypes);

        // Test pricing structure for each load balancer type
        foreach (['lb11', 'lb21', 'lb31'] as $lbType) {
            $this->assertArrayHasKey('price_monthly', $loadBalancerTypes[$lbType]);
            $this->assertArrayHasKey('net', $loadBalancerTypes[$lbType]['price_monthly']);
            $this->assertArrayHasKey('gross', $loadBalancerTypes[$lbType]['price_monthly']);
        }

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }

    /**
     * Test floating IP pricing details
     */
    public function test_floating_ip_pricing_details(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing();
        $pricing = $response->pricing();

        // Test floating IP pricing
        $floatingIp = $pricing->floatingIp();
        $this->assertArrayHasKey('price_monthly', $floatingIp);
        $this->assertArrayHasKey('net', $floatingIp['price_monthly']);
        $this->assertArrayHasKey('gross', $floatingIp['price_monthly']);

        // Test floating IP type pricing
        $floatingIpType = $pricing->floatingIpType();
        $this->assertArrayHasKey('ipv4', $floatingIpType);
        $this->assertArrayHasKey('ipv6', $floatingIpType);

        foreach (['ipv4', 'ipv6'] as $ipType) {
            $this->assertArrayHasKey('price_monthly', $floatingIpType[$ipType]);
            $this->assertArrayHasKey('net', $floatingIpType[$ipType]['price_monthly']);
            $this->assertArrayHasKey('gross', $floatingIpType[$ipType]['price_monthly']);
        }

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }

    /**
     * Test primary IP pricing details
     */
    public function test_primary_ip_pricing_details(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing();
        $pricing = $response->pricing();

        // Test primary IP pricing
        $primaryIp = $pricing->primaryIp();
        $this->assertArrayHasKey('price_monthly', $primaryIp);
        $this->assertArrayHasKey('net', $primaryIp['price_monthly']);
        $this->assertArrayHasKey('gross', $primaryIp['price_monthly']);

        // Test primary IP type pricing
        $primaryIpType = $pricing->primaryIpType();
        $this->assertArrayHasKey('ipv4', $primaryIpType);
        $this->assertArrayHasKey('ipv6', $primaryIpType);

        foreach (['ipv4', 'ipv6'] as $ipType) {
            $this->assertArrayHasKey('price_monthly', $primaryIpType[$ipType]);
            $this->assertArrayHasKey('net', $primaryIpType[$ipType]['price_monthly']);
            $this->assertArrayHasKey('gross', $primaryIpType[$ipType]['price_monthly']);
        }

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }

    /**
     * Test volume and traffic pricing details
     */
    public function test_volume_and_traffic_pricing_details(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing();
        $pricing = $response->pricing();

        // Test volume pricing
        $volume = $pricing->volume();
        $this->assertArrayHasKey('price_per_gb_month', $volume);
        $this->assertArrayHasKey('net', $volume['price_per_gb_month']);
        $this->assertArrayHasKey('gross', $volume['price_per_gb_month']);

        // Test traffic pricing
        $traffic = $pricing->traffic();
        $this->assertArrayHasKey('price_per_tb', $traffic);
        $this->assertArrayHasKey('net', $traffic['price_per_tb']);
        $this->assertArrayHasKey('gross', $traffic['price_per_tb']);

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }

    /**
     * Test image pricing details
     */
    public function test_image_pricing_details(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing();
        $pricing = $response->pricing();

        // Test image pricing
        $image = $pricing->image();
        $this->assertArrayHasKey('price_per_gb_month', $image);
        $this->assertArrayHasKey('net', $image['price_per_gb_month']);
        $this->assertArrayHasKey('gross', $image['price_per_gb_month']);

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }

    /**
     * Test error handling with API exceptions
     */
    public function test_can_handle_billing_api_exceptions(): void
    {
        $exception = new Exception('API Error: Billing service unavailable');
        $requests = [];
        $responses = [$exception];
        $client = $this->fakeClient($responses, $requests);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('API Error: Billing service unavailable');

        $client->billing()->listPricing();
    }

    /**
     * Test error response handling
     */
    public function test_can_handle_billing_error_responses(): void
    {
        $errorResponse = new Response(400, [], json_encode([
            'error' => [
                'code' => 'invalid_request',
                'message' => 'Invalid billing request parameters',
                'details' => [
                    'field' => 'currency',
                    'value' => 'INVALID',
                ],
            ],
        ]) ?: '');

        $requests = [];
        $responses = [$errorResponse];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing();

        $this->assertInstanceOf(
            ListPricingResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }

    /**
     * Test using individual billing fake resource
     */
    public function test_using_individual_billing_fake(): void
    {
        $requests = [];
        $responses = [];
        $billingFake = $this->fakeBilling($responses, $requests);

        // Test various billing operations
        $billingFake->listPricing();
        $billingFake->listPricing(['currency' => 'USD']);

        // Assert all requests were made
        $this->assertCount(2, $requests);

        // Test resource-specific assertions
        $billingFake->assertSent(function ($request) {
            return $request['method'] === 'list_pricing';
        });

        $billingFake->assertSent(function ($request) {
            return $request['method'] === 'list_pricing' &&
                   isset($request['parameters']['currency']) &&
                   $request['parameters']['currency'] === 'USD';
        });
    }

    /**
     * Test mixed response types for billing
     */
    public function test_can_handle_mixed_billing_response_types(): void
    {
        $successResponse = new Response(200, [], json_encode(['pricing' => []]) ?: '');
        $exception = new Exception('Network timeout');

        $requests = [];
        $responses = [$successResponse, $exception];
        $client = $this->fakeClient($responses, $requests);

        // First call succeeds
        $response1 = $client->billing()->listPricing();
        $this->assertInstanceOf(
            ListPricingResponse::class,
            $response1
        );

        // Verify first request was made
        $this->assertCount(1, $requests);

        // Second call throws exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Network timeout');
        $client->billing()->listPricing();
    }

    /**
     * Test billing with complex query parameters
     */
    public function test_can_list_pricing_with_complex_parameters(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing([
            'currency' => 'EUR',
            'include_tax' => true,
            'region' => 'eu-central',
            'format' => 'detailed',
        ]);

        $this->assertInstanceOf(
            ListPricingResponse::class,
            $response
        );

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing', function ($request) {
            return $request['parameters']['currency'] === 'EUR' &&
                   $request['parameters']['include_tax'] === true &&
                   $request['parameters']['region'] === 'eu-central' &&
                   $request['parameters']['format'] === 'detailed';
        });
    }

    /**
     * Test billing pricing calculation workflow
     */
    public function test_billing_pricing_calculation_workflow(): void
    {
        $requests = [];
        $responses = [];
        $client = $this->fakeClient($responses, $requests);

        // Get pricing information
        $response = $client->billing()->listPricing();
        $pricing = $response->pricing();

        // Simulate calculating costs for a server setup
        $serverTypes = $pricing->serverType();
        $volume = $pricing->volume();
        $traffic = $pricing->traffic();

        // Test that we can access pricing data for calculations
        $this->assertArrayHasKey('cx11', $serverTypes);
        $this->assertArrayHasKey('price_per_gb_month', $volume);
        $this->assertArrayHasKey('price_per_tb', $traffic);

        // Verify the request was made
        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }

    /**
     * Test billing resource assertions
     */
    public function test_billing_resource_assertions(): void
    {
        $requests = [];
        $responses = [];
        $billingFake = $this->fakeBilling($responses, $requests);

        // No requests made yet
        $billingFake->assertNotSent();

        // Make a request
        $billingFake->listPricing();

        // Now it should fail
        $this->expectException(AssertionFailedError::class);
        $billingFake->assertNotSent();
    }

    /**
     * Test billing with custom response data for specific pricing
     */
    public function test_billing_with_specific_pricing_data(): void
    {
        $customResponse = new Response(200, [], json_encode([
            'pricing' => [
                'currency' => 'EUR',
                'vat_rate' => '19.00',
                'server_type' => [
                    'cx11' => [
                        'price_monthly' => [
                            'net' => '2.9600000000',
                            'gross' => '3.5224000000',
                        ],
                    ],
                ],
                'volume' => [
                    'price_per_gb_month' => [
                        'net' => '0.0400000000',
                        'gross' => '0.0476000000',
                    ],
                ],
                'traffic' => [
                    'price_per_tb' => [
                        'net' => '1.0000000000',
                        'gross' => '1.1900000000',
                    ],
                ],
            ],
        ]) ?: '');

        $requests = [];
        $responses = [$customResponse];
        $client = $this->fakeClient($responses, $requests);

        $response = $client->billing()->listPricing();
        $pricing = $response->pricing();

        // Test specific pricing values
        $this->assertEquals('EUR', $pricing->currency());
        $this->assertEquals('19.00', $pricing->vatRate());

        $serverTypes = $pricing->serverType();
        $this->assertEquals('2.9600000000', $serverTypes['cx11']['price_monthly']['net']);
        $this->assertEquals('3.5224000000', $serverTypes['cx11']['price_monthly']['gross']);

        $volume = $pricing->volume();
        $this->assertEquals('0.0400000000', $volume['price_per_gb_month']['net']);
        $this->assertEquals('0.0476000000', $volume['price_per_gb_month']['gross']);

        $traffic = $pricing->traffic();
        $this->assertEquals('1.0000000000', $traffic['price_per_tb']['net']);
        $this->assertEquals('1.1900000000', $traffic['price_per_tb']['gross']);

        $this->assertRequestWasMade($requests, 'billing', 'list_pricing');
    }
}
