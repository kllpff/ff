<?php

namespace FF\View;

/**
 * HtmlValue - Wrapper for HTML-escaped values.
 *
 * Provides automatic escaping when rendered while allowing explicit raw output.
 */
class HtmlValue
{
    protected string $value;
    protected bool $isRaw;

    /**
     * @param string $value
     * @param bool $isRaw Whether the value should be considered safe/raw.
     */
    public function __construct(string $value, bool $isRaw = false)
    {
        $this->value = $value;
        $this->isRaw = $isRaw;
    }

    /**
     * Create a wrapped value that will be escaped on output.
     */
    public static function sanitized(string $value): self
    {
        return new self($value, false);
    }

    /**
     * Create a wrapped value that will be rendered as-is.
     */
    public static function raw(string $value): self
    {
        return new self($value, true);
    }

    /**
     * Create a wrapper from any stringable value.
     */
    public static function from($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return new self((string)$value, false);
    }

    /**
     * Render the value as a string.
     */
    public function __toString(): string
    {
        return $this->isRaw ? $this->value : $this->escape();
    }

    /**
     * Get the escaped representation.
     */
    public function escape(int $flags = ENT_QUOTES, string $encoding = 'UTF-8'): string
    {
        return htmlspecialchars($this->value, $flags, $encoding);
    }

    /**
     * Determine if the value is marked as raw.
     */
    public function isRaw(): bool
    {
        return $this->isRaw;
    }

    /**
     * Get the underlying string without escaping.
     */
    public function rawValue(): string
    {
        return $this->value;
    }
}
