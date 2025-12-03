<?php declare(strict_types=1);

namespace Careminate\Storage;

class FileSystem implements DiskInterface
{
    protected string $root;

    public function __construct(string $root)
    {
        $this->root = rtrim($root, DIRECTORY_SEPARATOR);
    }

    /**
     * Put a file under the disk root. Returns the relative path (path/filename.ext)
     */
    public function putFile(string $path, string $filename, string $tmp): string
    {
        $path = trim($path ?? '', '/');
        $folder = $this->root . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');

        if (!is_dir($folder) && !@mkdir($folder, 0777, true) && !is_dir($folder)) {
            throw new \Exception("Unable to create directory: {$folder}");
        }

        $destination = $folder . DIRECTORY_SEPARATOR . $filename;

        // Normal secure move for uploaded files
        if (!@move_uploaded_file($tmp, $destination)) {
            // Fallback for temp files created by resize() (not uploaded via PHP native upload)
            if (!@copy($tmp, $destination)) {
                throw new \Exception("Failed to move/copy file to destination.");
            }
            // try to remove tmp if it's a temp file
            @unlink($tmp);
        }

        return ($path !== '' ? $path . '/' : '') . $filename;
    }

    /**
     * Basic url method for local disk. Public disk may override.
     */
    public function url(string $relativePath): string
    {
        return '/storage/' . ltrim($relativePath, '/');
    }
}
