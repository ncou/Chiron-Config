<?php

declare(strict_types=1);

namespace Chiron\Tests\Config;

use Chiron\Config\ConfigLoader;
use Chiron\Config\Loader\IniLoader;
use Chiron\Config\Loader\JsonLoader;
use Chiron\Config\Loader\PathLoader;
use Chiron\Config\Loader\PhpLoader;
use Chiron\Config\Loader\YmlLoader;
use PHPUnit\Framework\TestCase;

class ConfigLoaderTest extends TestCase
{
    public function testConfigLoaderWithEmptyConfig()
    {
        $config = new ConfigLoader();
        $config->pushLoader(new PhpLoader());
        $config->pushLoader(new JsonLoader());
        $config->pushLoader(new IniLoader());
        $config->pushLoader(new YmlLoader());
        $config->load(__DIR__ . '/Fixtures/test_php.php');
        $config->load(__DIR__ . '/Fixtures/test_json.json');
        $config->load(__DIR__ . '/Fixtures/test_ini.ini');
        $config->load(__DIR__ . '/Fixtures/test_yml.yml');

        static::assertSame([
            'foo'     => 'foo string',
            'vendor1' => [
                'service1' => [
                    'name' => 'vendor1 service1 name..',
                    'path' => 'vendor1 service1 path..',
                ],
                'service2' => [
                    'name' => 'vendor1 service2 name..',
                    'path' => 'vendor1 service2 path..',
                ],
            ],
            'vendor2' => [
                'service1' => [
                    'name' => 'vendor2 service1 name..',
                    'path' => 'vendor2 service1 path..',
                ],
                'service2' => [
                    'name' => 'vendor2 service2 name..',
                    'path' => 'vendor2 service2 path..',
                ],
            ],
            'json1' => 'json 1 string',
            'json2' => [
                'json2-1',
                'json2-2',
            ],
            'ini1' => 'what the',
            'ini2' => 'false',
            'ini3' => ['name' => 'configuration', 'host' => ['port' => '80', 'name' => 'localhost']],
            'yml1' => [
                'yml11' => true,
            ],
            'yml2' => [
                'paths' => ['vendor/*', 'tests/*'],
            ],
            'yml3' => [
                'yml3_1',
                'yml3_2',
            ],
        ], $config->getConfig()->toArray());
    }

    public function testPathLoader()
    {
        $config = new ConfigLoader();

        $config->pushLoader(new PathLoader([
            new IniLoader(),
            new JsonLoader(),
            new PhpLoader(),
            new YmlLoader(),
        ]));

        $config->load(__DIR__ . '/Fixtures');

        static::assertEquals([
            'test_ini' => [
                'ini1' => 'what the',
                'ini2' => 'false',
                'ini3' => ['name' => 'configuration', 'host' => ['port' => '80', 'name' => 'localhost']],
            ],
            'test_json' => [
                'json1' => 'json 1 string',
                'json2' => [
                    'json2-1',
                    'json2-2',
                ],
            ],
            'test_php' => [
                'foo'     => 'foo string',
                'vendor1' => [
                    'service1' => [
                        'name' => 'vendor1 service1 name..',
                        'path' => 'vendor1 service1 path..',
                    ],
                    'service2' => [
                        'name' => 'vendor1 service2 name..',
                        'path' => 'vendor1 service2 path..',
                    ],
                ],
                'vendor2' => [
                    'service1' => [
                        'name' => 'vendor2 service1 name..',
                        'path' => 'vendor2 service1 path..',
                    ],
                    'service2' => [
                        'name' => 'vendor2 service2 name..',
                        'path' => 'vendor2 service2 path..',
                    ],
                ],
            ],
            'test_yml' => [
                'yml1' => [
                    'yml11' => true,
                ],
                'yml2' => [
                    'paths' => ['vendor/*', 'tests/*'],
                ],
                'yml3' => [
                    'yml3_1',
                    'yml3_2',
                ],
            ],
            'test_path' => [
                'app' => [
                    'debug' => true,
                    'env'   => 'test',
                ],
            ],
        ], $config->getConfig()->toArray());
    }
}
