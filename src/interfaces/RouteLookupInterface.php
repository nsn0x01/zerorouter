<?php

namespace Zeroframe\Zerorouter\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface RouteLookupInterface
{

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function lookup(ServerRequestInterface $request): array;
}
