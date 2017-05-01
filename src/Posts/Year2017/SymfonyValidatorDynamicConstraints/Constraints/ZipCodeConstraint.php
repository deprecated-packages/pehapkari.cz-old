<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\SymfonyValidatorDynamicConstraints\Constraints;

use Pehapkari\Website\Posts\Year2017\SymfonyValidatorDynamicConstraints\IsoCodes\ZipCode as IsoCodesZipCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

final class ZipCodeConstraint extends Constraint
{
    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $message = 'This value is not a valid ZIP code.';

    /**
     * @param array $options
     */
    public function __construct(?array $options = null)
    {
        parent::__construct($options);

        if (! in_array($this->country, IsoCodesZipCode::getAvailableCountries())) {
            throw new ConstraintDefinitionException(sprintf(
                'The option "country" must be one of "%s" or "all"',
                implode('", "', IsoCodesZipCode::getAvailableCountries())
            ));
        }
    }
}
