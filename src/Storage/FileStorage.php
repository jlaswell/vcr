<?php

namespace Jlaswell\VCR\Storage;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Psr7\Utils;
use Symfony\Component\Yaml\Exception\ParseException;

class FileStorage implements StorageInterface
{
    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = rtrim(ltrim($directory, '/.'), '/.');
    }

    public function hasCache(): bool
    {
        return is_dir($this->directory) && (count(glob($this->directory . '/*.yaml')) > 0);
    }

    public function has(string $key, RequestInterface $request): bool
    {
        return file_exists($this->getFilePath($key));
    }

    public function get(string $key, RequestInterface $request): ?ResponseInterface
    {
        try {
            $cassette = (array) Yaml::parsefile($this->getFilePath($key));
        } catch (ParseException $e) {
            return null;
        }

        foreach ($cassette['data'] as $case) {
            if ($case['id'] === hash('xxh3', Message::toString($request))) {
                return $this->decodeMessage($case['response']['encoding']);
            }
        }

        return null;
    }

    public function save(string $key, RequestInterface $request, ResponseInterface $response): void
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0664, true); // @codeCoverageIgnore
        }

        $data = [
            'id' => hash('xxh3', Message::toString($request)),
            'request' => array_merge(
                Message::parseMessage(Message::toString($request)),
                ['encoding' => $this->encodeMessage($request)],
            ),
            'response' => array_merge(
                Message::parseMessage(Message::toString($response)),
                ['status' => $response->getStatusCode()],
                ['encoding' => $this->encodeResponse($response)],
            ),
        ];

        file_put_contents($this->getFilePath($key), Yaml::dump(['name' => $key, 'data' => [$data]], 8, 2));
    }

    function encodeMessage(MessageInterface $message): string
    {
        $body = (string) $message->getBody();
        $headers = $message->getHeaders();
        $protocolVersion = $message->getProtocolVersion();

        $encodedMessage = [
            'body' => $body,
            'headers' => $headers,
            'protocolVersion' => $protocolVersion
        ];

        return base64_encode(json_encode($encodedMessage));
    }

    function encodeResponse(ResponseInterface $message): string
    {
        $body = (string) $message->getBody();
        $headers = $message->getHeaders();
        $protocolVersion = $message->getProtocolVersion();

        $encodedMessage = [
            'status' => $message->getStatusCode(),
            'body' => $body,
            'headers' => $headers,
            'protocolVersion' => $protocolVersion
        ];

        return base64_encode(json_encode($encodedMessage));
    }

    function decodeMessage(string $encodedMessage): MessageInterface
    {
        $decodedData = json_decode(base64_decode($encodedMessage), true);

        if (!$decodedData) {
            throw new \InvalidArgumentException('Invalid encoded message');
        }

        return new Response(
            $decodedData['status'],
            $decodedData['headers'],
            Utils::streamFor($decodedData['body']),
            $decodedData['protocolVersion'],
        );
    }

    private function getFilePath(string $key): string
    {
        return $this->directory . '/' . $key . '.yaml';
    }
}
