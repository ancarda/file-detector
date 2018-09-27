<?php

declare(strict_types=1);

namespace Test;

use \Ancarda\File\Detector;
use \PHPUnit\Framework\TestCase;

final class MimeTest extends TestCase
{
    private $detector;

    public function setUp()
    {
        $this->detector = new \Ancarda\File\Detector;
    }

    public function testText()
    {
        $file = $this->fileFromString('this is some text');
        $mime = $this->detector->determineMimeType($file);
        $this->assertEquals('text/plain; charset=utf-8', $mime);
    }

    public function testJpeg()
    {
        $this->assertFileHasMimeType('sample.jpg', 'image/jpg');
    }

    public function testPng()
    {
        $this->assertFileHasMimeType('sample.png', 'image/png');
    }

    public function testGif()
    {
        $this->assertFileHasMimeType('sample-87a.gif', 'image/gif');
        $this->assertFileHasMimeType('sample-89a.gif', 'image/gif');
    }

    public function testXml()
    {
        $this->assertFileHasMimeType('sample.xml', 'text/xml');
    }

    public function testWebp()
    {
        $this->assertFileHasMimeType('sample.webp', 'image/webp');
    }

    public function testFlac()
    {
        $this->assertFileHasMimeType('sample.flac', 'audio/flac');
    }

    public function testEmptyFile()
    {
        $this->detector->determineMimeType(new \SplTempFileObject());
    }

    public function testFromNonZeroCursorPosition()
    {
        $file = new \SplFileObject(__DIR__ . '/files/sample.jpg', 'r');
        $file->fseek(16, \SEEK_SET);

        $this->assertEquals('image/jpg', $this->detector->determineMimeType($file));
        $this->assertEquals(16, $file->ftell()); // Make sure cursor did not move.
    }

    public function testNonSeekable()
    {
        $file = $this->getMockBuilder(\SplFileObject::class)
            ->setConstructorArgs([__DIR__ . '/files/sample.jpg', 'r'])
            ->setMethods(['fseek'])
            ->getMock();
        $file->method('fseek')
            ->will($this->returnValue(-1));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not reset the cursor.');
        $this->detector->determineMimeType($file);
    }

    public function testFromNonZeroCursorAndNonSeekablePosition()
    {
        $file = new \SplFileObject(__DIR__ . '/files/sample.jpg', 'r');
        $file->fseek(16, \SEEK_SET);

        $file = $this->getMockBuilder(\SplFileObject::class)
            ->setConstructorArgs([__DIR__ . '/files/sample.jpg', 'r'])
            ->setMethods(['fseek', 'ftell'])
            ->getMock();
        $file->method('fseek')
            ->will($this->returnValue(-1));
        $file->method('ftell')
            ->will($this->returnValue(16)); // Report to not be at 0, will try to fseek to 0.

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not read specified bytes from the file.');
        $this->detector->determineMimeType($file);
    }

    private function assertFileHasMimeType(string $path, string $filetype)
    {
        $file = new \SplFileObject(__DIR__ . '/files/' . $path, 'r');
        $mime = $this->detector->determineMimeType($file);
        $this->assertEquals($filetype, $mime);
    }

    private function fileFromString(string $s)
    {
        $file = new \SplTempFileObject();
        $file->fwrite($s);
        $file->rewind();
        return $file;
    }
}
