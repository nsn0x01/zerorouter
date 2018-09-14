<?php

namespace Zeroframe\Zerorouter\Lookups;

use Zeroframe\Zerorouter\Abstracts\BlocksLookupStrategyAbstract;
use Psr\Http\Message\ServerRequestInterface;

class GenericBlockRegexLookupStrategy extends BlocksLookupStrategyAbstract
{

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function lookup(ServerRequestInterface $request): array
    {
        return $this->matchRegsex(
            $this->map['regsex'] ?? [],
            strtolower($request->getUri()->getPath()),
            $request->getMethod()
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
        if (!$this->isGenericRegex($route)) {
            return false;
        }

        return $this->buildMapAndSetData(
            $httpMethod,
            $route,
            [],
            $data
        );
    }
}
