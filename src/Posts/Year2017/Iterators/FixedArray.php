<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\Iterators;

use SplFixedArray;

final class FixedArray extends SplFixedArray
{
    /**
     * @return mixed[]
     */
    public function __debugInfo(): array
    {
        $return = [];
        foreach ($this as $key => $val) {
            $return[(string) $key] = (string) $val;
        }

        return $return;
    }
}
