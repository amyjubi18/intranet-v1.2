<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait FileUploadSecurity
{
    /**
     * Allowed MIME types for file uploads.
     */
    protected array $allowedMimeTypes = [
        // Documents
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt' => 'text/plain',

        // Images
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',

        // Archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        '7z' => 'application/x-7z-compressed',

        // CSV
        'csv' => 'text/csv',
    ];

    /**
     * Allowed extensions.
     */
    protected array $allowedExtensions = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt',
        'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp',
        'zip', 'rar', '7z', 'csv',
    ];

    /**
     * Maximum file size in bytes (10MB).
     */
    protected int $maxFileSize = 10 * 1024 * 1024;

    /**
     * Validate and upload a file securely.
     */
    public function validateAndUploadFile(UploadedFile $file, string $directory, ?string $filename = null): array
    {
        // Validate file exists
        if (!$file->isValid()) {
            return [
                'success' => false,
                'error' => 'El archivo no se pudo subir correctamente.',
            ];
        }

        // Validate file size
        if ($file->getSize() > $this->maxFileSize) {
            return [
                'success' => false,
                'error' => 'El archivo excede el tamaño máximo permitido de 10MB.',
            ];
        }

        // Get file extension
        $extension = strtolower($file->getClientOriginalExtension());

        // Validate extension
        if (!in_array($extension, $this->allowedExtensions)) {
            return [
                'success' => false,
                'error' => 'El tipo de archivo no está permitido.',
            ];
        }

        // Validate MIME type
        $mimeType = $file->getMimeType();
        $expectedMimeType = $this->allowedMimeTypes[$extension] ?? null;

        if ($expectedMimeType && $mimeType !== $expectedMimeType) {
            return [
                'success' => false,
                'error' => 'El tipo MIME del archivo no coincide con su extensión.',
            ];
        }

        // Check for double extensions (security issue)
        if ($this->hasDoubleExtension($file->getClientOriginalName())) {
            return [
                'success' => false,
                'error' => 'El archivo contiene extensiones sospechosas.',
            ];
        }

        // Check for executable content in images
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            if ($this->containsExecutableContent($file)) {
                return [
                    'success' => false,
                    'error' => 'El archivo de imagen contiene contenido ejecutable.',
                ];
            }
        }

        // Generate safe filename
        $safeFilename = $filename ?? $this->generateSafeFilename($file);

        // Store file
        $path = $file->storeAs($directory, $safeFilename, 'public');

        return [
            'success' => true,
            'path' => $path,
            'filename' => $safeFilename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $mimeType,
        ];
    }

    /**
     * Check for double extensions (e.g., file.php.jpg).
     */
    protected function hasDoubleExtension(string $filename): bool
    {
        $parts = explode('.', $filename);
        if (count($parts) < 3) {
            return false;
        }

        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'exe', 'com', 'bat', 'sh', 'js', 'jsp', 'asp', 'aspx'];

        for ($i = 1; $i < count($parts) - 1; $i++) {
            if (in_array(strtolower($parts[$i]), $dangerousExtensions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an image file contains executable content (PHP shell).
     */
    protected function containsExecutableContent(UploadedFile $file): bool
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            return false;
        }

        $content = fread($handle, 8192);
        fclose($handle);

        // Check for PHP tags or suspicious patterns
        $suspiciousPatterns = [
            '<?php',
            '<?=',
            '<script',
            'eval(',
            'base64_decode',
            'shell_exec',
            'system(',
            'passthru',
            'exec(',
            'popen',
            'proc_open',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a safe filename.
     */
    protected function generateSafeFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Remove special characters and spaces
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
        $safeName = preg_replace('/_+/', '_', $safeName);
        $safeName = trim($safeName, '_');

        // Truncate if too long
        if (strlen($safeName) > 100) {
            $safeName = substr($safeName, 0, 100);
        }

        // Add unique identifier
        return $safeName . '_' . Str::random(8) . '.' . $extension;
    }

    /**
     * Delete a file securely.
     */
    public function deleteFile(string $path): bool
    {
        if (!file_exists(storage_path('app/public/' . $path))) {
            return false;
        }

        // Prevent directory traversal
        if (str_contains($path, '..') || str_starts_with($path, '/')) {
            return false;
        }

        return unlink(storage_path('app/public/' . $path));
    }
}

