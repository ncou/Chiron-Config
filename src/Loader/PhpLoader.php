<?php

declare(strict_types=1);

namespace Chiron\Config\Loader;

use Chiron\Config\Exception\LoaderException;

use Throwable;

// TODO : utiliser le trait suivant qui vérifie qu'on ne charge pas un fichier de config en remontant dans l'arborescence via des "../"      https://github.com/cakephp/cakephp/blob/cafb1a25c07f4273f9e73d6aee46efbeeb6556bd/src/Core/Configure/FileConfigTrait.php#L44

//https://github.com/spiral/config/blob/master/src/Loader/PhpLoader.php

final class PhpLoader implements LoaderInterface
{
    /** @var string */
    private $extension = 'php';

    /** @var string */
    private $path;

    /**
     * Constructor for PHP Config file reading.
     *
     * @param string $path The path to load config files from.
     */
    public function __construct(string $path)
    {
        $this->path = rtrim($path, '\/');
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $section): bool
    {
        $filepath = $this->getFilePath($section);

        return is_file($filepath);
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $section): array
    {
        $filepath = $this->getFilePath($section, true);

        $data = include $filepath; // TODO : faire un require et surtout un try/catch Throwable pour transformer l'exception en LoaderException !!! https://github.com/spiral/config/blob/master/src/Loader/PhpLoader.php#L38

        // Check for array, if its anything else, throw an exception
        if (! is_array($data)) {
            throw new LoaderException(sprintf('Config file "%s" did not return an array', $filepath));
        }

        return $data;
    }

    /**
     * Get file path
     *
     * @param string $name
     * @param bool $checkExists Whether to check if file exists. Defaults to false.
     *
     * @return string Full file path
     *
     * @throws LoaderException When files don't exist or when files contain '..' as this could lead to abusive reads.
     */
    // TODO : mettre ce bout de code dans un trait car il pourra servir pour les différentes classes de Loader (pour lire du json, du ini ou du yaml).
    private function getFilePath(string $name, bool $checkExists = false): string
    {
        if (strpos($name, '..') !== false) {
            throw new LoaderException('Cannot load configuration files with ../ in them.');
        }

        $file = sprintf('%s/%s.%s', $this->path, $name, $this->extension);

        if (! $checkExists || is_file($file)) {
            return $file;
        }

        $realPath = realpath($file);
        if ($realPath !== false && is_file($realPath)) {
            return $realPath;
        }

        throw new LoaderException(sprintf('Could not load configuration file: %s', $file)); // TODO : changer le message en 'Unable to load config "$name".'

    }
}
