<?php

declare(strict_types=1);

namespace Chiron\Config\Loader;

// TODO : améliorer le code en regardant ici => https://github.com/ncou/phalcon/blob/master/src/Phalcon/Config/Adapter/Json.php
// TODO : lever une exception si le json decode ne marche pas !!!! https://github.com/spiral/config/blob/master/src/Loader/JsonLoader.php

class JsonLoader implements LoaderInterface
{
    /** @var string */
    protected $pattern;

    public function __construct(string $pattern = '~^[a-z_][a-z0-9_]*\.json$~')
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
        $data = json_decode(file_get_contents($path), true);

        // Check for array, if its anything else, throw an exception
        if (! is_array($data)) {
            throw new LoaderException(sprintf('Config file [%s] does not return an array', $path));
        }

        return $data;
    }
}
