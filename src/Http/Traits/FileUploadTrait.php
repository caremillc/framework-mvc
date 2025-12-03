<?php declare(strict_types=1);

namespace Careminate\Http\Traits;

use Careminate\Http\Requests\FileRequest;
use Careminate\Http\Requests\FileValidator;
use Careminate\Http\Requests\UploadedFile;

trait FileUploadTrait
{
    /**
     * Uploads a single file and returns an UploadedFile or null.
     *
     * Options:
     *  - disk: 'public'|'local'
     *  - path: relative path inside disk (eg. 'images/posts')
     *  - filename: optional filename (with or without extension)
     *  - resize: [width, height]
     *  - validate: rules array for FileValidator
     */
    public function uploadImage(string $key, array $options = []): ?UploadedFile
    {
        $file = FileRequest::file($key);
        if (!$file) return null;

        if (!empty($options['validate'])) {
            FileValidator::validate($file, $options['validate']);
        }

        if (!empty($options['resize']) && is_array($options['resize'])) {
            [$w, $h] = $options['resize'];
            $file->resize($w, $h);
        }

        $disk = $options['disk'] ?? 'public';
        $path = $options['path'] ?? 'uploads';
        $filename = $options['filename'] ?? null;

        $file->store($path, $filename, $disk);

        return $file;
    }
}
