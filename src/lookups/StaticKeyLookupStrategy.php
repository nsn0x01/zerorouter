<?php

namespace Zeroframe\Zerorouter\Lookups;

use Psr\Http\Message\ServerRequestInterface;
use Zeroframe\Zerorouter\Abstracts\LookupStrategyAbstract;

class StaticKeyLookupStrategy extends LookupStrategyAbstract
{

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function lookup(ServerRequestInterface $request): array
    {
        $path = strtolower($request->getUri()->getPath());

        if (!isset($this->map[$path])) {
            return $this->responseNotFound();
        }

        $data = $this->map[$path];
        $method = $request->getMethod();

        if (!isset($data['methods'][$method])) {
            return $this->responseMethodNotAllowed($data['methods']);
        }

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
     * @return bool
     */
    public function addRoute(string $httpMethod, string $route, array $data = []): bool
    {
        if (!$this->isStaticRoute($route)) {
            return false;
        }

        $route = strtolower($route);
        $this->map[$route] = $this->map[$route] ?? [];
        $this->map[$route]['methods'] = $this->map[$route]['methods'] ?? [];
        $this->map[$route]['methods'][$httpMethod] = $data;

        return true;
    }
}
