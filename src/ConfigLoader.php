<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\Loader\PhpLoader;
use Chiron\Config\Loader\IniLoader;
use Chiron\Config\Loader\JsonLoader;
use Chiron\Config\Loader\YmlLoader;
use Chiron\Config\Loader\LoaderInterface;

// TODO : Classe à renommer en ConfigReader ???? ou ConfigParser ????
class ConfigLoader
{
    /** @var LoaderInterface[] */
    protected $loaders = [];

    public function __construct()
    {
        $this->loaders[] = new PhpLoader();
        $this->loaders[] = new IniLoader();
        $this->loaders[] = new JsonLoader();
        $this->loaders[] = new YmlLoader();
    }

    /**
     * @param LoaderInterface $loader
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    public function load(string $path): array
    {
        foreach ($this->loaders as $loader) {
            if ($loader->canLoad($path)) {
                return $loader->load($path);
            }
        }

        return [];

        // TODO : mettre une exception de type ConfigException, et mettre un message comme quoi on n'a pas de loader valable pour ce type de fichiers.
        //throw new \InvalidArgumentException(sprintf('Cannot load "%s"', $path));
    }
}
