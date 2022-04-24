<?php

declare(strict_types=1);

namespace Chiron\Config\Loader;

use Chiron\Config\Exception\LoaderException;

use Throwable;

// TODO : classe temporaire c'est utilisé pour les tests de l'application quand on ne souhaite pas passer par le PHPLoader. Mais si on ajouter une notion de "Default" dans la classe Configure dans ce cas on n'aura pas besoin de cette classe car on pourra gérer les bouchons de données de config via un setDefaults(XXXX)
final class ArrayLoader implements LoaderInterface
{
    /** @var array<string, array<string, mixed>> */
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $section): bool
    {
        return isset($this->data[$section]);
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $section): array
    {
        $data = $this->data[$section];

        // Check for array, if its anything else, throw an exception
        if (! is_array($data)) {
            throw new LoaderException(sprintf('Config key "%s" did not return an array', $section));
        }

        return $data;
    }
}
