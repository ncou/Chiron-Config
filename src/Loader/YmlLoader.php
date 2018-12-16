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
        return file_exists($path) && preg_match($this->pattern, pathinfo($path)['basename']);
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $path)
    {
        return YamlParser::parseFile($path, YamlParser::PARSE_CONSTANT);
    }
}
