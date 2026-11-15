<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VisitorConnectionTest extends TestCase
{
    #[DataProvider('addresses')]
    public function test_only_public_addresses_are_returned(string $address, ?string $expected): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => $address])
            ->getJson('/visitor-connection');

        $response->assertOk()->assertExactJson(['ip' => $expected]);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public static function addresses(): array
    {
        return [
            'public IPv4' => ['8.8.8.8', '8.8.8.8'],
            'public IPv6' => ['2606:4700:4700::1111', '2606:4700:4700::1111'],
            'loopback' => ['127.0.0.1', null],
            'private IPv4' => ['192.168.1.1', null],
            'reserved IPv4' => ['192.0.2.1', null],
            'private IPv6' => ['fd00::1', null],
            'loopback IPv6' => ['::1', null],
            'invalid' => ['not-an-ip', null],
        ];
    }

    public function test_untrusted_forwarded_headers_are_not_used(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeader('X-Forwarded-For', '8.8.8.8')
            ->getJson('/visitor-connection')
            ->assertOk()->assertExactJson(['ip' => null]);
    }
}
