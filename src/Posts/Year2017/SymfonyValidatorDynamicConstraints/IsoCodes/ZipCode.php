<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\SymfonyValidatorDynamicConstraints\IsoCodes;

use InvalidArgumentException;

/**
 * Inspired by https://github.com/ronanguilloux/IsoCodes/blob/master/src/IsoCodes/ZipCode.php.
 */
final class ZipCode
{
    /**
     * @var array
     */
    private static $patterns = [
        'CZ' => '\\d{3} ?\\d{2}',
        'US' => '(\\d{5})(?:[ \\-](\\d{4}))?'
    ];

    public static function validate(string $zipcode, string $country): bool
    {
        $country = strtoupper($country);

        if (! isset(self::$patterns[$country])) {
            throw new InvalidArgumentException(sprintf(
                'The zipcode validator for "%s" does not exists yet: feel free to add it.',
                $country
            ));
        }

        return (bool) preg_match(
            '/^(' . self::$patterns[$country] . ')$/',
            $zipcode
        );
    }

    /**
     * @return string[]
     */
    public static function getAvailableCountries(): array
    {
        return array_keys(self::$patterns);
    }
}
