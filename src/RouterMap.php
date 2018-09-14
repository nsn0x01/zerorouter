<?php

namespace Zeroframe\Zerorouter;

use Zeroframe\Zerorouter\Abstracts\LookupStrategyAbstract;

class RouterMap
{

    /** @var array */
    private $map = [];

    /** @var callable */
    private $routeConfig;

    /** @var LookupStrategyAbstract[] */
    private $strategies;

    /** @var bool */
    private $useCache;

    /** @var string */
    private $cacheFile;

    /** @var array */
    private $methods = [
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
    ];

    /**
     * Router constructor.
     *
     * @param bool                      $useCache
     * @param string                    $cacheFile
     * @param Callable                  $routeConfig
     * @param LookupStrategyAbstract[]  $strategies
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(callable $routeConfig, bool $useCache = false, string $cacheFile = '', array $strategies = [])
    {
        $this->useCache = $useCache;
        $this->cacheFile = $cacheFile;
        $this->routeConfig = $routeConfig;
        $this->strategies = $strategies;
    }

    /**
     * @param LookupStrategyAbstract $strategy
     *
     * @return $this
     */
    public function addStrategy(LookupStrategyAbstract $strategy): self
    {
        $this->strategies[] = $strategy;

        return $this;
    }

    /**
     * @return LookupStrategyAbstract[]
     */
    public function getStrategies(): array
    {
        return $this->strategies;
    }

    /**
     * @param string $route
     * @param array  $data
     *
     * @return bool
     */
    public function head(string $route, array $data = []): bool
    {
        return $this->addRoute('HEAD', $route, $data);
    }

    /**
     * @param string $route
     * @param array  $data
     *
     * @return bool
     */
    public function options(string $route, array $data = []): bool
    {
        return $this->addRoute('OPTIONS', $route, $data);
    }

    /**
     * @param string $route
     * @param array  $data
     *
     * @return bool
     */
    public function get(string $route, array $data = []): bool
    {
        return $this->addRoute('GET', $route, $data);
    }

    /**
     * @param string $route
     * @param array  $data
     *
     * @return bool
     */
    public function post(string $route, array $data = []): bool
    {
        return $this->addRoute('POST', $route, $data);
    }

    /**
     * @param string $route
     * @param array  $data
     *
     * @return bool
     */
    public function delete(string $route, array $data = []): bool
    {
        return $this->addRoute('DELETE', $route, $data);
    }

    /**
     * @param string $route
     * @param array  $data
     *
     * @return bool
     */
    public function patch(string $route, array $data = []): bool
    {
        return $this->addRoute('PATCH', $route, $data);
    }

    /**
     * @param string $route
     * @param array  $data
     *
     * @return bool
     */
    public function put(string $route, array $data = []): bool
    {
        return $this->addRoute('PUT', $route, $data);
    }

    /**
     * @param string $route
     * @param array  $data
     *
     * @return bool
     */
    public function all(string $route, array $data = []): bool
    {
        foreach ($this->methods as $method) {
            $this->$method($route, $data);
        }

        return true;
    }

    /**
     * @return array
     */
    public function getMap(): array
    {
        if ($this->useCache && file_exists($this->cacheFile)) {
            return require $this->cacheFile;
        }

        ($this->routeConfig)($this);

        foreach ($this->strategies as $strategy) {
            $this->map[\get_class($strategy)] = $strategy->getMap();
        }

        if ($this->useCache) {
            file_put_contents($this->cacheFile, '<?php return ' . var_export($this->map, true) . ';');
        }

        return $this->map;
    }

    /**
     * @param string $strategyClassName
     *
     * @return array
     */
    public function getMapByStrategy(string $strategyClassName): array
    {
        return $this->map[$strategyClassName] ?? [];
    }

    /**
     * @param string $httpMethod
     * @param string $route
     * @param array  $data
     *
     * @return bool
     */
    private function addRoute(string $httpMethod, string $route, array $data = []): bool
    {
        foreach ($this->strategies as $strategy) {
            $strategy->addRoute($httpMethod, $route, $data);
        }

        return true;
    }
}
