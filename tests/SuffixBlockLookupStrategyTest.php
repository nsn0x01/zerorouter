<?php
/**
 * Created by PhpStorm.
 * User: davidebernardo
 * Date: 13/09/18
 * Time: 15:04
 */

namespace Zeroframe\Zerorouter\tests;

use Zeroframe\Zerorouter\Lookups\SuffixBlockLookupStrategy;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class SuffixBlockLookupStrategyTest extends TestCase
{

    /** @var SuffixBlockLookupStrategy */
    private $strategy;

    public function setUp()
    {
        $this->strategy = new SuffixBlockLookupStrategy();
        $this->strategy->addRoute('GET', '/{value}/dummy/strategy');
    }

    public function testLookup()
    {
        $mockUri = $this->createMock(UriInterface::class);
        $mockUri->expects(self::any())
            ->method('getPath')
            ->willReturn('/zero/dummy/strategy');

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects(self::any())
            ->method('getUri')
            ->willReturn($mockUri);

        $mockRequest->expects(self::any())
            ->method('getMethod')
            ->willReturn('GET');

        $this->assertSame(
            [
                'status_code' => 200,
                'methods_allowed' => ['GET' => 0],
                'data' => [
                    'matches' =>
                        [
                            0 => '/zero/dummy/strategy',
                            'value' => 'zero',
                            1 => 'zero'
                        ],
                ],
                'matched_strategy' => SuffixBlockLookupStrategy::class
            ],
            $this->strategy->lookup($mockRequest)
        );
    }

    public function testMethodNotAllowed()
    {
        $mockUri = $this->createMock(UriInterface::class);
        $mockUri->expects(self::any())
            ->method('getPath')
            ->willReturn('/zero/dummy/strategy');

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects(self::any())
            ->method('getUri')
            ->willReturn($mockUri);

        $mockRequest->expects(self::any())
            ->method('getMethod')
            ->willReturn('PUT');

        $this->assertSame(
            [
                'status_code' => 405,
                'methods_allowed' => ['GET' => 0],
                'data' => [],
                'matched_strategy' => SuffixBlockLookupStrategy::class
            ],
            $this->strategy->lookup($mockRequest)
        );
    }
}
