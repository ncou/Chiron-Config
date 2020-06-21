<?php

declare(strict_types=1);

namespace Chiron\Config\Loader;

use Chiron\Config\Exception\LoaderException;
use Chiron\Config\Exception\UnsupportedFormatException;

use Throwable;

// TODO : utiliser le trait suivant qui vérifie qu'on ne charge pas un fichier de config en remontant dans l'arborescence via des "../"      https://github.com/cakephp/cakephp/blob/cafb1a25c07f4273f9e73d6aee46efbeeb6556bd/src/Core/Configure/FileConfigTrait.php#L44

class PhpLoader implements LoaderInterface
{
    /** @var string */
    protected $pattern;

    public function __construct(string $pattern = '~^[a-z_][a-z0-9_]*\.php$~')
    {
        $this->pattern = $pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function canLoad(string $path): bool
    {
        return file_exists($path) && preg_match($this->pattern, pathinfo($path)['basename']);
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $path): array
    {
        $data = require $path;

        // Check for array, if its anything else, throw an exception
        if (! is_array($data)) {
            throw new LoaderException(sprintf('Config file "%s" did not return an array', $path));
        }

        return $data;
    }

/*
    public function getSupportedExtensions()
    {
        return ['php'];
    }
    */
}
