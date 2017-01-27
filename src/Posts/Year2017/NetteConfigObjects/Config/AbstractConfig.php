<?php

declare(strict_types = 1);

namespace Pehapkari\Website\Posts\Year2017\NetteConfigObjects\Config;

use Nette\Utils\ArrayHash;

abstract class AbstractConfig extends ArrayHash
{
    public function __construct(array $arr)
    {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $this->$key = ArrayHash::from($value, true);
            } else {
                $this->$key = $value;
            }
        }
    }
}
