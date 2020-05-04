<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\Loader\LoaderInterface;
use Chiron\Config\Exception\ConfigException;
use Chiron\Boot\Filesystem;
use Chiron\Config\Loader\PhpLoader;
use Chiron\Config\Loader\IniLoader;
use Chiron\Config\Loader\JsonLoader;
use Chiron\Config\Loader\YmlLoader;
use LogicException;

//https://github.com/illuminate/config/blob/master/Repository.php
//https://github.com/hassankhan/config/blob/master/src/AbstractConfig.php#L114
//https://github.com/zendframework/zend-config/blob/master/src/Config.php

final class ConfigManager
{
    /** @var array */
    // TODO : renommer cette variable en "$sections" et pas data !!!!
    private $sections = [];
    /** @var LoaderInterface[] */
    private $loaders = [];
    /** @var Filesystem */
    private $filesystem;

    public function __construct()
    {
        $this->loaders[] = new PhpLoader();
        $this->loaders[] = new IniLoader();
        $this->loaders[] = new JsonLoader();
        $this->loaders[] = new YmlLoader();

        $this->filesystem = new Filesystem();
    }

    /**
     * @param LoaderInterface $loader
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    public function hasConfig(string $section): bool
    {
        // TODO : fait plutot un array_key_exist()
        return isset($this->sections[$section]);
    }

    /**
     * @inheritdoc
     */
    // TODO : permettre de récupérer un subset de la config !!!! cad ajouter un paramétre $subset à null et si ce subset existe et q'uil est de type array on fait un new Config($subset_values)
    public function getConfig(string $section): Config
    {
        if (isset($this->sections[$section])) {
            return $this->sections[$section];
        }

        // TODO : afficher le nom de la section et du subset recherché dans le message de l'exception. Ca sera plus simple pour débugger !!!
        throw new ConfigException('Config not found in the manager !');

    }

    // TODO : code à améliorer !!!!
    public function loadFromFile(string $file): void
    {
        if (! $this->filesystem->isFile($file)) {
            // TODO : Lever plutot une InvalidConfigurationException
            throw new ConfigException('Invalid file path');
        }

        foreach ($this->loaders as $loader) {
            if ($loader->canLoad($file)) {
                $this->config->merge($loader->load($file));

                return;
            }
        }

        throw new ConfigException(sprintf('Cannot load "%s"', $path));
    }

    // TODO : éventuellement lui passer un paramétre $section pour le nom et ensuite le contenu $data
    public function loadFromArray(array $data): void
    {
        // TODO : à implémenter !!!!
    }



    public function loadFromDirectory(string $directory): void
    {
        if (! $this->filesystem->isDirectory($directory)) {
            // TODO : Lever plutot une InvalidConfigurationException
            throw new ConfigException('Invalid directory path');
        }

        $directory = realpath($directory);
        $files = $this->filesystem->files($directory);

        foreach ($files as $file) {

            // TODO : lui passer plutot un getBaseName en paramétre, voir même un getExtension() !!!!
            $loader = $this->getLoaderFor($file->getRealPath());

            if ($loader) {
                $section = $this->generateSectionName($file, $directory);
                $data = $loader->load($file->getRealPath());

                $this->merge($section, $data);
            }
        }
    }

    // return the Loader found, else return null
    private function getLoaderFor(string $filepath): ?LoaderInterface
    {
        foreach ($this->loaders as $loader) {
            if ($loader->canLoad($filepath)) {
                return $loader;
            }
        }

        return null;
    }

    /**
     * Gets a parser for a given file extension.
     *
     * @param  string $extension
     *
     * @return Noodlehaus\Parser\ParserInterface
     *
     * @throws UnsupportedFormatException If `$extension` is an unsupported file format
     */
    /*
    protected function getParser($extension)
    {
        foreach ($this->supportedParsers as $parser) {
            if (in_array($extension, $parser::getSupportedExtensions())) {
                return new $parser();
            }
        }

        // If none exist, then throw an exception
        throw new UnsupportedFormatException('Unsupported configuration format');
    }*/


