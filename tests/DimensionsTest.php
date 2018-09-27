<?php

declare(strict_types=1);

namespace Test;

use \Ancarda\File\Detector;
use \PHPUnit\Framework\TestCase;

final class DimensionsTest extends TestCase
{
    private $detector;

    public function setUp()
    {
        $this->detector = new \Ancarda\File\Detector;
    }

    public function testSquarePNG()
    {
        $file = new \SplFileObject(__DIR__ . '/files/sample.png', 'r');

        $this->assertSame([16, 16], $this->detector->determineDimensions($file));
    }

    public function testRectangleJPG()
    {
        $file = new \SplFileObject(__DIR__ . '/files/sample2.jpg', 'r');

        $this->assertSame([3, 7], $this->detector->determineDimensions($file));
    }

    public function testNonImage()
    {
        $file = new \SplFileObject(__DIR__ . '/files/sample.xml', 'r');

        $this->expectException(\InvalidArgumentException::class);

        $this->detector->determineDimensions($file);
    }

    public function testFromNonZeroCursorPosition()
    {
        $file = new \SplFileObject(__DIR__ . '/files/sample.jpg', 'r');
        $file->fread(512);

        $this->assertEquals([16, 16], $this->detector->determineDimensions($file));
    }
}
