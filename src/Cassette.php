<?php

namespace Jlaswell\VCR;

use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use Jlaswell\VCR\Storage\FileStorage;
use Jlaswell\VCR\Storage\StorageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Cassette
{
    const MODE_AUTO = 'auto';
    const MODE_RECORD = 'record';
    const MODE_REPLAY = 'replay';

    private StorageInterface $storage;

    public function __construct(
        public string $name = '',
        private string $mode = self::MODE_RECORD,
        public string $libraryPath = 'tests/fixtures/vcr',
    ) {
        if ('' === $this->name) {
            $this->name = hash('xxh3', random_bytes(24));
        }

        // @todo There is no need to do ltrim twice.
        $this->libraryPath = rtrim(ltrim($libraryPath, '/.'), '/.');
        $this->storage = new FileStorage($this->libraryPath);
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
            return match ($this->mode) {
                self::MODE_RECORD => $this->buildRecordHandler($handler, $request, $options),
                self::MODE_REPLAY => $this->buildReplayHandler($handler, $request, $options),
                self::MODE_AUTO => $this->buildAutoHandler($handler, $request, $options),
                default => $handler($request, $options),
            };
        };
    }

    public function buildRecordHandler(callable $handler, RequestInterface $request, array $options): PromiseInterface
    {
        $name = $this->name;

        return $handler($request, $options)->then(
            function (ResponseInterface $response) use ($name, $request): ResponseInterface {
                $this->storage->save($name, $request, $response);
                return $response;
            }
        );
    }

    public function buildReplayHandler(callable $handler, RequestInterface $request, array $options): PromiseInterface
    {
        if ($this->storage->has($this->name, $request)) {
            return Create::promiseFor($this->storage->get($this->name, $request));
        }

        return $handler($request, $options);
    }

    public function buildAutoHandler(callable $handler, RequestInterface $request, array $options): PromiseInterface
    {
        if ($this->storage->has($this->name, $request)) {
            return $this->buildReplayHandler($handler, $request, $options);
        }

        return $this->buildRecordHandler($handler, $request, $options);
    }
}
