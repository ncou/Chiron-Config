<?php

declare(strict_types=1);

namespace Chiron\Tests\Config;

use Chiron\Container\Container;
use Chiron\Logger\LoggerManager;
use Chiron\Core\Directories;
use InvalidArgumentException;
use Chiron\Config\Exception\ConfigException;
use Nette\Schema\Schema;
use Nette\Schema\Expect;
use PHPUnit\Framework\TestCase;

class AbstractConfigSchemaTest extends TestCase
{
    // TODO : ajouter des tests avec de l'injection automatique via le container.

    public function testConfigWithoutInjection(): void
    {
        $config = new FixtureConfig([]);

        $this->assertSame([], $config->getData());
    }

    public function testNoExceptionWithDotCharacterInValue(): void
    {
        $config = new FixtureConfig(['value.with.dot']);

        $this->assertSame(['value.with.dot'], $config->getData());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Config key [key.with.dot] can't contains a dot (".") character.
     */
    public function testExceptionWithDotCharacterInKeys_1(): void
    {
        $config = new FixtureConfig(['key.with.dot' => 'value']);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Config key [sub.key.with.dot] can't contains a dot (".") character.
     */
    public function testExceptionWithDotCharacterInKeys_2(): void
    {
        $config = new FixtureConfig(['key' => [
            'sub.key.with.dot' => 'value']
        ]);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Config key [key.with.dot] can't contains a dot (".") character.
     */
    public function testExceptionWithDotCharacterInKeys_3(): void
    {
        $config = new BadConfigWithDotCharInKey([]);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Config key [key.with.dot] can't contains a dot (".") character.
     */
    public function testExceptionWithDotCharacterInKeys_4(): void
    {
        // ensure the character check is donne in the structure without cast (it's sot StdClass object)
        $config = new BadConfigWithDotCharInKeyWithoutCast([]);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Config key [key.with.dot] can't contains a dot (".") character.
     */
    public function testExceptionWithDotCharacterInKeys_5(): void
    {
        $config = new BadConfigWithDotCharInKeyWithCast([]);
    }

}

class FixtureConfig extends \Chiron\Config\AbstractConfigSchema
{
    protected const CONFIG_SECTION_NAME = 'none';

    protected function getConfigSchema(): Schema
    {
        return Expect::mixed();
    }
}

class BadConfigWithDotCharInKey extends \Chiron\Config\AbstractConfigSchema
{
    protected const CONFIG_SECTION_NAME = 'none';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'key.with.dot'       => Expect::string()->default('value'),
        ])->otherItems(Expect::mixed());
    }
}

class BadConfigWithDotCharInKeyWithoutCast extends \Chiron\Config\AbstractConfigSchema
{
    protected const CONFIG_SECTION_NAME = 'none';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'structure'       => Expect::structure(['key.with.dot' => Expect::string()]),
        ])->otherItems(Expect::mixed());
    }
}

class BadConfigWithDotCharInKeyWithCast extends \Chiron\Config\AbstractConfigSchema
{
    protected const CONFIG_SECTION_NAME = 'none';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'structure'       => Expect::structure(['key.with.dot' => Expect::string()])->castTo('array'),
        ])->otherItems(Expect::mixed());
    }
}
