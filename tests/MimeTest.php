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
        $stream = $this->streamFromString('this is some text');
        $mime = $this->detector->determineMimeType($stream);
        $this->assertEquals('text/plain; charset=utf-8', $mime);
        fclose($stream);
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

    private function assertFileHasMimeType(string $path, string $filetype)
    {
        $stream = fopen(__DIR__ . '/files/' . $path, 'r');
        $mime = $this->detector->determineMimeType($stream);
        $this->assertEquals($filetype, $mime);
        fclose($stream);
    }

    private function streamFromString(string $s)
    {
        $stream = fopen('php://memory', 'rw');
        fwrite($stream, $s);
        rewind($stream);
        return $stream;
    }
}
