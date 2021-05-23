<?php

declare(strict_types=1);

namespace Chiron\Config\Loader;

use Chiron\Config\Exception\LoaderException;

interface LoaderInterface
{
    /**
     * Return true if config section exists.
     *
     * @param string $section
     * @return bool
     */
    public function has(string $section): bool;

    /**
     * Load a config section data array.
     *
     * @param string $section
     * @return array
     *
     * @throws LoaderException
     */
    public function load(string $section): array;
}