    /**
     * Generate the section name (nesting path + file name using dot separator).
     *
     * @param  \SplFileInfo  $file
     * @param  string  $path
     * @return string
     */
    private function generateSectionName(\SplFileInfo $file, string $path): string
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($path, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }

        $key = $nested . $file->getBasename('.' . $file->getExtension());

        return $key;
    }

    /**
     * @param array $appender
     */
    // TODO : conserver cette méthode en public ??? éventuelleùment cela permettrait de charger la config depuis un array (ce qui viendrait compléter les possibilité de chargement en plus des méthodes loadFromDirectory et loadFromFile) éventuellement renommer cette méthode en loadFromArray($section, $data)
    public function merge(string $section, array $data): void
    {
        // if the section is already present, we merge both the datas.
        $result = $this->recursiveMerge($this->sections[$section] ?? [], $data);
        $this->sections[$section] = new Config($result);
    }

    /**
     * @param mixed $origin
     * @param mixed $appender
     *
     * @return mixed
     */
    //https://github.com/yiisoft/yii2-framework/blob/ecae73e23abb524bb637c37c62e4db5495f5f4f2/helpers/BaseArrayHelper.php#L117
    //https://github.com/hiqdev/composer-config-plugin/blob/master/src/utils/Helper.php#L27
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
    /*
    public function subset(string $name): ConfigInterface
    {
        $subset = $this->get($name);

        if (! is_array($subset)) {
            throw new \InvalidArgumentException('Subset must be an array.');
        }

        return new static($subset);
    }*/

    /**
   * Merges multiple arrays, recursively, and returns the merged array.
   *
   * This function is equivalent to NestedArray::mergeDeep(), except the
   * input arrays are passed as a single array parameter rather than a variable
   * parameter list.
   *
   * The following are equivalent:
   * - NestedArray::mergeDeep($a, $b);
   * - NestedArray::mergeDeepArray(array($a, $b));
   *
   * The following are also equivalent:
   * - call_user_func_array('NestedArray::mergeDeep', $arrays_to_merge);
   * - NestedArray::mergeDeepArray($arrays_to_merge);
   *
   * @param array $arrays
   *   An arrays of arrays to merge.
   * @param bool $preserve_integer_keys
   *   (optional) If given, integer keys will be preserved and merged instead of
   *   appended. Defaults to FALSE.
   *
   * @return array
   *   The merged array.
   *
   * @see NestedArray::mergeDeep()
   */
    // TODO : vérifier avec la fonction de drupal pour le deep merge. => https://api.drupal.org/api/drupal/includes%21bootstrap.inc/function/drupal_array_merge_deep/7.x
  public static function mergeDeepArray(array $arrays, $preserve_integer_keys = FALSE) {
    $result = [];
    foreach ($arrays as $array) {
      foreach ($array as $key => $value) {

        // Renumber integer keys as array_merge_recursive() does unless
        // $preserve_integer_keys is set to TRUE. Note that PHP automatically
        // converts array keys that are integer strings (e.g., '1') to integers.
        if (is_int($key) && !$preserve_integer_keys) {
          $result[] = $value;
        }
        elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
          $result[$key] = self::mergeDeepArray([
            $result[$key],
            $value,
          ], $preserve_integer_keys);
        }
        else {
          $result[$key] = $value;
        }
      }
    }
    return $result;
  }


  function drupal_array_merge_deep_array($arrays) {
      $result = array();
      foreach ($arrays as $array) {
        foreach ($array as $key => $value) {

          // Renumber integer keys as array_merge_recursive() does. Note that PHP
          // automatically converts array keys that are integer strings (e.g., '1')
          // to integers.
          if (is_integer($key)) {
            $result[] = $value;
          }
          elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
            $result[$key] = drupal_array_merge_deep_array(array(
              $result[$key],
              $value,
            ));
          }
          else {
            $result[$key] = $value;
          }
        }
      }
      return $result;
    }

    //TODO : autre exemple : https://api.cakephp.org/3.3/class-Cake.Utility.Hash.html#_merge
    //

}
