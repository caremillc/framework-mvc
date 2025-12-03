<?php declare(strict_types=1);

namespace Careminate\Storage;

class Storage
{
    /**
     * Return a DiskInterface implementation.
     * Reads paths from config/filesystems.php if available.
     */
    public static function disk(string $name): DiskInterface
    {
        $config = @include base_path('config/filesystems.php');
// dd($config);
        $disks = $config['disks'] ?? [];

        if (!isset($disks[$name])) {
            // Fallback to sensible defaults
            return match ($name) {
                'public' => new PublicDisk(storage_path('app/public')),
                'local'  => new FileSystem(storage_path('app')),
                default  => throw new \Exception("Disk '{$name}' not found."),
            };
        }

        $root = $disks[$name]['root'] ?? ($name === 'public' ? storage_path('app/public') : storage_path('app'));
        $root = is_callable($root) ? $root() : $root;

        return match ($name) {
            'public' => new PublicDisk($root),
            default => new FileSystem($root),
        };
    }
}
