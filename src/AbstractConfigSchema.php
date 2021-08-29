<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\Exception\ConfigException;
use Closure;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\Schema\Elements\Structure;

// TODO : ajouter une méthode magique __getXXXX() qui se charge de retrouver la clés XXXX dans les propriétés ca permet de retrouver directement une propriété.
// Exemple :   $this->getCookieName() va appeller la méthode magique __call(), get qui va récupérer la fin de la méthode pour vérifier si la clés existe. On récupérer "CookieName" on applique un snake_case dessus pour avoir la clés. Donc on vérifiée via un Schema->has('cookie_name') ou Config->has('cookie_name') que cette clés existe et à ce moment là on renvoit la valeur. Ca rend plus générique les getteurs et setteurs !!!

// https://github.com/thephpleague/config/blob/main/src/Configuration.php

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

    // TODO : renommer la méthode en defineSchema(): Structure    https://github.com/redbitcz/subreg-api-php/blob/60f377ac68f3c1871b926eca336f5eb8d3368455/src/Schema/SchemaObject.php#L20
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
        $schema = $this->getConfigSchema();



        // ensure all the structure added in the schema are :
        // - casted as output array and not as stdClass.
        // - doesn't contains invalid characters (ex: the '.' char car disturb the nested get function).
        $this->prepareStructure($schema);

        $processor = new Processor();
        try {
            $result = $processor->processMultiple($schema, $configs);
        } catch (\Nette\Schema\ValidationException $e) {
            // TODO : faire une reflection de la méthode getConfigSchema pour afficher dans l'exception (en faisant un replace de $line et $file) l'endroit ou cela a planté ???
            throw new ConfigException(
                sprintf('Schema validation inside %s::class failed [%s]', static::class, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        // ensure there is no dot character in the key to not disturb the ->get('xxx.yyy') funtion
        // TODO : virer ce bout de code !!!
        //$this->assertNoCharacterDotInKeys($result); // TODO : controle à déplacer dans la méthode castAllStructure et utiliser la variable $key pour avoir le nom !!!!

        return $result;
    }

    /**
     * The config get() function use the dot as separator to recursively grab the array data.
     * A dot character in the original config key could disturb this function, so we forbid this character.
     */
    /*
    protected function assertNoCharacterDotInKeys(array $config): void
    {
        $iterator  = new \RecursiveArrayIterator($config);
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($recursive as $key => $value) {
            // does the key contain a dot character ?
            // TODO il faudrait plutot faire un truc du genre : if (! preg_match('/^[a-z0-9_]+$/', $key)) throw Exception     ca permettrait de limiter les clés dans les fichiers de config à des caractéres us-ascii en minuscule et avec uniquement le séparateur "_" permis. ca permettrait d'avoir un truc propre en terme de nommage des clés !!!!
            if (is_string($key) && strpos($key, '.') !== false) {
                throw new \UnexpectedValueException(
                    sprintf('Config key [%s] can\'t contains a dot (".") character.', $key)
                );

            }
        }
    }*/

    /*
     * Force the cast on all the structure objects.
     * Enforce the expected key format for the structures.
     */
    protected function prepareStructure(Schema $schema): void
    {
        if ($schema instanceof Structure) {
            // cast the object
            $schema->castTo('array');

            // look in the other items if there is another structure
            $prop = new \ReflectionProperty(Structure::class, 'items');
            // the items is a private property, change the visibility to read it.
            $prop->setAccessible(true);

            foreach ($prop->getValue($schema) as $key => $item) {
                // enforce the keys format: alphanumeric, lowercase and underscore as separator.
                if (! preg_match('/^[a-z0-9_]+$/', $key)) {
                    throw new \UnexpectedValueException(
                        sprintf('Config key [%s::%s] contains invalid characters.', static::class, $key)
                    );
                }

                $this->prepareStructure($item);
            }
        }
    }
}
