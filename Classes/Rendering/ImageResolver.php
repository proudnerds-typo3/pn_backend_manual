<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Rendering;

/**
 * Turns an image path relative to the Markdown file into a public URL, but only for files
 * inside the given public directory, so a manual cannot expose anything else.
 */
final readonly class ImageResolver
{
    /**
     * @param \Closure(string): string $publicUrl gets the path relative to $publicDirectory
     */
    public function __construct(
        private string $markdownDirectory,
        private string $publicDirectory,
        private \Closure $publicUrl,
    ) {}

    public function resolve(string $source): ?string
    {
        if ($source === '' || str_starts_with($source, '/') || preg_match('#^[a-z][a-z0-9+.-]*:#i', $source) === 1) {
            return null;
        }

        $path = rawurldecode($source);
        if (str_contains($path, "\0")) {
            return null;
        }
        $file = realpath($this->markdownDirectory . '/' . $path);
        $publicDirectory = realpath($this->publicDirectory);
        if ($file === false || $publicDirectory === false || !is_file($file)
            || !str_starts_with($file, $publicDirectory . DIRECTORY_SEPARATOR)
        ) {
            return null;
        }

        $relativePath = substr($file, strlen($publicDirectory) + 1);
        // The core builds the URL from the path as is, so these would become query or fragment
        if (strpbrk($relativePath, '#?') !== false) {
            return null;
        }

        return ($this->publicUrl)($relativePath);
    }
}
