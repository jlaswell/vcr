<?php

namespace Jlaswell\VCR;

use Jlaswell\VCR\Cassette;

class VCR
{
    public static function insertCassette(
        string $name = '',
        string $mode = Cassette::MODE_AUTO,
        string $libraryPath = 'tests/fixtures/vcr',
    ): Cassette {
        return new Cassette($name, $mode, $libraryPath);
    }
}
