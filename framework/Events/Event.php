<?php

namespace FF\Events;

/**
 * Base Event class.
 *
 * Events extending this class automatically provide their fully-qualified class
 * name for dispatcher matching.
 */
abstract class Event
{
    /**
     * Get the canonical event name.
     */
    public function getName(): string
    {
        return static::class;
    }
}

