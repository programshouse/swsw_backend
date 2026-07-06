<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class FileHelper
{
    public static function uploadImage(
        Request $request,
        string $field = 'image',
        string $folder = 'assets/uploads/'
    ): ?string {

        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);

        $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());

        $path = public_path($folder);

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file->move($path, $fileName);

        return 'public/' . trim($folder, '/') . '/' . $fileName;
    }

    public static function deleteFile(?string $filePath): void
    {
        if (!$filePath) {
            return;
        }

        $realPath = public_path(str_replace('public/', '', $filePath));

        if (file_exists($realPath)) {
            @unlink($realPath);
        }
    }
}