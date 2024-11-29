<?php

declare(strict_types=1);

namespace Jlaswell\VCR\Tests\Storage;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Jlaswell\VCR\Storage\FileStorage;
use PHPUnit\Framework\TestCase;

class FileStorageTest extends TestCase
{
    public function testHasCache(): void
    {
        $storage = new FileStorage('tests/fixtures/vcr');
        $this->assertTrue($storage->hasCache());
        $this->assertFalse((new FileStorage('tests/fixtures/missing'))->hasCache());
    }

    public function testGet(): void
    {
        $this->assertNull((new FileStorage('tests/fixtures/vcr'))->get('missing', new Request('GET', '/')));
        $this->assertNull((new FileStorage('tests/fixtures/vcr'))->get('basic-example', new Request('GET', '/')));
    }

    public function testSaveMultipleRequestsToSameFile(): void
    {
        $storage = new FileStorage('tests/fixtures/vcr');

        $storage->save('multiple-requests', new Request('GET', '/test'), new Response());
        $storage->save('multiple-requests', new Request('PUT', '/test'), new Response(202));

        $cassette = $storage->getAll('multiple-requests');

        foreach ($cassette['data'] as $case) {
            match ($case['id']) {
                'ebe2836810a36d37' => $this->assertEquals(200, $case['response']['status']),
                '82b3cfe788b5fd3e' => $this->assertEquals(202, $case['response']['status']),
                default => $this->fail('No matching request found'),
            };
        }
    }
}
