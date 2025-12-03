<?php declare(strict_types=1);

namespace Careminate\Storage;

class PublicDisk extends FileSystem
{
    /**
     * Public URL construction uses config url if present.
     */
    public function url(string $relativePath): string
    {
        // Try to read config to return configured URL base
        $config = @include base_path('config/filesystems.php');
        $publicUrl = $config['disks']['public']['url'] ?? null;

        if ($publicUrl) {
            return rtrim($publicUrl, '/') . '/' . ltrim($relativePath, '/');
        }

        return '/storage/' . ltrim($relativePath, '/');
    }
}
