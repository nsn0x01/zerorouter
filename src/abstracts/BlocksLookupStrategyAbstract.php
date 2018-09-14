<?php

namespace Zeroframe\Zerorouter\Abstracts;

abstract class BlocksLookupStrategyAbstract extends LookupStrategyAbstract
{
    protected const URL_BLOCKS_DELIMITER = '/';

    /**
     * @param string $httpMethod
     * @param string $routeBlock
     * @param array  $blocks
     * @param array  $data
     *
     * @return bool
     */
    protected function addRouteBlock(string $httpMethod, string $routeBlock, array $blocks = [], array $data = []): bool
    {
        if (!$blocks || !$this->isRegexRoute($routeBlock)) {
            return false;
        }

        return $this->buildMapAndSetData(
            $httpMethod,
            $routeBlock,
            $blocks,
            $data
        );
    }

    /**
     * @param array  $regsex
     * @param string $path
     * @param string $httpMethod
     *
     * @return array
     */
    protected function matchRegsex(array $regsex, string $path, string $httpMethod): array
    {
        foreach ($regsex as $regex => $data) {
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }

            if (!isset($data['methods'][$httpMethod])) {
                return $this->responseMethodNotAllowed($data['methods']);
            }

            $data['methods'][$httpMethod]['matches'] = $matches;

            return $this->responseFound(
                $data['methods'],
                $data['methods'][$httpMethod]
            );
        }

        return $this->responseNotFound();
    }

    /**
     * @param array $map
     * @param array $blocks
     * @param int   $maxLen
     * @param int   $index
     *
     * @return array|null
     */
    protected function lookupByBlocks(array $map, array $blocks, int $maxLen, int $index = 0): array
    {
        if ($index >= $maxLen) {
            return $map['regsex'] ?? [];
        }

        $block = $blocks[$index];

        if (!isset($map[$block])) {
            return $map['regsex'] ?? [];
        }

        return $this->lookupByBlocks($map[$block], $blocks, $maxLen, $index + 1);
    }

    /**
     * @param array $blocks
     *
     * @return array
     */
    protected function getBlocksBeforeDynamicRoute(array $blocks): array
    {
        $blocksBeforeFirstRegexRoute = [];

        foreach ($blocks as $block) {
            if ($this->isRegexRoute($block)) {
                return $blocksBeforeFirstRegexRoute;
            }
            $blocksBeforeFirstRegexRoute[] = $block;
        }

        return $blocksBeforeFirstRegexRoute;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function getPrefixes(string $path): array
    {
        return $this->getBlocks($path, self::URL_BLOCKS_DELIMITER);
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function getSuffixes(string $path): array
    {
        return array_reverse($this->getPrefixes($path));
    }

    /**
     * here we consider as generic regex a route without static prefixes and suffixes.
     * In other words every block must be a regular expression.
     *
     * @param string $route
     *
     * @return bool
     */
    protected function isGenericRegex(string $route): bool
    {
        foreach ($blocks = $this->getBlocks($route, self::URL_BLOCKS_DELIMITER) as $block) {
            if (!$this->isRegexRoute($block)) {
                return false;
            }
        }

        return $blocks ? true : false;
    }

    /**
     * @param string $httpMethod
     * @param string $route
     * @param array  $blocks
     * @param array  $data
     *
     * @return bool
     */
    protected function buildMapAndSetData(string $httpMethod, string $route, array $blocks, array $data): bool
    {
        $map = &$this->map;

        foreach ($blocks as $block) {
            $map[$block] = $map[$block] ?? [];
            $map = &$map[$block];
        }

        $route = $this->rewriteRoute($route);

        //appending data to last block
        $map['regsex'] = $map['regsex'] ?? [];
        $map['regsex'][$route] = $map['regsex'][$route] ?? [];
        $map['regsex'][$route]['methods'] = $map['regsex'][$route]['methods'] ?? [];
        $map['regsex'][$route]['methods'][$httpMethod] = $data;

        return true;
    }

    /**
     * @param string $path
     * @param string $delimiter
     *
     * @return array
     */
    private function getBlocks(string $path, $delimiter): array
    {
        return explode($delimiter, trim($path, $delimiter));
    }
}
