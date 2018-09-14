<?php
/**
 * Created by PhpStorm.
 * User: davidebernardo
 * Date: 13/09/18
 * Time: 15:04
 */

namespace Zeroframe\Zerorouter\tests;

use Zeroframe\Zerorouter\Lookups\HeaderKeyLookupStrategy;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class HeaderKeyLookupStrategyTest extends TestCase
{

    /** @var HeaderKeyLookupStrategy */
    private $strategy;

    public function setUp()
    {
        $this->strategy = new HeaderKeyLookupStrategy();
        $this->strategy->addRoute(
            'GET',
            '/{data:[a-zA-Z0-9+=\x2F]*}-{token:[a-z0-9]*}',
            ['X-Routing-Key' => 'check-token']
        );
    }

    public function testLookup()
    {
        $mockUri = $this->createMock(UriInterface::class);
        $mockUri->expects(self::any())
            ->method('getPath')
            ->willReturn('/sammy/data/test-tokentest');

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects(self::any())
            ->method('getUri')
            ->willReturn($mockUri);

        $mockRequest->expects(self::any())
            ->method('getHeader')
            ->with('X-Routing-Key')
            ->willReturn(['check-token']);

        $mockRequest->expects(self::any())
            ->method('getMethod')
            ->willReturn('GET');

        $this->assertSame(
            [
                'status_code' => 200,
                'methods_allowed' => ['GET' => 0],
                'data' => [
                    'X-Routing-Key' => 'check-token',
                    'matches' =>
                        [
                            0 => '/sammy/data/test-tokentest',
                            'data' => 'sammy/data/test',
                            1 => 'sammy/data/test',
                            'token' => 'tokentest',
                            2 => 'tokentest'
                        ],
                ],
                'matched_strategy' => HeaderKeyLookupStrategy::class
            ],
            $this->strategy->lookup($mockRequest)
        );
    }

    public function testMethodNotAllowed()
    {
        $mockUri = $this->createMock(UriInterface::class);
        $mockUri->expects(self::any())
            ->method('getPath')
            ->willReturn('/');

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects(self::any())
            ->method('getUri')
            ->willReturn($mockUri);

        $mockRequest->expects(self::any())
            ->method('getHeader')
            ->with('X-Routing-Key')
            ->willReturn(['check-token']);

        $mockRequest->expects(self::any())
            ->method('getMethod')
            ->willReturn('PUT');

        $this->assertSame(
            [
                'status_code' => 405,
                'methods_allowed' => ['GET' => 0],
                'data' => [],
                'matched_strategy' => HeaderKeyLookupStrategy::class
            ],
            $this->strategy->lookup($mockRequest)
        );
    }
}
