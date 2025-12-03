<?php declare(strict_types=1);

namespace Careminate\Http\Requests;

use Careminate\Storage\Storage;

class UploadedFile
{
    protected ?string $name;
    protected ?string $ext;
    protected ?string $storedPath = null;
    protected array $file;

    public function __construct(array $file)
    {
        $this->file = $file;
        $info = pathinfo($file['name'] ?? '');
        $this->name = $info['filename'] ?? null;
        $this->ext  = isset($info['extension']) ? strtolower($info['extension']) : null;
    }

    public function getOriginalName(): string
    {
        return $this->file['name'] ?? '';
    }

    public function extension(): ?string
    {
        return $this->ext;
    }

    public function size(): int
    {
        return (int) ($this->file['size'] ?? 0);
    }

    public function tmp(): string
    {
        return $this->file['tmp_name'];
    }

    public function mime(): string
    {
        return (string) @mime_content_type($this->tmp());
    }

    /**
     * Resize image and replace tmp file. Uses GD.
     */
    public function resize(int $width, int $height): self
    {
        $tmp = $this->tmp();
        $info = @getimagesize($tmp);
        if (!$info) {
            throw new \Exception("File is not an image.");
        }

        [$origW, $origH] = $info;

        $src = match ($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($tmp),
            'image/png'  => imagecreatefrompng($tmp),
            'image/gif'  => imagecreatefromgif($tmp),
            default      => throw new \Exception("Unsupported image type: {$info['mime']}")
        };

        $dst = imagecreatetruecolor($width, $height);

        // Preserve transparency for PNG
        if ($info['mime'] === 'image/png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $origW, $origH);

        $newTmp = tempnam(sys_get_temp_dir(), 'caremi_img_') . ($this->ext ? '.' . $this->ext : '');

        match ($info['mime']) {
            'image/jpeg' => imagejpeg($dst, $newTmp, 90),
            'image/png'  => imagepng($dst, $newTmp),
            'image/gif'  => imagegif($dst, $newTmp),
        };

        imagedestroy($src);
        imagedestroy($dst);

        // replace tmp name (old tmp removed if needed)
        $this->file['tmp_name'] = $newTmp;

        return $this;
    }

    /**
     * Store file on a disk.
     * $path is relative to disk root.
     * Returns stored relative path (path/filename.ext).
     */
    public function store(string $path = '', ?string $filename = null, string $disk = 'public'): string
    {
        $diskObj = Storage::disk($disk);

        if (!$filename) {
            $filename = uniqid('file_', true) . ($this->ext ? '.' . $this->ext : '');
        }

        if (!str_contains($filename, '.') && $this->ext) {
            $filename .= '.' . $this->ext;
        }

        $relative = $diskObj->putFile($path, $filename, $this->tmp());

        // Save stored path for later url() / storePath()
        $this->storedPath = ltrim($relative, '/');

        return $this->storedPath;
    }

    /**
     * Returns stored relative path (e.g. "images/foo.png") or null.
     */
    public function storePath(): ?string
    {
        return $this->storedPath;
    }

    /**
     * Return public URL via disk. If storedPath isn't set, returns URL to original filename under path.
     */
    public function url(string $disk = 'public'): string
    {
        $relative = $this->storedPath ?? ($this->file['name'] ?? '');
        return Storage::disk($disk)->url($relative);
    }
}
