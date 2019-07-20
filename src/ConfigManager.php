<?php

declare(strict_types=1);

namespace Chiron\Config;

use LogicException;

class ConfigManager
{
    /** @var LoaderInterface */
    private $loader;
    /** @var bool */
    private $strict;
    /** @var array */
    private $data = [];
    /** @var array */
    private $defaults = [];
    /** @var array */
    //private $instances = [];

    /**
     * @param LoaderInterface $loader
     * @param bool            $strict
     */
    public function __construct()//LoaderInterface $loader, bool $strict = true)
    {
        //$this->loader = $loader;
        //$this->strict = $strict;

        // TODO : c'est un test à virer plus tard.
        $this->data['html'] = ["basePath" => "/toto"];
    }

    /**
     * @inheritdoc
     */
    public function exists(string $section): bool
    {
        return isset($this->defaults[$section]) || isset($this->data[$section]) || $this->loader->has($section);
    }

    /**
     * @inheritdoc
     */
    public function setDefaults(string $section, array $data)
    {
        if (isset($this->defaults[$section])) {
            throw new LogicException("Unable to set default config `{$section}` more than once.");
        }
        if (isset($this->data[$section])) {
            throw new LogicException("Unable to set default config `{$section}`, config has been loaded.");
        }

        $this->defaults[$section] = $data;
    }

    /**
     * @inheritdoc
     */
    // TODO : à virer !!!!
    /*
    public function modify(string $section, PatchInterface $patch): array
    {
        if (isset($this->instances[$section])) {
            if ($this->strict) {
                throw new ConfigDeliveredException(
                    "Unable to patch config `{$section}`, config object has already been delivered."
                );
            }
            unset($this->instances[$section]);
        }
        $data = $this->getConfig($section);
        try {
            return $this->data[$section] = $patch->patch($data);
        } catch (PatchException $e) {
            throw new PatchException("Unable to modify config `{$section}`.", $e->getCode(), $e);
        }
    }*/

    /**
     * {@inheritdoc}
     */
    // TODO : améliorer la fonction "has()" et "get()" avec l'utilisation d'un cache (à vider dans la fonction merge) !!!! => https://github.com/hassankhan/config/blob/master/src/AbstractConfig.php#L114
    public function has(string $name): bool
    {
        if ($name === '') {
            return true;
        }
        $names = explode('.', $name);
        $dataToReturn = $this->data;
        while (count($names)) {
            $name = array_shift($names);
            if (! is_array($dataToReturn) || ! array_key_exists($name, $dataToReturn)) {
                return false;
            }
            $dataToReturn = $dataToReturn[$name];
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    // TODO : améliorer la fonction "get()" => https://github.com/pinepain/php-simple-config/blob/master/src/Config.php#L49
    public function get(string $name, $default = null)
    {
        if ($name === '') {
            return $this->data;
        }
        $names = explode('.', $name);
        $dataToReturn = $this->data;
        while (count($names)) {
            $name = array_shift($names);
            if (! is_array($dataToReturn) || ! array_key_exists($name, $dataToReturn)) {
                return $default;
            }
            $dataToReturn = $dataToReturn[$name];
        }

        return $dataToReturn;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(string $section = null): array
    {
        // Read the cache first.
        if (isset($this->data[$section])) {
            return $this->data[$section];
        }

        if (isset($this->defaults[$section])) {
            $data = [];
            if ($this->loader->has($section)) {
                $data = $this->loader->load($section);
            }
            $data = array_merge($this->defaults[$section], $data);
        } else {
            $data = $this->loader->load($section);
        }

        return $this->data[$section] = $data;
    }


    public function loadConfig(string $path): void
    {
        //$path = realpath(get_path('config'));
        $path = realpath($path);

        $files = $this->getConfigurationFiles($path);

        if (! isset($files['app'])) {
            throw new LogicException(sprintf('Unable to load the "app" configuration file in the "%s" folder.', $path));
        }

        //$config = $container->get('config');

        foreach ($files as $key => $path) {
            //$config->merge([$key => $this->requirePhpFile($path)]);
            $this->merge([$key => require $path]);
        }
    }

    function getConfigurationFiles(string $configPath): array
    {
        $files = [];
        $configPath = realpath($configPath);

        foreach ($this->getIterator($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);
            $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }

    /**
     * Get the configuration file nesting path.
     *
     * @param  \SplFileInfo  $file
     * @param  string  $configPath
     * @return string
     */
    function getNestedDirectory(\SplFileInfo $file, string $configPath): string
    {
        $directory = $file->getPath();
        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }
        return $nested;
    }


    function getIterator(string $configPath): \Iterator
    {
        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS;
        $depth = -1;

        $directory = new \RecursiveDirectoryIterator($configPath, $flags);

        $directory = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::SELF_FIRST);
        $directory->setMaxDepth($depth);

        $directory = new \CallbackFilterIterator($directory, function (\SplFileInfo $current) {
            return $current->isFile();
        });

        $directory = new \CallbackFilterIterator($directory, function (\SplFileInfo $current) {
            return $current->getExtension() === 'php';
        });

        //return iterator_to_array($directory);
        return $directory;
    }

    /**
     * @param array $appender
     */
    public function merge(array $appender): void
    {
        $this->data = $this->recursiveMerge($this->data, $appender);
    }

    /**
     * @param mixed $origin
     * @param mixed $appender
     *
     * @return mixed
     */
    // TODO : on dirait que les deux paramétres sont des tableaux. et que la valeur de retour sera aussi un tableau. modifier le typehint
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
