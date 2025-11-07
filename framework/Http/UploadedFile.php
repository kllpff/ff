<?php

namespace FF\Http;

class UploadedFile
{
    protected string $originalName;
    protected string $tmpName;
    protected int $size;
    protected int $error;
    protected ?string $clientType;

    public function __construct(array $file)
    {
        $this->originalName = (string)($file['name'] ?? '');
        $this->tmpName = (string)($file['tmp_name'] ?? '');
        $this->size = (int)($file['size'] ?? 0);
        $this->error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        $this->clientType = isset($file['type']) ? (string)$file['type'] : null;
    }

    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK
            && $this->tmpName !== ''
            && is_uploaded_file($this->tmpName);
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getClientOriginalName(): string
    {
        return $this->originalName;
    }

    public function getClientMimeType(): ?string
    {
        return $this->clientType;
    }

    public function getMimeType(): string
    {
        if ($this->tmpName && file_exists($this->tmpName)) {
            if (function_exists('finfo_open')) {
                $f = finfo_open(FILEINFO_MIME_TYPE);
                if ($f) {
                    $type = finfo_file($f, $this->tmpName);
                    finfo_close($f);
                    if (is_string($type) && $type !== '') {
                        return $type;
                    }
                }
            }
            if (function_exists('mime_content_type')) {
                $type = @mime_content_type($this->tmpName);
                if (is_string($type) && $type !== '') {
                    return $type;
                }
            }
        }
        return $this->clientType ?? 'application/octet-stream';
    }

    public function getClientExtension(): string
    {
        $ext = pathinfo($this->originalName, PATHINFO_EXTENSION);
        return strtolower((string)$ext);
    }

    protected function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/[\\\\\/]+/', '-', $name);
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
        $name = trim($name, '.-_');
        if ($name === '') {
            $ext = $this->getClientExtension();
            $name = 'file_' . bin2hex(random_bytes(6));
            if ($ext !== '') {
                $name .= '.' . $ext;
            }
        }
        return $name;
    }

    /**
     * Move the uploaded file into target directory under approved base paths.
     * Returns absolute path to saved file.
     */
    public function move(string $targetDir, ?string $fileName = null): string
    {
        if (!$this->isValid()) {
            throw new \RuntimeException('Uploaded file is not valid.');
        }

        $approved = [
            rtrim(\base_path('public/uploads'), DIRECTORY_SEPARATOR),
            rtrim(\storage_path('uploads'), DIRECTORY_SEPARATOR),
        ];

        $targetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
        if (!is_dir($targetDir)) {
            if (!@mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
                throw new \RuntimeException('Failed to create upload directory: ' . $targetDir);
            }
        }

        $realTarget = realpath($targetDir);
        if ($realTarget === false) {
            throw new \RuntimeException('Upload directory is invalid: ' . $targetDir);
        }

        $allowed = false;
        foreach ($approved as $base) {
            $realBase = realpath($base) ?: $base;
            if (strpos($realTarget, rtrim($realBase, DIRECTORY_SEPARATOR)) === 0) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) {
            throw new \RuntimeException('Uploads are only allowed under public/uploads or storage/uploads.');
        }

        $finalName = $this->sanitizeFileName($fileName ?: $this->getClientOriginalName());
        $finalPath = $realTarget . DIRECTORY_SEPARATOR . $finalName;

        // Ensure unique file if exists
        $counter = 0;
        $pathInfo = pathinfo($finalPath);
        while (file_exists($finalPath)) {
            $counter++;
            $suffix = '-' . $counter;
            $nameStem = $pathInfo['filename'];
            $ext = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
            $finalPath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $nameStem . $suffix . $ext;
        }

        if (!@move_uploaded_file($this->tmpName, $finalPath)) {
            throw new \RuntimeException('Failed to move uploaded file.');
        }

        return $finalPath;
    }

    public function moveToPublicUploads(?string $fileName = null): string
    {
        return $this->move(\base_path('public/uploads'), $fileName);
    }

    public function moveToStorageUploads(?string $fileName = null): string
    {
        return $this->move(\storage_path('uploads'), $fileName);
    }
}