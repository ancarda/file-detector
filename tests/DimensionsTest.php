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

    public function testNonImageFile()
    {
        $file = new \SplFileObject(__DIR__ . '/files/sample.xml', 'r');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('@^Failed to get the image size. Given path is .+?\.$@');
        $this->detector->determineDimensions($file);
    }

    public function testNonexistingFilePath()
    {
        $this->markTestIncomplete('Nonexisting file path to getimagesize throws an Error.');
        $file = $this->getMockBuilder(\SplTempFileObject::class)
            ->setMethods(['getRealPath'])
            ->getMock();
        $file->method('getRealPath')
            ->will($this->returnValue(__DIR__ . '/files/nonexisting'));

        $this->detector->determineDimensions($file);
    }

    public function testNonImageBuffer()
    {
        $this->markTestIncomplete('Buffer to getimagesizefromstring throws an Error rather than returning false.');

        $file = new \SplTempFileObject();
        $file->fwrite('abc');
        $this->detector->determineDimensions($file);
    }

    public function testEmptyFile()
    {
        $this->markTestIncomplete('Buffer to getimagesizefromstring throws an Error on empty strings.');

        $this->detector->determineDimensions(new \SplTempFileObject());
    }

    public function testFromNonZeroCursorPosition()
    {
        $file = new \SplFileObject(__DIR__ . '/files/sample.jpg', 'r');
        $file->fread(512);

        $this->assertEquals([16, 16], $this->detector->determineDimensions($file));
    }

    public function testNonRewindable()
    {
        $file = $this->getMockBuilder(\SplTempFileObject::class)
            ->setMethods(['rewind'])
            ->getMock();
        $file->method('rewind')
            ->will($this->throwException(new \RuntimeException));
        $file->fwrite('a');

        $this->expectException(\RuntimeException::class);
        $this->detector->determineDimensions($file);
    }

    public function testNonSeekable()
    {
        $file = $this->getMockBuilder(\SplTempFileObject::class)
            ->setMethods(['fseek'])
            ->getMock();
        $file->method('fseek')
            ->will($this->returnValue(-1));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not reset the cursor.');
        $this->detector->determineDimensions($file);
    }
}
