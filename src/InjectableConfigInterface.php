<?php

declare(strict_types=1);

namespace Chiron\Config;

// TODO : éventuellement séparer le code et les interfaces en deux parties, une pour la partie "Modification" avec les méthodes reset/set/addConfig, et une partie "Injection" avec la méthode getConfigSectionName()

// TODO : il faudrait que cette interface étende da l'interface : ConfigInterface !!! car on a besoin d'utiliser la méthode get() par exemple !!!!

interface InjectableConfigInterface
{
    public function getConfigSectionName(): string;

    public function getSectionSubsetName(): ?string;
}
