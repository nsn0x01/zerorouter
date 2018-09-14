<?php

namespace Zeroframe\Zerorouter;

use Zeroframe\Zerorouter\Abstracts\LookupStrategyAbstract;
use Zeroframe\Zerorouter\Interfaces\RouteDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router implements RouteDispatcherInterface
{

    /** @var RouterMap */
    private $routerMap;

    /**
     * Router constructor.
     *
     * @param RouterMap $routerMap
     */
    public function __construct(RouterMap $routerMap)
    {
        $this->routerMap = $routerMap;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function dispatch(ServerRequestInterface $request): array
    {
        $lookup = [];
        $map = $this->routerMap->getMap();

        foreach ($this->routerMap->getStrategies() as $strategy) {
            $strategy->setMap($map[\get_class($strategy)]);
            $lookup = $strategy->lookup($request);
            $statusCode = $lookup['status_code'];

            // trying next strategy
            if ($statusCode === LookupStrategyAbstract::HTTP_NOT_FOUND) {
                continue;
            }

            return $lookup;
        }

        return $lookup;
    }
}
