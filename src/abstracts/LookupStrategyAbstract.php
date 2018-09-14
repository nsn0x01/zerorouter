<?php

namespace Zeroframe\Zerorouter\Abstracts;

use Zeroframe\Zerorouter\Interfaces\RouteLookupInterface;

abstract class LookupStrategyAbstract implements RouteLookupInterface
{

    /** @var array */
    protected $map = [];

    public const HTTP_FOUND              = 200;
    public const HTTP_NOT_FOUND          = 404;
    public const HTTP_METHOD_NOT_ALLOWED = 405;

    /**
     * parse dynamic rules (ty Fastroute)
     */
    private const VARIABLE_REGEX = <<<'REGEX'
\{
    \s* ([a-zA-Z_][a-zA-Z0-9_-]*) \s*
    (?:
        : \s* ([^{}]*(?:\{(?-1)\}[^{}]*)*)
    )?
\}
REGEX;

    /**
     * @param string $httpMethod
     * @param string $route
     * @param array  $data
     *
     * @return bool
     */
    abstract public function addRoute(string $httpMethod, string $route, array $data = []): bool;

    /**
     * @return array
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * Inject map.
     *
     * @param array $map
     */
    public function setMap(array $map)
    {
        $this->map = $map;
    }

    /**
     * @param string $route
     *
     * @return bool
     */
    protected function isStaticRoute(string $route): bool
    {
        return !$this->isRegexRoute($route);
    }

    /**
     * @param string $route
     *
     * @return bool
     */
    protected function isRegexRoute(string $route): bool
    {
        return preg_match_all(
            '~' . self::VARIABLE_REGEX . '~x',
            $route,
            $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        ) ? true : false;
    }

    /**
     * @param string $route
     *
     * @return string
     */
    protected function rewriteRoute(string $route): string
    {
        return '/^' .
            preg_replace(
                ['/\//', '/\{([a-z]+)\}/', '/\{([a-z]+):([^\}]+)\}/'],
                ['\\/', '(?P<\1>[\w]+)', '(?P<\1>\2)'],
                $route
            )
            . '$/i';
    }

    /**
     * @param array $methods
     * @param array $handlerData
     *
     * @return array
     */
    protected function responseFound(array $methods = [], array $handlerData = []): array
    {
        return $this->lookupResponse(
            self::HTTP_FOUND,
            $methods,
            $handlerData
        );
    }

    /**
     * @return array
     */
    protected function responseNotFound(): array
    {
        return $this->lookupResponse(self::HTTP_NOT_FOUND);
    }

    /**
     * @param array $methodsAllowed
     *
     * @return array
     */
    protected function responseMethodNotAllowed(array $methodsAllowed): array
    {
        return $this->lookupResponse(
            self::HTTP_METHOD_NOT_ALLOWED,
            $methodsAllowed
        );
    }

    /**
     * @param int   $statusCode
     * @param array $methods
     * @param array $handlerData
     *
     * @return array
     */
    private function lookupResponse(int $statusCode, array $methods = [], array $handlerData = []): array
    {
        return [
            'status_code' => $statusCode,
            'methods_allowed' => array_flip(array_keys($methods)),
            'data' => $handlerData,
            'matched_strategy' => \get_class($this)
        ];
    }
}
