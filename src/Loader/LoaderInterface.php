<?php

declare(strict_types=1);

namespace Chiron\Config\Loader;

interface LoaderInterface
{
    /**
     * @param string $path
     *
     * @return bool
     */
    public function canLoad(string $path): bool;

    /**
     * @param string $path
     *
     * @return array|null
     */
    // TODO : forcer le retour en tant que array et lever une exception dans la méthode Load si on n'arrive pas à avoir un contenu de type tableau !!!!
    public function load(string $path);
}
