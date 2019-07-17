<?php

declare(strict_types=1);

namespace Chiron\Config;

class Config implements ConfigInterface
{
    /** @var array */
    protected $items;

    /**
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * {@inheritdoc}
     */
    // TODO : améliorer la fonction "has()" et "get()" avec l'utilisation d'un cache (à vider dans la fonction merge) !!!! => https://github.com/hassankhan/config/blob/master/src/AbstractConfig.php#L114
    public function has(string $name): bool
    {
        if ($name === '') {
            return true;
        }
        $names = explode('.', $name);
        $dataToReturn = $this->items;
        while (count($names)) {
            $name = array_shift($names);
            if (! is_array($dataToReturn) || ! array_key_exists($name, $dataToReturn)) {
                return false;
            }
            $dataToReturn = $dataToReturn[$name];
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    // TODO : améliorer la fonction "get()" => https://github.com/pinepain/php-simple-config/blob/master/src/Config.php#L49
    public function get(string $name, $default = null)
    {
        if ($name === '') {
            return $this->items;
        }
        $names = explode('.', $name);
        $dataToReturn = $this->items;
        while (count($names)) {
            $name = array_shift($names);
            if (! is_array($dataToReturn) || ! array_key_exists($name, $dataToReturn)) {
                return $default;
            }
            $dataToReturn = $dataToReturn[$name];
        }

        return $dataToReturn;
    }

    /**
     * {@inheritdoc}
     */
    public function subset(string $name): ConfigInterface
    {
        $subset = $this->get($name);

        if (! is_array($subset)) {
            throw new \InvalidArgumentException('Subset must be an array.');
        }

        return new static($subset);
    }

    /**
     * @param array $appender
     */
    public function merge(array $appender): void
    {
        $this->items = $this->recursiveMerge($this->items, $appender);
    }

    /**
     * @param mixed $origin
     * @param mixed $appender
     *
     * @return mixed
     */
    // TODO : on dirait que les deux paramétres sont des tableaux. et que la valeur de retour sera aussi un tableau. modifier le typehint
    private function recursiveMerge($origin, $appender)
    {
        if (is_array($origin)
            && array_values($origin) !== $origin
            && is_array($appender)
            && array_values($appender) !== $appender) {
            foreach ($appender as $key => $value) {
                if (isset($origin[$key])) {
                    $origin[$key] = $this->recursiveMerge($origin[$key], $value);
                } else {
                    $origin[$key] = $value;
                }
            }

            return $origin;
        }

        return $appender;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @param string $name
     *
     * @return ConfigInterface|mixed
     */
    public function __get($name)
    {
        $subset = $this->get($name);

        return is_array($subset) ? new static($subset) : $subset;
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
        throw new \LogicException(sprintf('Cannot call "%s" in "%s"', __FUNCTION__, __CLASS__));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException(sprintf('Cannot call "%s" in "%s"', __FUNCTION__, __CLASS__));
    }
}
