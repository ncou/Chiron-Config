<?php

declare(strict_types=1);

namespace Chiron\Config;

use ArrayAccess;

interface ConfigInterface extends ArrayAccess
{
    /**
     * @param string $path
     */
    public function load(string $path): void;
    /**
     * @return array
     */
    public function toArray(): array;
    /**
     * @param string $name
     * @return ConfigInterface
     */
    public function subset(string $name): ConfigInterface;
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;
    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, $default = null);
    /**
     * @param string $name
     * @return ConfigInterface|mixed
     */
    public function __get($name);
}
