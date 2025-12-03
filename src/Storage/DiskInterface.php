<?php declare(strict_types=1);

namespace Careminate\Storage;

interface DiskInterface
{
    /**
     * Put an uploaded tmp file into disk under $path/$filename.
     * - $path is relative to disk root (no leading/trailing slash preferred).
     * - Returns the stored relative path: e.g. "images/abc123.png"
     */
    public function putFile(string $path, string $filename, string $tmp): string;

    /**
     * Return a publicly accessible URL for a stored relative path (same value returned by putFile()).
     */
    public function url(string $relativePath): string;
}
