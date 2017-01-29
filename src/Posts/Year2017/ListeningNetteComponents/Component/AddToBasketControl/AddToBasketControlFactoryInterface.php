<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\ListeningNetteComponents\Component\AddToBasketControl;

interface AddToBasketControlFactoryInterface
{
    public function create(array $product): AddToBasketControl;
}
