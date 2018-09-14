<?php

namespace Zeroframe\Zerorouter\Lookups;

use Psr\Http\Message\ServerRequestInterface;
use Zeroframe\Zerorouter\Abstracts\LookupStrategyAbstract;

class HeaderKeyLookupStrategy extends LookupStrategyAbstract
{
    public const HTTP_ROUTING_KEY_HEADER = 'X-Routing-Key';

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function lookup(ServerRequestInterface $request): array
    {
        $routingKey = $request->getHeader(self::HTTP_ROUTING_KEY_HEADER);

        if (!$routingKey || !isset($this->map[$routingKey[0]])) {
            return $this->responseNotFound();
        }

        $data = $this->map[$routingKey[0]];
        $path = strtolower($request->getUri()->getPath());
        $method = $request->getMethod();

        if (!isset($data['methods'][$method])) {
            return $this->responseMethodNotAllowed($data['methods']);
        }

        if (!preg_match($data['route'], $path, $matches)) {
            return $this->responseNotFound();
        }

        $data['methods'][$method]['matches'] = $matches;

        return $this->responseFound(
            $data['methods'],
            $data['methods'][$method]
        );
    }

    /**
     * @param string $httpMethod
     * @param string $route
     * @param array  $data
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function addRoute(string $httpMethod, string $route, array $data = []): bool
    {
        $routingKey = $data[self::HTTP_ROUTING_KEY_HEADER] ?? null;

        // use static strategy for routes not dynamic
        if (null === $routingKey || !$this->isRegexRoute($route)) {
            return false;
        }

        if (isset($this->map[$routingKey])) {
            throw new \InvalidArgumentException("X-Routing-Key $routingKey already used.");
        }

        $this->map[$routingKey] = [];
        $this->map[$routingKey]['route'] = $this->rewriteRoute($route);
        $this->map[$routingKey]['methods'] = $this->map[$routingKey]['methods'] ?? [];
        $this->map[$routingKey]['methods'][$httpMethod] = $data;

        return true;
    }
}
