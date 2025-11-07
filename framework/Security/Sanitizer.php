<?php

namespace FF\Security;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Sanitizer - Input Sanitization
 *
 * Provides methods for sanitizing user input to prevent XSS attacks.
 */
class Sanitizer
{
    /** @var array<int,string> */
    protected static array $defaultAllowedTags = [
        'a', 'abbr', 'b', 'blockquote', 'br', 'caption', 'code', 'del',
        'em', 'figcaption', 'figure', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'hr', 'i', 'img', 'ins', 'li', 'mark', 'ol', 'p', 'pre', 'span',
        'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th',
        'thead', 'tr', 'ul'
    ];

    /** @var array<string,array<int,string>> */
    protected static array $defaultAllowedAttributes = [
        '*' => ['class'],
        'a' => ['href', 'title', 'rel', 'target'],
        'abbr' => ['title'],
        'caption' => ['align'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'ol' => ['start'],
        'th' => ['scope', 'colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
        'table' => ['summary'],
    ];

    public static function string(string $value): string
    {
        $value = self::removeNullBytes($value);
        $value = trim($value);
        $value = strip_tags($value);

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function email(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    public static function url(string $url): string
    {
        $url = self::removeNullBytes(trim($url));
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    public static function int($value): int
    {
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function float($value): float
    {
        return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function array(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::array($value);
                continue;
            }

            if (is_string($value)) {
                $sanitized[$key] = self::string($value);
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    public static function isSuspiciousSql(string $value): bool
    {
        $sqlKeywords = ['UNION', 'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER'];

        foreach ($sqlKeywords as $keyword) {
            if (stripos($value, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function removeNullBytes(string $value): string
    {
        return str_replace("\0", '', $value);
    }

    public static function htmlEncode($value): string
    {
        if (is_array($value)) {
            return '<pre>' . self::escape(json_encode($value, JSON_PRETTY_PRINT)) . '</pre>';
        }

        return self::escape((string)$value);
    }

    /**
     * Sanitize HTML content using allowlist of tags and attributes.
     */
    public static function cleanHtml(string $html, ?array $allowedTags = null, ?array $allowedAttributes = null): string
    {
        $html = self::removeNullBytes($html);

        $allowedTags = $allowedTags !== null
            ? array_values(array_unique(array_map('strtolower', $allowedTags)))
            : self::$defaultAllowedTags;

        $allowedAttributes = $allowedAttributes !== null
            ? self::normalizeAllowedAttributes($allowedAttributes)
            : self::$defaultAllowedAttributes;

        if (trim($html) === '') {
            return '';
        }

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);

        $doc->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        self::sanitizeNode($doc, $allowedTags, $allowedAttributes);

        $clean = $doc->saveHTML() ?: '';

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $clean;
    }

    protected static function sanitizeNode(DOMNode $node, array $allowedTags, array $allowedAttributes): void
    {
        if (!$node->hasChildNodes()) {
            return;
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMComment) {
                $node->removeChild($child);
                continue;
            }

            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (!in_array($tag, $allowedTags, true)) {
                    self::replaceWithChildren($child);
                    continue;
                }

                self::sanitizeElementAttributes($child, $allowedAttributes);
            }

            self::sanitizeNode($child, $allowedTags, $allowedAttributes);
        }
    }

    protected static function replaceWithChildren(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (!$parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    protected static function sanitizeElementAttributes(DOMElement $element, array $allowedAttributes): void
    {
        $tag = strtolower($element->tagName);

        $globalAllowed = $allowedAttributes['*'] ?? [];
        $tagAllowed = array_merge($globalAllowed, $allowedAttributes[$tag] ?? []);

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $value = (string)$attribute->value;

            if (!in_array($name, $tagAllowed, true) || !self::isAttributeValueAllowed($tag, $name, $value)) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            $cleanValue = self::cleanAttributeValue($name, $value);
            if ($cleanValue === null) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            $element->setAttribute($attribute->name, $cleanValue);
        }
    }

    protected static function isAttributeValueAllowed(string $tag, string $attribute, string $value): bool
    {
        $value = trim($value);

        if ($attribute === 'class') {
            return true;
        }

        if ($attribute === 'href' || $attribute === 'src') {
            // Disallow empty or dangerous URI schemes outright
            if ($value === '' || preg_match('/^(javascript|vbscript|data):/i', $value)) {
                return false;
            }

            // Allow fragment-only references
            if (str_starts_with($value, '#')) {
                return true;
            }

            // Allow common safe external schemes
            if (preg_match('/^(https?:|mailto:|tel:)/i', $value)) {
                return true;
            }

            // Allow data:image for src only (base64-encoded images)
            if ($attribute === 'src' && preg_match('/^data:image\/(gif|png|jpeg|webp);base64,/i', $value)) {
                return true;
            }

            // Allow same-origin relative paths:
            // - root-relative: /path
            // - dot-relative: ./path or ../path
            // - plain relative: path/to
            // Block anything that explicitly declares a (potentially unsafe) scheme.
            $hasScheme = preg_match('/^[a-z][a-z0-9+.-]*:/i', $value) === 1;
            $isRootRelative = preg_match('#^/(?!/)#', $value) === 1; // starts with single '/'
            $isDotRelative = str_starts_with($value, './') || str_starts_with($value, '../');
            $isPlainRelative = !$hasScheme && !str_starts_with($value, '//');
            if ($isRootRelative || $isDotRelative || $isPlainRelative) {
                return true;
            }

            return false;
        }

        if (in_array($attribute, ['width', 'height', 'colspan', 'rowspan', 'start'], true)) {
            return ctype_digit($value) && (int)$value > 0 && (int)$value <= 4096;
        }

        if ($attribute === 'scope') {
            return in_array(strtolower($value), ['row', 'col', 'rowgroup', 'colgroup'], true);
        }

        if ($attribute === 'summary') {
            return $value !== '';
        }

        if ($attribute === 'align') {
            return in_array(strtolower($value), ['left', 'right', 'center', 'justify'], true);
        }

        if ($attribute === 'target') {
            return in_array(strtolower($value), ['_blank', '_self', '_parent', '_top'], true);
        }

        if ($attribute === 'rel') {
            return true;
        }

        return $value !== '' && !preg_match('/^(javascript|vbscript|data):/i', $value);
    }

    protected static function cleanAttributeValue(string $attribute, string $value): ?string
    {
        $value = self::removeNullBytes(trim($value));

        if ($value === '') {
            return null;
        }

        if ($attribute === 'class') {
            $value = preg_replace('/[^a-z0-9_\-\s]/i', ' ', $value);
            $value = preg_replace('/\s+/', ' ', $value);
            $value = trim($value);
            return $value === '' ? null : $value;
        }

        if (in_array($attribute, ['width', 'height', 'colspan', 'rowspan', 'start'], true)) {
            return ctype_digit($value) ? (string)(int)$value : null;
        }

        return $value;
    }

    protected static function normalizeAllowedAttributes(array $attributes): array
    {
        $normalized = [];

        foreach ($attributes as $tag => $list) {
            $tag = strtolower($tag);
            $normalized[$tag] = array_values(array_unique(array_map('strtolower', (array)$list)));
        }

        return $normalized;
    }
}
