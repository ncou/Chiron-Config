<?php

declare(strict_types=1);

namespace Chiron\Config\Loader;

// https://github.com/ncou/phalcon/blob/master/src/Phalcon/Config/Adapter/Ini.php

class IniLoader implements LoaderInterface
{
    /** @var string */
    protected $pattern;

    public function __construct(string $pattern = '~^[a-z_][a-z0-9_]*\.ini$~')
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
    public function load(string $path)
    {
        $content = parse_ini_file($path, true);

        foreach ($content as $section => $directives) {
            if (is_array($directives) === false || empty($directives) === true) {
                $array[$section] = $directives;
            } else {
                foreach ($directives as $key => $value) {
                    if (strpos($key, '.') !== false) {
                        (isset($array[$section]) === false) && $array[$section] = [];
                        $array[$section] = self::_parseKey($array[$section], $key, $value);
                    } else {
                        $array[$section][$key] = $value;
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Recursive parse key.
     *
     * <code>
     * print_r(self::_parseKey(array(), 'a.b.c', 1));
     * </code>
     *
     * @param array        $config
     * @param string       $key
     * @param scalar|array $value
     *
     * @throws Exception
     *
     * @return array
     */
    private static function _parseKey(array $config, $key, $value)
    {
        if (strpos($key, '.') !== false) {
            list($k, $v) = explode('.', $key, 2);
            if (empty($k) === false && empty($v) === false) {
                if (isset($config[$k]) === false) {
                    $config[$k] = [];
                }
            } else {
                throw new \InvalidArgumentException("Invalid key '" . $key . "'");
            }
            $config[$k] = self::_parseKey($config[$k], $v, $value);
        } else {
            $config[$key] = $value;
        }

        return $config;
    }
}
