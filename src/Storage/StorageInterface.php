<?php

namespace Jlaswell\VCR\Storage;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface StorageInterface
{
    public function hasCache(): bool;
    public function has(string $key, RequestInterface $request): bool;
    public function get(string $key, RequestInterface $request): ?ResponseInterface;
    public function getAll(string $key): ?array;
    public function save(string $key, RequestInterface $request, ResponseInterface $response): void;
}
