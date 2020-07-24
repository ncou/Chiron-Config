<?php

declare(strict_types=1);

namespace Chiron\Config\Helper;

use Nette\Utils\Validators as NetteValidator;

// TODO : améliorer le extend du nette validator pour ajouter des méthodes protected pour ajouter le charset/timezone/locale à la méthode 'is()' présente dans nette\validators
final class Validator extends NetteValidator
{
    /**
     * Helper used for the Nette/Schema assert() validation.
     * Check if the array is associative (all keys should be strings).
     *
     * @return Closure
     */
    public static function isArrayAssociative(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) === count($array);
    }

    /**
     * Helper used for the Nette/Schema assert() validation.
     * Check if the array is a zero-based integer indexed array.
     *
     * @return Closure
     */
    public static function isArrayIndexed(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }


    public static function isCharset(string $encoding): bool
    {
        return in_array($encoding, mb_list_encodings());
    }

    public static function isTimezone(string $timezone): bool
    {
        return in_array($timezone, \DateTimeZone::listIdentifiers());
    }

    public static function isLocale(string $locale): bool
    {
        return in_array($locale, \ResourceBundle::getLocales(''));
    }
}
