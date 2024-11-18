<?php

declare(strict_types=1);

namespace Jlaswell\VCR\Tests;

use PHPUnit\Framework\TestCase;
use Jlaswell\VCR\Cassette;

class CassetteTest extends TestCase
{
    public function testName(): void
    {
        $this->assertEquals('example', (new Cassette('example'))->name);
        $this->assertEquals(16, strlen((new Cassette())->name));
    }

    public function testLibraryPath(): void
    {
        $this->assertEquals('tests/fixtures/vcr', (new Cassette())->libraryPath);
        $this->assertEquals('tests/fixtures/vcr', (new Cassette('example', Cassette::MODE_AUTO, '/tests/fixtures/vcr'))->libraryPath);
        $this->assertEquals('tests/fixtures/vcr', (new Cassette('example', Cassette::MODE_AUTO, '../tests/fixtures/vcr'))->libraryPath);
        $this->assertEquals('tests/fixtures/vcr', (new Cassette('example', Cassette::MODE_AUTO, '../tests/fixtures/vcr//'))->libraryPath);

        $cassette = new Cassette();
        $cassette->libraryPath = '/dev/null';
        $this->assertEquals('/dev/null', $cassette->libraryPath);
    }
}
