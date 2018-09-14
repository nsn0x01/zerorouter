<?php

namespace Zeroframe\Zerorouter\Lookups;

use Zeroframe\Zerorouter\Abstracts\BlocksLookupStrategyAbstract;
use Psr\Http\Message\ServerRequestInterface;

class PrefixBlockLookupStrategy extends BlocksLookupStrategyAbstract
{

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function lookup(ServerRequestInterface $request): array
    {
        $path = strtolower($request->getUri()->getPath());
        $prefixes = $this->getPrefixes($path);

        return $this->matchRegsex(
            $this->lookupByBlocks($this->map, $prefixes, \count($prefixes)),
            $path,
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
        return $this->addRouteBlock(
            $httpMethod,
            $route,
            $this->getBlocksBeforeDynamicRoute($this->getPrefixes(strtolower($route))),
            $data
        );
    }
}
