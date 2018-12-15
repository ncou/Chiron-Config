<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\Loader\LoaderInterface;

class ConfigLoader
{
    /** @var ConfigInterface */
    protected $config;

    /** @var LoaderInterface[] */
    protected $loaders = [];

    /**
     * @param array $items
     */
    public function __construct(ConfigInterface $config = null)
    {
        $this->config = $config ?? new Config();
    }

    /**
     * @param LoaderInterface $loader
     */
    public function pushLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $path): void
    {
        foreach ($this->loaders as $loader) {
            if ($loader->canLoad($path)) {
                $this->config->merge($loader->load($path));

                return;
            }
        }

        throw new \InvalidArgumentException(sprintf('Cannot load "%s"', $path));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }
}
