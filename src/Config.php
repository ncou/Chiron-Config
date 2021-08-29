<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\Exception\ConfigException;
use ArrayIterator;

// TODO : utiliser le package "dflydev/dot-access-data" pour gérer les structures avec un point ????

// TODO : renommer la méthode has() en exists()
// TODO : virer les méthode offsetGet/Set/Exists...etc idem pour la méthode getIterator à virer (penser à modifier l'interface ConfigInterface::class) !!!!
class Config implements ConfigInterface
{
    /**
     * Stores the configuration data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Caches the configuration data
     *
     * @var array
     */
    protected $cache = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if ($this->has($key)) {
            return $this->cache[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        // Check if already cached
        if (isset($this->cache[$key])) {
            return true;
        }

        $segments = explode('.', $key);
        $root = $this->data;

        // nested case
        foreach ($segments as $segment) {
            if (array_key_exists($segment, $root)) {
                $root = $root[$segment];
                continue;
            } else {
                return false;
            }
        }

        // Set cache for the given key
        $this->cache[$key] = $root;

        return true;
    }

    /**
     * @return array
     */
    // TODO : renommer la méthode en all() ou data() !!!!
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new ConfigException(
            'Unable to change configuration data, configs are treated as immutable by default'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new ConfigException(
            'Unable to change configuration data, configs are treated as immutable by default'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }
}
