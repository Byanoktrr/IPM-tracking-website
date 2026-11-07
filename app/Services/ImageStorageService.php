<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class ImageStorageService
{
    /**
     * Get the resolved absolute path to the image directory.
     */
    public function getStoragePath(): string
    {
        $path = config('po.image_path', storage_path('app/public/gambar_po'));

        // Check if path is relative or starts with storage/
        if (! preg_match('/^[a-zA-Z]:[\\\\\/]/', $path) && ! str_starts_with($path, '/') && ! str_starts_with($path, '\\')) {
            $path = base_path($path);
        }

        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true, true);
        }

        return $path;
    }

    /**
     * Save an image from a base64 string, UploadedFile, or return as-is if string/URL.
     */
    public function saveImage(string|UploadedFile|null $input, string|int $poId, string $prefix = 'resi'): ?string
    {
        if (blank($input)) {
            return null;
        }

        $storageDir = $this->getStoragePath();
        $timestamp = time();

        // Handle UploadedFile
        if ($input instanceof UploadedFile) {
            $ext = $input->getClientOriginalExtension() ?: 'jpg';
            $filename = "{$prefix}-{$poId}-{$timestamp}.{$ext}";
            $input->move($storageDir, $filename);

            return $filename;
        }

        // Handle base64 data URI
        if (preg_match('/^data:image\/(\w+);base64,(.+)$/s', $input, $matches)) {
            $ext = strtolower($matches[1]);
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            $data = base64_decode($matches[2]);
            $filename = "{$prefix}-{$poId}-{$timestamp}.{$ext}";
            File::put($storageDir.DIRECTORY_SEPARATOR.$filename, $data);

            return $filename;
        }

        // Handle raw base64 without data URI prefix if it's long enough
        if (strlen($input) > 200 && preg_match('/^[a-zA-Z0-9\/+=\r\n]+$/', $input)) {
            $data = base64_decode($input);
            $filename = "{$prefix}-{$poId}-{$timestamp}.jpg";
            File::put($storageDir.DIRECTORY_SEPARATOR.$filename, $data);

            return $filename;
        }

        // Otherwise it is already a filename or URL
        return $input;
    }

    /**
     * Transform a stored filename into a base64 Data URI if the file exists.
     * If file not found or already a data URI / URL, returns string as-is.
     */
    public function toBase64Uri(?string $filename): ?string
    {
        if (blank($filename)) {
            return null;
        }

        if (str_starts_with($filename, 'data:image') || str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://')) {
            return $filename;
        }

        $filePath = $this->getStoragePath().DIRECTORY_SEPARATOR.$filename;

        if (File::exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => 'image/jpeg',
            };

            $data = base64_encode(File::get($filePath));

            return "data:{$mime};base64,{$data}";
        }

        return $filename;
    }

    /**
     * Get the full file path for a stored image if it exists.
     */
    public function getFilePath(string $filename): ?string
    {
        $filePath = $this->getStoragePath().DIRECTORY_SEPARATOR.$filename;

        return File::exists($filePath) ? $filePath : null;
    }
}
