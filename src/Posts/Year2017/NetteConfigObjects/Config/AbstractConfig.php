<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\NetteConfigObjects\Config;

use Nette\Utils\ArrayHash;

abstract class AbstractConfig extends ArrayHash
{
    /**
     * @param mixed[] $array
     */
    public function __construct(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->{$key} = ArrayHash::from($value, true);
            } else {
                $this->{$key} = $value;
            }
        }
    }
}
