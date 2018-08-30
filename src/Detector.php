<?php

declare(strict_types=1);

namespace Ancarda\File;

/**
 * Detector contains routines for working with file objects.
 */
final class Detector
{
    /**
     * Makes an educated guess of the most appropriate MIME type for a
     * given file object.
     *
     * @param \SplFileObject $file File object.
     * @return string MIME type or null if it can't be determined.
     */
    public function determineMimeType(\SplFileObject $file): string
    {
        /** @var string $initial The first 8 bytes of the file. */
        $initial = $this->read($file, 0, 8);

        // Simple types that have their magic bytes at offset 0
        $grimoire = [
            'application/postscript' => [[37, 33, 80, 83]],

            'audio/flac' => [[102, 76, 97, 67]],

            'image/gif' => [
                [71, 73, 70, 56, 57, 97],
                [71, 73, 70, 56, 55, 97],
            ],
            'image/jpg' => [[255, 216, 255]],
            'image/png' => [[137, 80, 78, 71, 13, 10, 26, 10]],

            'text/xml' => [[60, 63, 120, 109, 108, 32]],
        ];

        foreach ($grimoire as $contentType => $magic) {
            foreach ($magic as $match) {
                if ($this->take($initial, count($match)) === $match) {
                    return $contentType;
                }
            }
        }

        // More complicated types have non-zero offset for their magic bytes
        // or variable bytes to detect (examples: tar, avi, wav)
        $next8 = $this->read($file, 8, 16);
        if ($this->take($next8, 4) === [87, 69, 66, 80]) {
            return 'image/webp';
        }

        // We're going to have to guess if it's binary or not.
        $binary = array_merge(range(0, 8), range(11, 12), range(14, 31), [0x7F]);
        foreach ($binary as $b) {
            if (strpos($initial, $b) !== false) {
                return 'application/octet-stream';
            }
        }

        // If it's not binary, and not a detected type, let's treat it as text.
        return 'text/plain; charset=utf-8';
    }

    /**
     * Read width and height from a image file.
     *
     * @param \SplFileObject $file File object
     * @return array [$width, $height]
     * @throws \InvalidArgumentException Unreadable SplFileInfo
     * @throws \RuntimeException SplFileObject $file object is failed to rewind
     */
    public function determineDimensions(\SplFileObject $file): array
    {
        if (false !== $path = $file->getRealPath()) {
            $info = getimagesize($path);

            if (false === $info) {
                throw new \InvalidArgumentException('Failed to get the image size. Given path is ' . $path . '.');
            }

            return [$info[0], $info[1]];
        }

        $file->rewind();
        $buffer = '';
        while (false === $file->eof()) {
            $buffer .= $file->fread(512);
        }

        $info = getimagesizefromstring($buffer);

        if (false === $info) {
            throw new \InvalidArgumentException('Failed to get the image size from string.');
        }

        return [$info[0], $info[1]];
    }

    /**
     * Read bytes from a file, leaving the object unchanged.
     *
     * @param \SplFileObject $file File object to read.
     * @param int $start Byte offset to start reading from.
     * @param int $length Number of bytes to read.
     * @throws \Exception When seeking or reading failed.
     * @throws \Exception When the cursor could not be reset.
     * @return string Extracted bytes.
     */
    private function read(\SplFileObject $file, int $start, int $length): string
    {
        $position = $file->ftell();
        $move = $file->fseek($start, \SEEK_SET);
        $read = $file->fread($length);
        // Reading failed, or the reading start position was uncertain.
        if ($read === false || $move === -1 && $position !== $start) {
            throw new \Exception('Could not read specified bytes from the file.');
        }
        // The original position is unknown or the position could not be set to it.
        if ($position === false || $file->fseek($position, \SEEK_SET) === -1) {
            throw new \Exception('Could not reset the cursor.');
        }
        return $read;
    }

    /**
     * Extracts part of a string as an array of code points.
     *
     * @param string $bstr Byte string.
     * @param int $len Length of bytes to read.
     * @return array Numeric offsets.
     */
    private function take(string $bstr, int $len): array
    {
        $out = [];
        for ($i = 0; $i < $len; $i++) {
            $out[] = ord($bstr[$i]);
        }
        return $out;
    }
}
