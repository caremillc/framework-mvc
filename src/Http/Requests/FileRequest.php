<?php declare(strict_types=1);

namespace Careminate\Http\Requests;

class FileRequest
{
    /**
     * Return a single UploadedFile or null.
     */
    public static function file(string $key): ?UploadedFile
    {
        if (!isset($_FILES[$key])) {
            return null;
        }

        // Handle standard single file
        if (!is_array($_FILES[$key]['name'])) {
            if ($_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
                return null;
            }

            return new UploadedFile([
                'name'     => $_FILES[$key]['name'],
                'type'     => $_FILES[$key]['type'],
                'tmp_name' => $_FILES[$key]['tmp_name'],
                'error'    => $_FILES[$key]['error'],
                'size'     => $_FILES[$key]['size'],
            ]);
        }

        // If multiple under same key and developer called file(), return first valid
        foreach ($_FILES[$key]['name'] as $i => $name) {
            if ($_FILES[$key]['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            return new UploadedFile([
                'name'     => $_FILES[$key]['name'][$i],
                'type'     => $_FILES[$key]['type'][$i],
                'tmp_name' => $_FILES[$key]['tmp_name'][$i],
                'error'    => $_FILES[$key]['error'][$i],
                'size'     => $_FILES[$key]['size'][$i],
            ]);
        }

        return null;
    }

    /**
     * Return array of UploadedFile objects for inputs like name="photos[]"
     */
    public static function files(string $key): array
    {
        if (!isset($_FILES[$key])) {
            return [];
        }

        // Single file normalized to array
        if (!is_array($_FILES[$key]['name'])) {
            if ($_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
                return [];
            }

            return [
                new UploadedFile([
                    'name'     => $_FILES[$key]['name'],
                    'type'     => $_FILES[$key]['type'],
                    'tmp_name' => $_FILES[$key]['tmp_name'],
                    'error'    => $_FILES[$key]['error'],
                    'size'     => $_FILES[$key]['size'],
                ])
            ];
        }

        $files = [];
        foreach ($_FILES[$key]['name'] as $i => $name) {
            if ($_FILES[$key]['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $files[] = new UploadedFile([
                'name'     => $_FILES[$key]['name'][$i],
                'type'     => $_FILES[$key]['type'][$i],
                'tmp_name' => $_FILES[$key]['tmp_name'][$i],
                'error'    => $_FILES[$key]['error'][$i],
                'size'     => $_FILES[$key]['size'][$i]
            ]);
        }

        return $files;
    }
}
