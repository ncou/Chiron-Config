<?php

declare(strict_types=1);

namespace Chiron\Tests\Config\Helper\Validator;

use Chiron\Config\Helper\Validator;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidCharsetShouldReturnTrue()
    {
        $this->assertTrue(Validator::isCharset('UTF-8'));
        $this->assertTrue(Validator::isCharset('ASCII'));
        $this->assertTrue(Validator::isCharset('ISO-8859-1'));
    }

    public function testInvalidCharsetShouldReturnFalse()
    {
        $this->assertFalse(Validator::isCharset('UTF-9'));
    }

    public function testValidTimezoneShouldReturnTrue()
    {
        $this->assertTrue(Validator::isTimezone('UTC'));
        $this->assertTrue(Validator::isTimezone('America/Los_Angeles'));
        $this->assertTrue(Validator::isTimezone('Europe/Paris'));
    }

    public function testInvalidTimezoneShouldReturnFalse()
    {
        $this->assertFalse(Validator::isTimezone('Non_Existing'));
    }

    public function testValidLocaleShouldReturnTrue()
    {
        $this->assertTrue(Validator::isLocale('en_US'));
        $this->assertTrue(Validator::isLocale('fr'));
        $this->assertTrue(Validator::isLocale('ca_FR'));
    }

    public function testInvalidLocaleShouldReturnFalse()
    {
        $this->assertFalse(Validator::isLocale('Non_Existing'));
    }

}
