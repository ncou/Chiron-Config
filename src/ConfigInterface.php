<?php

declare(strict_types=1);

namespace Chiron\Config;

interface ConfigInterface extends \IteratorAggregate, \ArrayAccess
{
    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @return array
     */
    public function toArray(): array;
}
