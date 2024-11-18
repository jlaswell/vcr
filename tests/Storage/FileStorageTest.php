<?php

declare(strict_types=1);

namespace Jlaswell\VCR\Tests\Storage;

use GuzzleHttp\Psr7\Request;
use Jlaswell\VCR\Storage\FileStorage;
use PHPUnit\Framework\TestCase;

class FileStorageTest extends TestCase
{
    public function testHasCache(): void
    {
        $vcrStorage = new FileStorage('tests/fixtures/vcr');
        $this->assertTrue($vcrStorage->hasCache());
        $this->assertFalse((new FileStorage('tests/fixtures/missing'))->hasCache());
    }

    public function testGet(): void
    {
        $this->assertNull((new FileStorage('tests/fixtures/vcr'))->get('missing', new Request('GET', '/')));
        $this->assertNull((new FileStorage('tests/fixtures/vcr'))->get('basic-example', new Request('GET', '/')));
    }
}
