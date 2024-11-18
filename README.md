# VCR for Guzzle

A simple VCR-like HTTP recording and replay library for Guzzle.

## Overview

This library allows you to record HTTP interactions and replay them during tests. It was built so that I could stop copy and pasting mock requests in test cases.

## Installation

First, add the library to your project's dependencies by running:

```bash
composer require --dev jlaswell/vcr
```

Once the library is installed, you can start using it in your project. Here's a basic example of how to use it:

```php
<?php

namespace Jlaswell\Weather\Tests;

use Jlaswell\VCR\VCR;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Jlaswell\Weather\Client as WeatherClient;

class ClientTest extends TestCase
{
    // This will make a real request the first time and replay the request
    // on future calls.
    public function testCurrentWeather(): void
    {
        $cassette = VCR::insertCassette('weather-client');
        $stack = HandlerStack::create();
        $stack->push($cassette);

        $client = new Client(['handler' => $stack]);
        $weather = new WeatherClient($client)

        $conditions = $weather->forZipcode('20252');

        $this->assertEquals('Sunny', $conditions->simpleDescription);
        $this->assertEquals('Sunny with a 30% chance of rain', $conditions->longDescription);
    }

    public function testAndRecordEveryTime(): void
    {
        $cassette = VCR::insertCassette('weather-client', Cassette::MODE_RECORD);
        $stack = HandlerStack::create();
        $stack->push($cassette);

        // test logic
    }

    public function testAndOnlyReplayIfPresent(): void
    {
        $cassette = VCR::insertCassette('weather-client', Cassette::MODE_REPLAY);
        $stack = HandlerStack::create();
        $stack->push($cassette);

        // test logic
    }
}
```

For more advanced usage and configuration options, please refer to the [documentation](https://github.com/jlaswell/vcr).
