<?php

declare(strict_types=1);

namespace Chiron\Config\Loader;

interface LoaderInterface
{
    /**
     * @param string $path
     *
     * @return bool
     */
    public function canLoad(string $path): bool;

    /**
     * @param string $path
     *
     * @return array
     */
    public function load(string $path): array;
}
