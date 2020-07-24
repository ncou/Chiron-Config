<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\Exception\ConfigException;
use Closure;
use Nette\Schema\Processor;
use Nette\Schema\Schema;

abstract class AbstractConfigSchema extends Config implements ConfigSchemaInterface
{
    /**
     * @param array $items
     */
    public function __construct(array $data = [])
    {
        // init the data values (with default scheme values if $data is empty)
        $this->setData($data);
    }

    public function setData(array $data): void
    {
        $this->data = $this->processSchema([$data]);
        $this->cache = [];
    }

    /**
     * Merges (and validates) the current configuration and the new added configuration.
     */
    public function addData(array $data): void
    {
        $this->data = $this->processSchema([$this->data, $data]);
        $this->cache = [];
    }

    public function resetData(): void
    {
        $this->data = $this->processSchema([]);
        $this->cache = [];
    }

    abstract protected function getConfigSchema(): Schema;

    /**
     * Merges and validates configurations against scheme.
     *
     * @param array $configs
     *
     * @return array
     */
    protected function processSchema(array $configs): array
    {
        // Force the return value to be an array (by default the processed schema return an stdObject)
        $schema = $this->getConfigSchema()->castTo('array');
        $processor = new Processor();

        //$processor->skipDefaults();

        try {
            $result = $processor->processMultiple($schema, $configs);
        } catch (\Nette\Schema\ValidationException $e) {
            // TODO : faire une reflection de la méthode getConfigSchema pour afficher dans l'exception (en faisant un replace de $line et $file) l'endroit ou cela a planté ???
            throw new ConfigException(
                sprintf('Schema validation inside %s::class failed [%s]', static::class, $e->getMessage())
            );
        }

        // ensure there is no dot character in the key to not disturb the ->get('xxx.yyy') funtion
        $this->assertNoCharacterDotInKeys($result);

        return $result;
    }

    /**
     * The config get() function use the dot as separator to recursively grab the array data.
     * A dot character in the original config key could disturb this function, so we forbid this character.
     */
    protected function assertNoCharacterDotInKeys(array $config): void
    {
        $iterator  = new \RecursiveArrayIterator($config);
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($recursive as $key => $value) {
            // does the key contain a dot character ?
            if (is_string($key) && strpos($key, '.') !== false) {
                throw new \UnexpectedValueException(
                    sprintf('Config key [%s] can\'t contains a dot (".") character.', $key)
                );

            }
        }
    }
}
