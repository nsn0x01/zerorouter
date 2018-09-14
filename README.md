# PHP ZeroRouter

It's a <b>light and very fast router</b> able to mix different strategies and you can also implement your own. <br />
The router is based on <b>PSR-7</b> and we suggest use a fast implementation like nyholm/psr7 and nyholm/psr7-server.<br />
You can use this Router in your <b>Middleware</b> or also as standalone component.

The main target is to give a linear complexity to the problem and strategies like <b>PrefixBlockLookupStrategy</b> and <b>SuffixBlockLookupStrategy</b>
works well for reaching the target.

Below each strategy is explained.

<b>[ currently in review ]</b>

# HeaderKeyLookupStrategy

Skip all strategies and use the header key X-Routing-Key for matching directly your route and get best performance.
Could be useful with microservices and large dynamic maps.

Support only dynamic routes.

# PrefixBlockLookupStrategy

under costruction

# SuffixBlockLookupStrategy

under costruction

# StaticKeyLookupStrategy

Basic and best strategy for static routes.

/dummy<br>
/php<br>
/dave

# GenericBlockRegexLookupStrategy

This strategy try match only generic regular expression, a generic regex contains only dynamic routes without static part in the url.

# Install with composer

```sh
$ composer require nsn0x01/zerorouter
```

# basic usage:

```php
<?php
require 'vendor/autoload.php';

$router = new \Zeroframe\Zerorouter\Router(
    (new \Zeroframe\Zerorouter\RouterMap(
        function (\Zeroframe\Zerorouter\RouterMap $routerMap) {
            $routerMap->all('/');
            $routerMap->all('/{user}');
            $routerMap->all('/dummy/{bookmarks}');
            $routerMap->all('/{page}/category');
        },
        // enable cache only in production
        false,
        // cache file path
        ''
    // here you can add strategy, the order gives the priority and you are free to remove or add new strategies
    ))->addStrategy(new \Zeroframe\Zerorouter\Interfaces\HeaderKeyLookupStrategy())
    ->addStrategy(new \Zeroframe\Zerorouter\Interfaces\StaticKeyLookupStrategy())
    ->addStrategy(new \Zeroframe\Zerorouter\Interfaces\PrefixBlockLookupStrategy())
    ->addStrategy(new \Zeroframe\Zerorouter\Interfaces\SuffixBlockLookupStrategy())
    ->addStrategy(new \Zeroframe\Zerorouter\Interfaces\GenericBlockRegexLookupStrategy())
);

$psr17 = new \Nyholm\Psr7\Factory\Psr17Factory();
$request = (new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17,
    $psr17,
    $psr17,
    $psr17
))->fromGlobals();

var_dump($router->dispatch($request));
```