<?php

namespace FF\Console\Concerns;

use InvalidArgumentException;
use function base_path;

/**
 * Shared input handling utilities for generator-style console commands.
 *
 * Ensures values used for class and file generation are sanitized and that
 * resolved paths cannot escape their intended base directories.
 */
trait SanitizesGeneratorInput
{
    /**
     * Read the first argument that follows the command name.
     */
    protected function readGeneratorArgument(): ?string
    {
        foreach ($_SERVER['argv'] as $index => $argument) {
            if ($argument === $this->name && isset($_SERVER['argv'][$index + 1])) {
                return $_SERVER['argv'][$index + 1];
            }
        }

        return null;
    }

    /**
     * Prompt for a value on STDIN.
     */
    protected function prompt(string $message): ?string
    {
        fwrite(STDOUT, $message);
        $input = fgets(STDIN);

        return $input === false ? null : trim($input);
    }

    /**
     * Sanitize a fully-qualified class name (with optional namespaces).
     */
    protected function sanitizeClassName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $name = str_replace(['/', '\\'], '\\', $name);
        $parts = array_filter(array_map('trim', explode('\\', $name)));

        if (empty($parts)) {
            return null;
        }

        $sanitized = [];

        foreach ($parts as $part) {
            $clean = preg_replace('/[^A-Za-z0-9_]/', '', $part);
            if ($clean === null || $clean === '' || preg_match('/^[0-9]/', $clean)) {
                return null;
            }

            $sanitized[] = ucfirst($clean);
        }

        return implode('\\', $sanitized);
    }

    /**
     * Sanitize a migration/seeder file name slug.
     */
    protected function sanitizeFileName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $value = strtolower(trim($name));
        if ($value === '') {
            return null;
        }

        $value = str_replace([' ', '-'], '_', $value);
        $value = preg_replace('/[^a-z0-9_]/', '', $value) ?? '';
        $value = preg_replace('/_+/', '_', $value) ?? '';
        $value = trim($value, '_');

        if ($value === '' || !preg_match('/[a-z]/', $value)) {
            return null;
        }

        return $value;
    }

    /**
     * Resolve a fully-qualified class name into a file path inside $base.
     */
    protected function resolveClassPath(string $base, string $qualifiedName): string
    {
        $normalized = $this->normalizeRelativePath(
            str_replace('\\', '/', $qualifiedName) . '.php'
        );

        $base = trim($base, '/');

        return base_path($base . '/' . $normalized);
    }

    /**
     * Normalize a relative path and ensure it does not escape the base.
     *
     * @throws InvalidArgumentException
     */
    protected function normalizeRelativePath(string $path): string
    {
        $path = str_replace(['\\', '/'], '/', $path);
        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                if (!empty($segments)) {
                    array_pop($segments);
                    continue;
                }

                throw new InvalidArgumentException('Invalid path traversal detected.');
            }

            if (!preg_match('/^[A-Za-z0-9_]+(\.php)?$/', $segment)) {
                throw new InvalidArgumentException('Invalid characters in path segment.');
            }

            $segments[] = $segment;
        }

        if (empty($segments)) {
            throw new InvalidArgumentException('Unable to resolve generator path.');
        }

        return implode('/', $segments);
    }
}
