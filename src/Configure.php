<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\Config;
use Chiron\Config\ConfigInterface;
use Chiron\Config\ConfigLoader;
use Chiron\Config\Exception\ConfigException;
use Chiron\Container\SingletonInterface;
use SplFileInfo;
use Chiron\Config\Loader\LoaderInterface;
use Chiron\Config\Loader\PhpLoader;

//https://github.com/cakephp/cakephp/blob/master/src/Core/Configure.php

//https://github.com/illuminate/config/blob/master/Repository.php
//https://github.com/hassankhan/config/blob/master/src/AbstractConfig.php#L114
//https://github.com/zendframework/zend-config/blob/master/src/Config.php

//https://github.com/limingxinleo/x-phalcon-config-center/blob/master/src/Config/Center/Client.php

// TODO : on devrait pas créer une classe ConfigFactory qui se charge de créer les objets Config ??? https://github.com/zendframework/zend-config/blob/master/src/Factory.php
// TODO : renommer la classe en Configure::class et laisser les méthode load() qui chargera aussi bien un fichier qu'un répertoire. + has() et get() & getConfig() pour avoir un retour d'objet mais aussi de tableau data et la méthode add() qui permet de charger les données d'une config depuis un array.
// TODO : renommer en "Configure::class" + faire un helper dans les fonction de type config_item($item, $section) ou config($section)
// TODO : améliorer le code, surtout la méthode read/check/has/set/merge etc...
// TODO : renommer cette classe en ConfigManager::class
// TODO : déplacer cette classe dans le package "chiron\config"
final class Configure //implements SingletonInterface
{
    /** @var LoaderInterface */
    private $loader;
    /** @var array */
    private $data = [];


    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Return true if config section exists.
     *
     * @param string $section
     *
     * @return bool
     */
    public function exists(string $section): bool
    {
        return isset($this->data[$section]) || $this->loader->has($section);
    }

    /**
     * Read a config section data array.
     *
     * @param string $section
     * @return array
     *
     * @throws LoaderException
     */
    // TODO : gérer le cas ou la config n'existe pas, il faudrai que cette méthode leve une exception !!!!
    // TODO : *********************  il faut gérer le subset comme paramétre dans cette méthode !!!!! *******************
    public function read(string $section): array
    {
        if (isset($this->data[$section])) {
            return $this->data[$section];
        }

        $data = $this->loader->load($section); // indiquer dans la phpDoc que cette méthode va lever un @throws LoaderException si le fichier de config n'existe pas !!!! Eventuellement faire un try/catch et le ver une BootException ? ou une ImproperlyConfiguredException ???? ou une ConfiguratorException qui serait créée dans le package Chiron\Core !!!

        return $this->data[$section] = $data;
    }











    public function getConfig_SAVE(string $section, ?string $subset = null): ConfigInterface
    {
        if (! $this->hasConfig($section)) {
            throw new ConfigException(sprintf('Config section "%s" not found in the manager !', $section));
        }

        $config = $this->sections[$section];

        if ($subset !== null) {
            // TODO : attention il faudrait gérer le cas ou le subset n'existe pas !!!!
            $data = $config->get($subset);

            if (! is_array($data)) {
                // TODO : afficher le nom du subset recherché dans le message de l'exception. Ca sera plus simple pour débugger !!! Afficher le gettype() pour indiquer si c'est une chaine ou null par exemple.
                throw new ConfigException('Subset must be an array !');
            }

            $config = new Config($data);
        }

        return $config;
    }











}
