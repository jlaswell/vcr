<?php

use PHPUnit\Framework\TestCase;
use Jlaswell\VCR\VCR;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class RecordingMiddlewareTest extends TestCase
{
    public function testBasicExample(): void
    {
        $recordCassette = VCR::insertCassette('basic-example', 'record');
        $stack = HandlerStack::create();
        $stack->push($recordCassette);

        $client = new Client(['handler' => $stack]);
        $response = $client->post('https://232c-68-50-161-203.ngrok-free.app', ['json' => ['foo' => 'bar']]);

        $this->assertEquals(202, $response->getStatusCode());

        $replayCassette = VCR::insertCassette('basic-example', 'replay');
        $stack = HandlerStack::create();
        $stack->push($replayCassette);

        $client = new Client(['handler' => $stack]);
        $response = $client->post('https://232c-68-50-161-203.ngrok-free.app', ['json' => ['foo' => 'bar']]);

        $this->assertEquals(202, $response->getStatusCode());
    }
}
