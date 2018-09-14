<?php

namespace Zeroframe\Zerorouter\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface RouteDispatcherInterface
{

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function dispatch(ServerRequestInterface $request): array;
}
