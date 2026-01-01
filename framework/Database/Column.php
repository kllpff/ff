<?php

namespace FF\Database;

/**
 * Column - Schema Column Definition
 */
class Column
{
    protected string $name;
    protected string $type;
    protected bool $nullable = false;
    protected bool $autoIncrement = false;
    protected bool $isUnique = false;
    protected ?string $default = null;
    protected ?string $comment = null;
    protected ?int $length = null;
    protected ?int $precision = null;
    protected ?int $scale = null;
    protected ?Table $table = null;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string { return $this->name; }
    public function getType(): string { return $this->type; }
    public function isNullable(): bool { return $this->nullable; }
    public function isAutoIncrement(): bool { return $this->autoIncrement; }
    public function isUnique(): bool { return $this->isUnique; }
    public function getDefault(): ?string { return $this->default; }
    public function getComment(): ?string { return $this->comment; }
    public function getLength(): ?int { return $this->length; }
    public function getPrecision(): ?int { return $this->precision; }
    public function getScale(): ?int { return $this->scale; }

    public function nullable(bool $value = true): self
    {
        $this->nullable = $value;
        return $this;
    }

    public function autoIncrement(): self
    {
        $this->autoIncrement = true;
        return $this;
    }

    public function default($value): self
    {
        $this->default = $value;
        return $this;
    }

    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function setLength(int $length): self
    {
        $this->length = $length;
        return $this;
    }

    public function setPrecision(int $precision): self
    {
        $this->precision = $precision;
        return $this;
    }

    public function setScale(int $scale): self
    {
        $this->scale = $scale;
        return $this;
    }

    public function setTable(Table $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function unique(): self
    {
        $this->isUnique = true;
        if ($this->table) {
            $this->table->addUniqueKey([$this->name]);
        }
        return $this;
    }

    public function after(string $column): self
    {
        // MySQL-specific column positioning
        // For now, we'll ignore this as it's not critical
        return $this;
    }
}
