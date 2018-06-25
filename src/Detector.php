<?php

declare(strict_types=1);

namespace Ancarda\File;

/**
 * Detector contains routines for working on partial streams.
 */
final class Detector
{
    /**
     * Makes an educated guess of the most appropriate MIME type for a
     * given stream.
     *
     * @param resource $fh Stream resource.
     * @throws \InvalidArgumentException Resource isn't a stream.
     * @return string MIME type or null if it can't be determined.
     */
    public function determineMimeType($fh): string
    {
        // $fh needs to be a resource.
        if (gettype($fh) !== 'resource') {
            throw new \InvalidArgumentException('Must be a resource of stream');
        }

        // Even though we asked for a resource, we need a stream,
        // specifically.
        if (get_resource_type($fh) !== 'stream') {
            // FIXME(ancarda): Replace with ResourceNotStreamEx.
            throw new \InvalidArgumentException('Must be stream.');
        }

        // FIXME(ancarda): Make a note of where the pointer was so it
        // can be reset back before the function returns.
        $initial = fread($fh, 8);

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
        $next8 = fread($fh, 8);
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
            if (empty($bstr[$i])) {
                throw new UnreadableStreamException('The stream in offset ' . $i . ' is not readable.');
            }

            $out[] = ord($bstr[$i]);
        }
        return $out;
    }
}
