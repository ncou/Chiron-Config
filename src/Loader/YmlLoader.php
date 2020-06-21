<?php

declare(strict_types=1);

namespace Chiron\Config\Loader;

use Symfony\Component\Yaml\Yaml as YamlParser;

class YmlLoader implements LoaderInterface
{
    /** @var string */
    protected $pattern;

    public function __construct(string $pattern = '~^[a-z_][a-z0-9_]*\.(yml|yaml)$~')
    {
        $this->pattern = $pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function canLoad(string $path): bool
    {
        // TODO : il faudrait pas ajouter une vérification si la classe YamlParser existe bien ? (c'est dans le cas ou l'utilisateur n'a pas installé le package Yaml de Symfony !!!)
        return file_exists($path) && preg_match($this->pattern, pathinfo($path)['basename']);
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $path): array
    {
        $data = YamlParser::parseFile($path, YamlParser::PARSE_CONSTANT);

        // Check for array, if its anything else, throw an exception
        if (! is_array($data)) {
            throw new LoaderException(sprintf('Config file [%s] does not return an array', $path));
        }

        return $data;
    }
}
