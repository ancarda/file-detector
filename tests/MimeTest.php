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
