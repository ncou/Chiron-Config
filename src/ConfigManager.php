<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\Loader\LoaderInterface;
use Chiron\Config\Exception\ConfigException;
use Chiron\Boot\Filesystem;
use Chiron\Config\Loader\PhpLoader;
use Chiron\Config\Loader\IniLoader;
use Chiron\Config\Loader\JsonLoader;
use Chiron\Config\Loader\YmlLoader;
use LogicException;

//https://github.com/illuminate/config/blob/master/Repository.php
//https://github.com/hassankhan/config/blob/master/src/AbstractConfig.php#L114
//https://github.com/zendframework/zend-config/blob/master/src/Config.php

// TODO : on devrait pas créer une classe ConfigFactory qui se charge de créer les objets Config ??? https://github.com/zendframework/zend-config/blob/master/src/Factory.php
final class ConfigManager
{
    /** @var Config[] */
    private $sections = [];
    /** @var LoaderInterface[] */
    private $loaders = [];
    /** @var Filesystem */
    private $filesystem;

    public function __construct()
    {
        $this->loaders[] = new PhpLoader();
        $this->loaders[] = new IniLoader();
        $this->loaders[] = new JsonLoader();
        $this->loaders[] = new YmlLoader();

        $this->filesystem = new Filesystem();
    }

    public function hasConfig(string $section): bool
    {
        return isset($this->sections[$section]);
    }

    public function getConfig(string $section, ?string $subset = null): ConfigInterface
    {
        if (! $this->hasConfig($section)) {
            // TODO : afficher le nom de la section recherchée dans le message de l'exception. Ca sera plus simple pour débugger !!!
            throw new ConfigException('Config not found in the manager !');
        }

        $config = $this->sections[$section];

        if ($subset !== null) {
            $data = $config->get($subset);

            if (! is_array($data)) {
                // TODO : afficher le nom du subset recherché dans le message de l'exception. Ca sera plus simple pour débugger !!! Afficher le gettype() pour indiquer si c'est une chaine ou null par exemple.
                throw new ConfigException('Subset must be an array !');
            }

            $config = new Config($data);
        }

        return $config;
    }

    public function getConfigData(string $section, ?string $subset = null): array
    {
        $config = $this->getConfig($section, $subset);

        return $config->getData();
    }

    // TODO : code à améliorer !!!!
    // TODO : il faudrait créer une méthode loadFromFiles qui lirait un tableau de fichiers (un objet Transversable par exemple)
    public function loadFromFile(string $file): void
    {
        if (! $this->filesystem->isFile($file)) {
            // TODO : Lever plutot une InvalidConfigurationException
            throw new ConfigException('Invalid file path');
        }

        foreach ($this->loaders as $loader) {
            if ($loader->canLoad($file)) {
                $this->config->merge($loader->load($file));

                return;
            }
        }

        throw new ConfigException(sprintf('Cannot load "%s"', $path));
    }

    // TODO : éventuellement lui passer un paramétre $section pour le nom et ensuite le contenu $data
    public function loadFromArray(array $data): void
    {
        // TODO : à implémenter !!!!
    }

    public function loadFromDirectory(string $directory): void
    {
        if (! $this->filesystem->isDirectory($directory)) {
            // TODO : Lever plutot une InvalidConfigurationException
            throw new ConfigException('Invalid directory path');
        }

        $directory = realpath($directory);
        $files = $this->filesystem->files($directory);

        foreach ($files as $file) {

            // TODO : lui passer plutot un getBaseName en paramétre, voir même un getExtension() !!!!
            $loader = $this->getLoaderFor($file->getRealPath());

            if ($loader) {
                $section = $this->generateSectionName($file, $directory);
                $data = $loader->load($file->getRealPath());

                $this->merge($section, $data);
            }
        }
    }

    // return the Loader found, if none found return null
    private function getLoaderFor(string $filepath): ?LoaderInterface
    {
        foreach ($this->loaders as $loader) {
            if ($loader->canLoad($filepath)) {
                return $loader;
            }
        }

        return null;
    }
    /**
     * Gets a parser for a given file extension.
     *
     * @param  string $extension
     *
     * @return Noodlehaus\Parser\ParserInterface
     *
     * @throws UnsupportedFormatException If `$extension` is an unsupported file format
     */
    /*
    protected function getParser($extension)
    {
        foreach ($this->supportedParsers as $parser) {
            if (in_array($extension, $parser::getSupportedExtensions())) {
                return new $parser();
            }
        }

        // If none exist, then throw an exception
        throw new UnsupportedFormatException('Unsupported configuration format');
    }*/


    /**
     * Generate the section name (nesting path + file name using dot separator).
     *
     * @param  \SplFileInfo  $file
     * @param  string  $path
     * @return string
     */
    private function generateSectionName(\SplFileInfo $file, string $path): string
    {
        $directory = $file->getPath();
        $extension = '.' . $file->getExtension();

        if ($nested = trim(str_replace($path, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }

        return $nested . $file->getBasename($extension);
    }

    /**
     * @param LoaderInterface $loader
     */
    public function addLoader(LoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * @param array $appender
     */
    // TODO : conserver cette méthode en public ??? éventuelleùment cela permettrait de charger la config depuis un array (ce qui viendrait compléter les possibilité de chargement en plus des méthodes loadFromDirectory et loadFromFile) éventuellement renommer cette méthode en loadFromArray($section, $data)
    public function merge(string $section, array $appender): void
    {
        // if the section is already present, we merge both the datas.
        $origin = $this->hasConfig($section) ? $this->getConfigData($section) : [];
        $result = $this->recursiveMerge($origin, $appender);
        //$result = array_merge($origin, $appender);
        $this->sections[$section] = new Config($result);
    }

    /**
     * @param mixed $origin
     * @param mixed $appender
     *
     * @return mixed
     */
    //https://github.com/yiisoft/yii2-framework/blob/ecae73e23abb524bb637c37c62e4db5495f5f4f2/helpers/BaseArrayHelper.php#L117
    //https://github.com/hiqdev/composer-config-plugin/blob/master/src/utils/Helper.php#L27
    private function recursiveMerge($origin, $appender)
    {
        if (is_array($origin)
            && array_values($origin) !== $origin
            && is_array($appender)
            && array_values($appender) !== $appender) {
            foreach ($appender as $key => $value) {
                if (isset($origin[$key])) {
                    $origin[$key] = $this->recursiveMerge($origin[$key], $value);
                } else {
                    $origin[$key] = $value;
                }
            }

            return $origin;
        }

        return $appender;
    }

}
