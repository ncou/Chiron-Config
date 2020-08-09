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

    public static function isIp(string $value): bool
    {
        return static::validateIPv4($value) || static::validateIPv6($value);
    }

    /**
     * Validates an IPv4 address
     *
     * @param string $value
     * @return bool
     */
    public static function validateIPv4(string $value): bool
    {
        if (preg_match('/^([01]{8}\.){3}[01]{8}\z/i', $value)) {
            // binary format  00000000.00000000.00000000.00000000
            $value = bindec(substr($value, 0, 8)) . '.' . bindec(substr($value, 9, 8)) . '.'
                   . bindec(substr($value, 18, 8)) . '.' . bindec(substr($value, 27, 8));
        } elseif (preg_match('/^([0-9]{3}\.){3}[0-9]{3}\z/i', $value)) {
            // octet format 777.777.777.777
            $value = (int) substr($value, 0, 3) . '.' . (int) substr($value, 4, 3) . '.'
                   . (int) substr($value, 8, 3) . '.' . (int) substr($value, 12, 3);
        } elseif (preg_match('/^([0-9a-f]{2}\.){3}[0-9a-f]{2}\z/i', $value)) {
            // hex format ff.ff.ff.ff
            $value = hexdec(substr($value, 0, 2)) . '.' . hexdec(substr($value, 3, 2)) . '.'
                   . hexdec(substr($value, 6, 2)) . '.' . hexdec(substr($value, 9, 2));
        }

        $ip2long = ip2long($value);
        if ($ip2long === false) {
            return false;
        }

        return ($value == long2ip($ip2long));
    }

    /**
     * Validates an IPv6 address
     *
     * @param  string $value Value to check against
     * @return bool True when $value is a valid ipv6 address
     *                 False otherwise
     */
    public static function validateIPv6(string $value): bool
    {
        if (strlen($value) < 3) {
            return $value == '::';
        }

        if (strpos($value, '.')) {
            $lastcolon = strrpos($value, ':');
            if (! ($lastcolon && $this->validateIPv4(substr($value, $lastcolon + 1)))) {
                return false;
            }

            $value = substr($value, 0, $lastcolon) . ':0:0';
        }

        if (strpos($value, '::') === false) {
            return (bool) preg_match('/\A(?:[a-f0-9]{1,4}:){7}[a-f0-9]{1,4}\z/i', $value);
        }

        $colonCount = substr_count($value, ':');
        if ($colonCount < 8) {
            return (bool) preg_match('/\A(?::|(?:[a-f0-9]{1,4}:)+):(?:(?:[a-f0-9]{1,4}:)*[a-f0-9]{1,4})?\z/i', $value);
        }

        // special case with ending or starting double colon
        if ($colonCount == 8) {
            return (bool) preg_match('/\A(?:::)?(?:[a-f0-9]{1,4}:){6}[a-f0-9]{1,4}(?:::)?\z/i', $value);
        }

        return false;
    }
}
