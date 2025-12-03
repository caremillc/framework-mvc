<?php declare(strict_types=1);

namespace Careminate\Http\Requests;

class FileValidator
{
    /**
     * Rules:
     *  - max => KB integer
     *  - mimes => ['image/jpeg', 'image/png']
     *  - extensions => ['jpg','png']
     *  - dimensions => ['min_width' => int, 'min_height' => int]
     */
    public static function validate(UploadedFile $file, array $rules): void
    {
        if (isset($rules['max'])) {
            $maxBytes = (int)$rules['max'] * 1024;
            if ($file->size() > $maxBytes) {
                throw new \Exception("File is too large. Max allowed: {$rules['max']} KB");
            }
        }

        if (isset($rules['mimes'])) {
            $mime = $file->mime();
            if (!in_array($mime, (array) $rules['mimes'])) {
                throw new \Exception("Invalid MIME type: {$mime}");
            }
        }

        if (isset($rules['extensions'])) {
            $ext = strtolower((string)$file->extension());
            if (!in_array($ext, (array) $rules['extensions'])) {
                throw new \Exception("Invalid file extension: {$ext}");
            }
        }

        if (isset($rules['dimensions'])) {
            $info = @getimagesize($file->tmp());
            if (!$info) {
                throw new \Exception("File is not an image.");
            }

            [$w, $h] = $info;
            $minW = $rules['dimensions']['min_width'] ?? 0;
            $minH = $rules['dimensions']['min_height'] ?? 0;

            if ($w < $minW || $h < $minH) {
                throw new \Exception("Image dimensions too small. Minimum: {$minW}x{$minH}");
            }
        }
    }
}
