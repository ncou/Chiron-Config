<?php

declare(strict_types=1);

namespace Chiron\Config\Loader;

use DirectoryIterator;

class PathLoader implements LoaderInterface
{
    /** @var LoaderInterface[] */
    protected $loaders;

    /** @var string */
    protected $pattern;

    public function __construct(array $loaders = [], string $pattern = '~^[a-z_][a-z0-9_]*$~')
    {
        $this->loaders = $loaders;
        $this->pattern = $pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function canLoad(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $path)
    {
        $configToReturn = [];
        $dir = new DirectoryIterator($path);

        foreach ($dir as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            if ($fileInfo->isFile()) {
                $name = $fileInfo->getBasename('.' . $fileInfo->getExtension());
                foreach ($this->loaders as $loader) {
                    if ($loader->canLoad($fileInfo->getRealPath())) {
                        if ($config = $loader->load($fileInfo->getRealPath())) {
                            $configToReturn[$name] = $config;
                        }
                    }
                }
            } else {
                $filename = $fileInfo->getFilename();
                if (preg_match($this->pattern, $filename)) {
                    $configToReturn[$filename] = $this->load($fileInfo->getRealPath());
                }
            }
        }

        return $configToReturn;
    }
}
