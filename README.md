# PHP ZeroRouter

It's a <b>light and very fast router</b> able to mix different strategies and you can also implement your own. <br />
The router is based on <b>PSR-7</b> and we suggest use a fast implementation like nyholm/psr7 and nyholm/psr7-server.<br />
You can use this Router in your <b>Middleware</b> or as standalone component.

The main target is to give a linear complexity to the problem and strategies like <b>PrefixBlockLookupStrategy</b> and <b>SuffixBlockLookupStrategy</b>
works well for reaching the target.

Below each strategy is explained.

# HeaderKeyLookupStrategy

Skip all strategies and use the header key X-Routing-Key for matching directly your route and get best performance.
Could be useful with microservices and large dynamic maps.

Support only dynamic routes.

# PrefixBlockLookupStrategy

This strategy splits the url by blocks and each block is delimited by a '/'.
We create a map only with static parts before the dynamic route.
 
Example:

Route: /comments/sport/{id}

map: [
        'comments' => 
            [
                'sport' =>
                    [
                        'regsex' => [...]
                    ] 
            ]
    ]

The strategy will try lookup each block directly in array map, if a block it's not found then we try check 
on the last block found if exists a node "regsex" and then we try to match the regular expressions.
With this strategy you can have large dynamic maps without having bad performance because the access to the array is very fast
and at every access you are limiting the data to few regular expressions. 

Following the previous example with this URL: /comments/sport/8041984 PrefixBlockLookupStrategy will do these steps:

* accessing to array using 'comments' key [ OK ]
* accessing to array using 'sport' key [ OK ]
* accessing to array using '8041984' key [ FAILS ]
* trying to match the regular expressions on last block found, in this case our block is 'sport'.

handles URLS where the static part is before the dynamic route:

/users/{id}
/users/comments/{id}
/profiles/{activity}/id

# SuffixBlockLookupStrategy

Apply same strategy used by PrefixBlockLookupStrategy but in reverse order working only on suffixes.

handles URLS where the static part is after the dynamic route:

/{id}/users
/{id}/comments/users
/{id}/{activity}/profiles

# StaticKeyLookupStrategy

Basic and best strategy for static routes.

/dummy<br>
/php<br>
/dave

# GenericBlockRegexLookupStrategy

This strategy tries to match only generic regular expression, a generic regex contains only dynamic routes without static part in the url.

# Install with composer

```sh
$ composer require zeroframe/zerorouter
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
    ))->addStrategy(new \Zeroframe\Zerorouter\Lookups\HeaderKeyLookupStrategy())
    ->addStrategy(new \Zeroframe\Zerorouter\Lookups\StaticKeyLookupStrategy())
    ->addStrategy(new \Zeroframe\Zerorouter\Lookups\PrefixBlockLookupStrategy())
    ->addStrategy(new \Zeroframe\Zerorouter\Lookups\SuffixBlockLookupStrategy())
    ->addStrategy(new \Zeroframe\Zerorouter\Lookups\GenericBlockRegexLookupStrategy())
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